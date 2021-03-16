<?php
/**
 * contains stub class for config
 *
 * @package         tourBase
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @copyright       tourasia
 */

declare(strict_types=1);

namespace DavidLienhard\Config;

use \DavidLienhard\Config\ConfigInterface;

/**
 * stub class for config
 *
 * @author          David Lienhard <david.lienhard@tourasia.ch>
 * @copyright       tourasia
 */
class Stub implements ConfigInterface
{
    /**
     * the payload to use in the config
     * @var     array   $payload
     */
    private $payload = [ ];

    /**
     * sets path containing configuration files
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string          $directory      directory containing json configuration file
     * @return          void
     */
    public function __construct(string $directory)
    {
    }

    /**
     * returns the required configuration
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           string          $mainKey        the main key of the configuration. will be used as filename
     * @return          \stdClass
     * @uses            self::$payload
    */
    public function __get(string $mainKey) : \stdClass
    {
        $payload = json_decode(json_encode($this->payload));

        if (!isset($payload->{$mainKey})) {
            throw new \Exception("could not find key with name '".$mainKey."'");
        }

        return $payload->{$mainKey};
    }

    /**
     * returns the current log-directory
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @return          string
     */
    public function getDirectory() : string
    {
        return $this->directory;
    }

    /**
     * adds payload to the object
     *
     * @author          David Lienhard <david.lienhard@tourasia.ch>
     * @copyright       tourasia
     * @param           array           $payload        the payload to add
     * @return          void
     * @uses            self::$payload
    */
    public function addPayload(array $payload) : void
    {
        $this->payload = $payload;
    }
}
