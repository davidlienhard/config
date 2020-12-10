<?php
/**
 * contains \DavidLienhard\Config\ConfigInterface
 *
 * @package         tourBase
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @version         1.0.4, 10.12.2020
 * @since           1.0.0, 11.11.2020, created
 * @copyright       tourasia
 */

declare(strict_types=1);

namespace DavidLienhard\Config;

/**
 * interface for tourBase configuration object
 *
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @version         1.0.4, 10.12.2020
 * @since           1.0.0, 11.11.2020, created
 * @copyright       tourasia
 */
interface ConfigInterface
{
    /**
     * sets path containing configuration files
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 11.11.2020
     * @since           1.0.0, 11.11.2020, created
     * @copyright       tourasia
     * @param           string          $directory      directory containing json configuration file
     * @return          void
     */
    public function __construct(string $directory);

    /**
     * returns the required configuration and loads it once
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.0, 11.11.2020
     * @since           1.0.0, 11.11.2020, created
     * @copyright       tourasia
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @return          \stdClass|array
     */
    public function __get(string $mainKey);

    /**
     * returns the current log-directory
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @version         1.0.4, 10.12.2020
     * @since           1.0.4, 10.12.2020, created
     * @copyright       tourasia
     * @return          array
     */
    public function getDirectory() : string;
}
