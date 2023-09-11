<?php declare(strict_types=1);

/**
 * contains stub class for config
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Config;

use DavidLienhard\Config\ConfigInterface;
use DavidLienhard\Config\Exceptions\Conversion as ConversionException;
use DavidLienhard\Config\Exceptions\FileMismatch as FileMismatchException;
use DavidLienhard\Config\Exceptions\KeyMismatch as KeyMismatchException;
use League\Flysystem\Filesystem;

/**
 * stub class for config
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
class Stub implements ConfigInterface
{
    /**
     * the payload to use in the config
     * @var array
     */
    private array $payload = [];

    /**
     * sets path containing configuration files
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string                          $directory      directory containing json configuration file
     * @param           \League\Flysystem\Filesystem    $filesystem     filesystem to use (defaults to local)
     * @return          void
     */
    public function __construct(private string $directory, private Filesystem|null $filesystem = null)
    {
    }

    /**
     * returns the required configuration
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @uses            self::$payload
     */
    public function get(string $mainKey, string ...$subKeys) : int|float|string|bool|array|null
    {
        $filePath = $this->directory.$mainKey.".json";
        if (!isset($this->payload[$mainKey])) {
            throw new FileMismatchException("file '".$filePath."' does not exist");
        }

        // run $this->replace() to fetch env variables
        array_walk_recursive($this->payload, [ $this, "replace" ]);

        // return whole data if no subkeys are provided
        if (count($subKeys) === 0) {
            return $this->payload[$mainKey];
        }

        // recurse through configuration
        return $this->getSubKeys(
            $this->payload[$mainKey],
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
     * @param           int|float|string|bool|array|null    $data       data to search through
     * @param           string                              $subKeys    keys that will be used to find the config
     * @throws          KeyMismatchException            if given key cannot be found
     */
    private function getSubKeys(int|float|string|bool|array|null $data, string ...$subKeys) : int|float|string|bool|array|null
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
     * adds payload to the object
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           array           $payload        the payload to add
     * @uses            self::$payload
    */
    public function addPayload(array $payload) : void
    {
        $this->payload = $payload;
    }
}
