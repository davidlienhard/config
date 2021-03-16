<?php
/**
 * contains \DavidLienhard\Config\ConfigInterface
 *
 * @package         tourBase
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @copyright       tourasia
 */

declare(strict_types=1);

namespace DavidLienhard\Config;

/**
 * interface for tourBase configuration object
 *
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @copyright       tourasia
 */
interface ConfigInterface
{
    /**
     * sets path containing configuration files
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string          $directory      directory containing json configuration file
     * @return          void
     */
    public function __construct(string $directory);

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @param           string          $subKeys        keys that will be used to find the config
     * @return          mixed
     */
    public function get(string $mainKey, string ...$subKeys) : mixed;

    /**
     * returns the current log-directory
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @return          string
     */
    public function getDirectory() : string;
}
