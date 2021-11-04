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
            $this->loadedConfiguration[$mainKey] = $this->loadJson($mainKey);
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
     */
    public function getAsString(string $mainKey, string ...$subKeys) : string
    {
        return strval($this->get($mainKey, $subKeys));
    }

    /**
     * returns the required configuration as an int
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::get()
     */
    public function getAsInt(string $mainKey, string ...$subKeys) : int
    {
        return intval($this->get($mainKey, $subKeys));
    }

    /**
     * returns the required configuration as a float
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::get()
     */
    public function getAsFloat(string $mainKey, string ...$subKeys) : float
    {
        return floatval($this->get($mainKey, $subKeys));
    }

    /**
     * returns the required configuration as a bool
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::get()
     */
    public function getAsBool(string $mainKey, string ...$subKeys) : bool
    {
        return boolval($this->get($mainKey, $subKeys));
    }

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           mixed           $data           data to search through
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::$loadedConfiguration
     */
    private function getSubKeys(mixed $data, string ...$subKeys) : mixed
    {
        // return data if not subkeys are given
        if (count($subKeys) === 0) {
            return $data;
        }

        // extract first key and remove it from subkeys
        $firstKey = array_shift($subKeys);

        // return null if given key does not exist
        if (!isset($data[$firstKey])) {
            return null;
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
     * loads data from a json file
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $file           the json file to load
     * @throws          \Exception      if json file cannot be loaded
     * @uses            self::$directory
     */
    private function loadJson(string $file): \stdClass|array
    {
        $filePath = $this->directory.$file.".json";
        if (!$this->filesystem->fileExists($filePath)) {
            throw new \Exception("file '".$filePath."' does not exist");
        }


        try {
            $fileContent = $this->filesystem->read($filePath);
        } catch (FilesystemException | UnableToReadFile $e) {
            throw new \Exception("could not load config file", $e->getCode(), $e);
        }

        $config = json_decode($fileContent, true);
        if ($config === null) {
            throw new \Exception("could not parse config file: ".json_last_error_msg());
        }

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
}
