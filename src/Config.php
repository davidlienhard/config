<?php
/**
 * contains \DavidLienhard\Config\Config class
 *
 * @package         tourBase
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @copyright       tourasia
 */

declare(strict_types=1);

namespace DavidLienhard\Config;

use \DavidLienhard\Config\ConfigInterface;
use \DavidLienhard\FunctionCaller\Call as FunctionCaller;

/**
 * fetches the configuration from json files
 *
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @copyright       tourasia
*/
class Config implements ConfigInterface
{
    /**
     * contains the already loaded configuration objects
     * @var array
     */
    private array $loadedConfiguration = [];

    /**
     * sets path containing configuration files
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string          $directory      directory containing json configuration file
     * @return          void
     */
    public function __construct(private string $directory)
    {
    }

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
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
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
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
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @uses            self::$directory
     */
    public function getDirectory() : string
    {
        return $this->directory;
    }

    /**
     * loads data from a json file
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string          $file           the json file to load
     * @throws          \Exception      if json file cannot be loaded
     * @uses            self::$directory
     */
    private function loadJson(string $file): \stdClass|array
    {
        $filePath = $this->directory.$file.".json";
        if (!file_exists($filePath)) {
            throw new \Exception("file '".$filePath."' does not exist");
        }

        $caller = new FunctionCaller("file_get_contents", $filePath);
        $fileContent = $caller->getResult();
        if ($fileContent === false) {
            throw new \Exception("could not load config file");
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
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
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
