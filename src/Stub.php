<?php
/**
 * contains stub class for config
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

declare(strict_types=1);

namespace DavidLienhard\Config;

use DavidLienhard\Config\ConfigInterface;
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
     * @var     array
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
    public function get(string $mainKey, string ...$subKeys) : mixed
    {
        $filePath = $this->directory.$mainKey.".json";
        if (!isset($this->payload[$mainKey])) {
            throw new \Exception("file '".$filePath."' does not exist");
        }

        return $this->getSubKeys(
            $this->payload[$mainKey],
            ...$subKeys
        );
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
     */
    public function getDirectory() : string
    {
        return $this->directory;
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
