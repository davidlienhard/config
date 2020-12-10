<?php

declare(strict_types=1);

namespace tourBaseTests\Tests\Stubs;

use \PHPUnit\Framework\TestCase;
use \DavidLienhard\Config\Stub as Config;
use \DavidLienhard\Config\ConfigInterface;
use \tourBase\Tests\Stubs\StubInterface;

class ConfigStubTest extends TestCase
{
    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            Config::class,
            new Config
        );
    }

    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testConfigImplementsConfigInterface(): void
    {
        $this->assertInstanceOf(
            ConfigInterface::class,
            new Config
        );
    }

    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testConfigImplementsStubInterface(): void
    {
        $this->assertInstanceOf(
            StubInterface::class,
            new Config
        );
    }

    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testCanAddPayloadFromArray(): void
    {
        $config = new Config("config/");
        $config->addPayload([
            "test" => "test"]);
        $this->assertTrue(true);
    }

    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testCannotAddPayloadFromString(): void
    {
        $config = new Config("config/");

        $this->expectException(\TypeError::class);
        $config->addPayload("test");
    }

    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testCanGetExistingValueAsString(): void
    {
        $config = new Config("config/");
        $config->addPayload([
            "mainKey" => [
                "subKey" => "value" ]]);

        $this->assertEquals("value", $config->mainKey->subKey);
    }

    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testCanGetExistingValueAsInt(): void
    {
        $config = new Config("config/");
        $config->addPayload([
            "mainKey" => [
                "subKey" => 5 ]]);

        $this->assertEquals(5, $config->mainKey->subKey);
    }

    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testCannotGetInexistentKey(): void
    {
        $config = new Config("config/");

        $this->expectException(\Exception::class);
        $config->test->test;
    }

    /**
     * @covers \tourBase\Tests\Stubs\Config
     * @test
    */
    public function testCannotGetInexistentValue(): void
    {
        $config = new Config("config/");

        $this->expectException(\Exception::class);
        $config->test->test;
    }
}
