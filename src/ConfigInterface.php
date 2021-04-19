<?php
/**
 * contains \DavidLienhard\Config\ConfigInterface
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Config;

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
     * @return          void
     */
    public function __construct(string $directory);

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     */
    public function get(string $mainKey, string ...$subKeys) : mixed;

    /**
     * returns the current log-directory
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     */
    public function getDirectory() : string;
}
