<?php

declare(strict_types=1);

namespace DavidLienhard;

use DavidLienhard\Config\Config;
use DavidLienhard\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private string $directory = __DIR__."/assets/";

    /** @covers \DavidLienhard\Config\Config */
    public function testCanBeCreated(): void
    {
        $config = new Config(".");
        $this->assertInstanceOf(Config::class, $config);
        $this->assertInstanceOf(ConfigInterface::class, $config);
    }
    /** @covers \DavidLienhard\Config\Config */
    public function testCannotBeCreatedWithoutFolder(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new Config;
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testThrowsExceptionIfMainKeyDoesNotExist() : void
    {
        $config = new Config(".");
        $this->expectException(\Exception::class);
        $config->get("doesNotExist");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanReadSimpleFile() : void
    {
        $config = new Config($this->directory);
        $this->assertEquals([ "key" => "value" ], $config->get("simple"));
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanReadComplexFile() : void
    {
        $config = new Config($this->directory);
        $this->assertEquals([
            "value1",
            "value2",
            "value3",
            "value4",
            "value5"
        ], $config->get("complex", "key", "array"));

        $this->assertEquals(true, $config->get("complex", "key", "boolTrue"));
        $this->assertEquals(false, $config->get("complex", "key", "boolFalse"));
        $this->assertEquals(null, $config->get("complex", "key", "null"));
        $this->assertEquals(5, $config->get("complex", "key", "int1"));
        $this->assertEquals(-6, $config->get("complex", "key", "int2"));
        $this->assertEquals(121.181, $config->get("complex", "key", "float1"));
        $this->assertEquals(-1516.51, $config->get("complex", "key", "float2"));
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testNotExistingSubKeyReturnsNull() : void
    {
        $config = new Config($this->directory);
        $this->assertEquals(null, $config->get("simple", "doesnotexist"));
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testThrowsExceptionOnInvalidJsonFile() : void
    {
        $config = new Config($this->directory);
        $this->expectException(\Exception::class);
        $this->assertEquals(null, $config->get("invalidFile"));
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanReadEnvData() : void
    {
        $config = new Config($this->directory);

        putenv("TEST_VARIABLE=testvalue");
        $this->assertEquals("testvalue", $config->get("env", "key"));
        putenv("TEST_VARIABLE");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanGetDirectory() : void
    {
        $config = new Config($this->directory);
        $this->assertEquals($this->directory, $config->getDirectory());
    }
}
