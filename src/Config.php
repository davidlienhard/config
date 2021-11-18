<?php declare(strict_types=1);

/**
 * contains \DavidLienhard\Config\Config class
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Config;

use DavidLienhard\Config\ConfigInterface;
use DavidLienhard\Config\Exceptions\Config as ConfigException;
use DavidLienhard\Config\Exceptions\Conversion as ConversionException;
use DavidLienhard\Config\Exceptions\FileMismatch as FileMismatchException;
use DavidLienhard\Config\Exceptions\KeyMismatch as KeyMismatchException;
use DavidLienhard\Config\Parser\Json as JsonParser;
use DavidLienhard\Config\Parser\ParserAbstract;
use DavidLienhard\Config\Parser\Yaml as YamlParser;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToReadFile;

/**
 * fetches the configuration from json files
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
*/
class Config implements ConfigInterface
{
    /**
     * contains the already loaded configuration objects
     * @var array
     */
    private array $loadedConfiguration = [];

    /** filesystem to use */
    private Filesystem $filesystem;

    /**
     * list of parser-classes
     * defaults to json & yaml
     * @var array<int, class-string>
     */
    private array $registeredParsers = [
        JsonParser::class,
        YamlParser::class
    ];

    /**
     * list of all supported filetypes and the respective parser to use
     * @var array<string, class-string>
     */
    private array $filetypeList = [];

    /**
     * sets path containing configuration files
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string                          $directory      directory containing json configuration file
     * @param           \League\Flysystem\Filesystem    $filesystem     filesystem to use (defaults to local)
     * @return          void
     */
    public function __construct(private string $directory, Filesystem|null $filesystem = null)
    {
        if ($filesystem === null) {
            $adapter = new LocalFilesystemAdapter("/");
            $filesystem = new Filesystem($adapter);
        }

        $this->filesystem = $filesystem;

        $this->updateFiletypeList();
    }

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::$loadedConfiguration
     */
    public function get(string $mainKey, string ...$subKeys) : mixed
    {
        // fetch data from json if not loaded already
        if (!isset($this->loadedConfiguration[$mainKey])) {
            $this->loadedConfiguration[$mainKey] = $this->loadFile($mainKey);
        }

        // return whole data if no subkeys are provided
        if (count($subKeys) === 0) {
            return $this->loadedConfiguration[$mainKey];
        }

        // recurse through configuration
        return $this->getSubKeys(
            $this->loadedConfiguration[$mainKey],
            ...$subKeys
        );
    }

    /**
     * returns the required configuration as a string
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::get()
     * @throws          ConversionException             if data cannot be returned as a string
     */
    public function getAsString(string $mainKey, string ...$subKeys) : string
    {
        $data = $this->get($mainKey, ...$subKeys);

        if (is_array($data)) {
            throw new ConversionException("cannot convert array to string");
        }

        return strval($data);
    }

    /**
     * returns the required configuration as an int
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::get()
     * @throws          ConversionException             if data cannot be returned as an int
     */
    public function getAsInt(string $mainKey, string ...$subKeys) : int
    {
        $data = $this->get($mainKey, ...$subKeys);

        if (is_array($data)) {
            throw new ConversionException("cannot convert array to int");
        }

        return intval($data);
    }

    /**
     * returns the required configuration as a float
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::get()
     * @throws          ConversionException             if data cannot be returned as a float
     */
    public function getAsFloat(string $mainKey, string ...$subKeys) : float
    {
        $data = $this->get($mainKey, ...$subKeys);

        if (is_array($data)) {
            throw new ConversionException("cannot convert array to float");
        }

        return floatval($data);
    }

    /**
     * returns the required configuration as a bool
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::get()
     * @throws          ConversionException             if data cannot be returned as a bool
     */
    public function getAsBool(string $mainKey, string ...$subKeys) : bool
    {
        $data = $this->get($mainKey, ...$subKeys);

        if (is_array($data)) {
            throw new ConversionException("cannot convert array to bool");
        }

        return boolval($data);
    }

    /**
     * returns the required configuration as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::get()
     * @throws          ConversionException             if data cannot be returned as an array
     */
    public function getAsArray(string $mainKey, string ...$subKeys) : array
    {
        $data = $this->get($mainKey, ...$subKeys);

        if (!is_array($data)) {
            throw new ConversionException("given data cannot be returned as an array");
        }

        return $data;
    }

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           mixed           $data           data to search through
     * @param           string          $subKeys        keys that will be used to find the config
     * @throws          KeyMismatchException            if given key cannot be found
     */
    private function getSubKeys(mixed $data, string ...$subKeys) : mixed
    {
        // return data if not subkeys are given
        if (count($subKeys) === 0) {
            return $data;
        }

        // extract first key and remove it from subkeys
        $firstKey = array_shift($subKeys);

        // throw if given key does not exist
        if (!is_array($data) || !array_key_exists($firstKey, $data)) {
            throw new KeyMismatchException("configuration mismatch. check you configuration");
        }

        // call self
        return $this->getSubKeys($data[$firstKey], ...$subKeys);
    }

    /**
     * returns the current log-directory
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @uses            self::$directory
     */
    public function getDirectory() : string
    {
        return $this->directory;
    }

    /**
     * registers a new parser
     * given value must be full name to class and class must be subclass of ParserAbstract
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           class-string    $parser         name including full namespace of parser
     */
    public function registerParser(string $parser) : void
    {
        if (!\is_subclass_of($parser, ParserAbstract::class)) {
            throw new ConfigException("parser must be subclass of 'ParserAbstract'");
        }

        $this->registeredParsers[] = $parser;
        $this->updateFiletypeList();
    }

    /**
     * unregisters an existing parser
     * given value must be full name to class
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           class-string    $parser         name including full namespace of parser
     */
    public function unregisterParser(string $parser) : bool
    {
        if (($key = \array_search($parser, $this->registeredParsers, true)) !== false) {
            unset($this->registeredParsers[$key]);
            $this->updateFiletypeList();
            return true;
        }

        return false;
    }

    /**
     * loads data from a file
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           the file to load
     * @throws          FileMismatchException           if file cannot be loaded or parsed
     * @uses            self::$directory
     */
    private function loadFile(string $file) : array
    {
        $parserToUse = null;
        $fileToUse = null;

        foreach ($this->filetypeList as $filetype => $parser) {
            $fullFilePath = $this->directory.$file.".".$filetype;
            if ($this->filesystem->fileExists($fullFilePath)) {
                $parserToUse = $parser;
                $fileToUse = $fullFilePath;
                break;
            }
        }

        if ($parserToUse === null || $fileToUse === null) {
            throw new FileMismatchException("unable to find config file for '".$file."' in '".$this->directory."'");
        }


        try {
            $fileContent = $this->filesystem->read($fileToUse);
        } catch (FilesystemException | UnableToReadFile $e) {
            throw new FileMismatchException("could not load config file", intval($e->getCode()), $e);
        }

        $parser = new $parserToUse;
        if (! $parser instanceof ParserAbstract) {
            throw new ConfigException("invalid parser");
        }

        $config = $parser->parse($fileContent);

        // run $this->replace() to fetch env variables
        array_walk_recursive($config, [ $this, "replace" ]);

        return $config;
    }

    /**
     * callback for array_walk_recursive() in self::loadJson()
     * checks each config entry. if it starts witch env: it will be interpreted as an env variable
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           mixed           $item           item to check. used as reference to be able to replace it
     * @param           int|string      $key            key of the array
     */
    private function replace(mixed &$item, int|string $key) : void
    {
        if (is_string($item) && strtolower(substr($item, 0, 4)) === "env:") {
            $item = getenv(substr($item, 4));
        }
    }

    /**
     * updates the list with the filetypes to parser
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    private function updateFiletypeList() : void
    {
        $this->filetypeList = [];

        foreach ($this->registeredParsers as $parser) {
            $supportedFiletypes = $parser::getSupportedFiletypes();

            foreach ($supportedFiletypes as $filetype) {
                $this->filetypeList[$filetype] = $parser;
            }
        }
    }
}
