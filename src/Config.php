<?php
/**
 * contains \DavidLienhard\Config\Config class
 *
 * @package         tourBase
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @version         1.0.0, 11.11.2020
 * @since           1.0.0, 11.11.2020, created
 * @copyright       tourasia
 */

declare(strict_types=1);

namespace DavidLienhard\Config;

use \DavidLienhard\Config\ConfigInterface;

/**
 * fetches the configuration from json files
 *
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @version         1.0.0, 11.11.2020
 * @since           1.0.0, 11.11.2020, created
 * @copyright       tourasia
*/
class Config implements ConfigInterface
{
    /**
     * direcory containing configuration files
     * @var     array
     */
    private $directory;

    /**
     * contains the already loaded configuration objects
     * @var array $loadedConfiguration
     */
    private $loadedConfiguration = [ ];

    /**
     * sets path containing configuration files
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 11.11.2020
     * @since           1.0.0, 11.11.2020, created
     * @copyright       tourasia
     * @param           string          $directory      directory containing json configuration file
     * @return          void
     * @uses            self::$directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 11.11.2020
     * @since           1.0.0, 11.11.2020, created
     * @copyright       tourasia
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @return          \stdClass|array
     * @uses            self::loadedConfiguration()
     */
    public function __get(string $mainKey)
    {
        if (!isset($this->loadedConfiguration[$mainKey])) {
            $this->loadedConfiguration[$mainKey] = $this->loadJson($mainKey);
        }

        return $this->loadedConfiguration[$mainKey];
    }

    /**
     * loads data from a json file
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 11.11.2020
     * @since           1.0.0, 11.11.2020, created
     * @copyright       tourasia
     * @param           string          $file           the json file to load
     * @return          \stdClass|array
     * @throws          \Exception      if json file cannot be loaded
     * @uses            self::$directory
     */
    private function loadJson(string $file)
    {
        $filePath = $this->directory.$file.".json";
        if (!file_exists($filePath)) {
            throw new \Exception("file '".$filePath."' does not exist");
        }

        $fileContent = @file_get_contents($filePath);
        if ($fileContent === false) {
            throw new \Exception("could not load config file");
        }


        $config = json_decode($fileContent);
        if ($config === null) {
            throw new \Exception("could not parse config file: ".json_last_error_msg());
        }

        return $config;
    }
}
