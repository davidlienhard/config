<?php

declare(strict_types=1);

namespace DavidLienhard;

require_once dirname(__DIR__) . "/src/Config.php";
require_once dirname(__DIR__) . "/src/ConfigInterface.php";

use \PHPUnit\Framework\TestCase;
use \DavidLienhard\Config\Config;

class ConfigTest extends TestCase
{
    /**
     * @covers \DavidLienhard\Config\Config
    */
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            Config::class,
            new Config(".")
        );
    }

    /**
     * @covers \DavidLienhard\Config\Config::__get()
     * @covers \DavidLienhard\Config\Config::loadJson()
     */
    public function testThrowsExceptionIfMainKeyDoesNotExist() : void
    {
        $config = new Config(".");
        $this->expectException(\Exception::class);
        $config->get("doesNotExist");
    }
}
