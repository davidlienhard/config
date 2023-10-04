<?php declare(strict_types=1);

/**
 * contains \DavidLienhard\Config\ConfigInterface
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Config;

use League\Flysystem\Filesystem;

/**
 * interface for tourBase configuration object
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */
interface ConfigInterface
{
    /**
     * sets path containing configuration files
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $directory      directory containing json configuration file
     * @param           \League\Flysystem\Filesystem    $filesystem     filesystem to use (defaults to local)
     * @return          void
     */
    public function __construct(string $directory, Filesystem|null $filesystem);

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function get(string $mainKey, string ...$subKeys) : int|float|string|bool|array|null;

    /**
     * returns the required configuration as a string
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsString(string $mainKey, string ...$subKeys) : string;

    /**
     * returns the required configuration as a string or null
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsNullableString(string $mainKey, string ...$subKeys) : string|null;

    /**
     * returns the required configuration as an int
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsInt(string $mainKey, string ...$subKeys) : int;

    /**
     * returns the required configuration as an int or null
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsNullableInt(string $mainKey, string ...$subKeys) : int|null;

    /**
     * returns the required configuration as a float
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsFloat(string $mainKey, string ...$subKeys) : float;

    /**
     * returns the required configuration as a float or null
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsNullableFloat(string $mainKey, string ...$subKeys) : float|null;

    /**
     * returns the required configuration as a bool
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsBool(string $mainKey, string ...$subKeys) : bool;

    /**
     * returns the required configuration as a bool or null
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsNullableBool(string $mainKey, string ...$subKeys) : bool|null;

    /**
     * returns the required configuration as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsArray(string $mainKey, string ...$subKeys) : array;

    /**
     * returns the required configuration as an array or null
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function getAsNullableArray(string $mainKey, string ...$subKeys) : array|null;

    /**
     * returns the current log-directory
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getDirectory() : string;
}
