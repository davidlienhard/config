<?php declare(strict_types=1);

namespace DavidLienhard;

use DavidLienhard\Config\Config;
use DavidLienhard\Config\ConfigInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected static array $files = [];

    public static function setUpBeforeClass() : void
    {
        self::$files['simple'] = "{ \"key\": \"value\" }";

        self::$files['complex'] = <<<CODE
        {
            "key": {
                "array": [
                    "value1",
                    "value2",
                    "value3",
                    "value4",
                    "value5"
                ],
                "boolTrue": true,
                "boolFalse": false,
                "null": null,
                "int1": 5,
                "int2": -6,
                "float1": 121.181,
                "float2": -1516.51
            }
        }
        CODE;

        self::$files['invalid'] = <<<CODE
        {
            "key
        }
        CODE;

        self::$files['env'] = <<<CODE
        {
            "key": "ENV:TEST_VARIABLE"
        }
        CODE;
    }

    private function getFilesystem() : Filesystem
    {
        $adapter = new InMemoryFilesystemAdapter;
        return new Filesystem($adapter);
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanBeCreated(): void
    {
        $config = new Config("/");
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
        $filesystem = $this->getFilesystem();

        $config = new Config("/", $filesystem);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("file '/doesNotExist.json' does not exist");
        $config->get("doesNotExist");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanReadSimpleFile() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("simple.json", self::$files['simple']);

        $config = new Config("/", $filesystem);
        $this->assertEquals([ "key" => "value" ], $config->get("simple"));
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanReadComplexFile() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);
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
        $filesystem = $this->getFilesystem();
        $filesystem->write("simple.json", self::$files['simple']);

        $config = new Config("/", $filesystem);
        $this->assertEquals(null, $config->get("simple", "doesnotexist"));
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testThrowsExceptionOnInvalidJsonFile() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("invalid.json", self::$files['invalid']);

        $config = new Config("/", $filesystem);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches("/^could not parse config file:/");
        $this->assertEquals(null, $config->get("invalid"));
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanReadEnvData() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("env.json", self::$files['env']);

        $config = new Config("/", $filesystem);

        putenv("TEST_VARIABLE=testvalue");
        $this->assertEquals("testvalue", $config->get("env", "key"));
        putenv("TEST_VARIABLE");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanGetDirectory() : void
    {
        $filesystem = $this->getFilesystem();

        $config = new Config("/test/directory", $filesystem);
        $this->assertEquals("/test/directory", $config->getDirectory());
    }
}
