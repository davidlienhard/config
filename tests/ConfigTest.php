<?php declare(strict_types=1);

namespace DavidLienhard;

use DavidLienhard\Config\Config;
use DavidLienhard\Config\ConfigInterface;
use DavidLienhard\Config\Exceptions\Conversion as ConversionException;
use DavidLienhard\Config\Exceptions\Mismatch as MismatchException;
use DavidLienhard\Config\Exceptions\Parse as ParseException;
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
                "string": "string value",
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

        self::$files['complexYaml'] = <<<CODE
        key:
          string: string value
          array:
          - value1
          - value2
          - value3
          - value4
          - value5
          boolTrue: true
          boolFalse: false
          'null':
          int1: 5
          int2: -6
          float1: 121.181
          float2: -1516.51
        CODE;

        self::$files['invalid'] = <<<CODE
        {
            "key
        }
        CODE;

        self::$files['invalidYaml'] = <<<CODE
        key: key: key:
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
        $this->expectException(MismatchException::class);
        $this->expectExceptionMessage("unable to find config file for 'doesNotExist' in '/'");
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
        $this->expectException(MismatchException::class);
        $config->get("simple", "doesnotexist");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testThrowsExceptionOnInvalidJsonFile() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("invalid.json", self::$files['invalid']);

        $config = new Config("/", $filesystem);
        $this->expectException(ParseException::class);
        $this->expectExceptionMessageMatches("/^could not parse config file:/");
        $this->assertEquals(null, $config->get("invalid"));
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testThrowsExceptionOnInvalidYamlFile() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("invalid.yml", self::$files['invalidYaml']);

        $config = new Config("/", $filesystem);
        $this->expectException(ParseException::class);
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

    /** @covers \DavidLienhard\Config\Config */
    public function testCanGetAsString() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);

        $result = $config->getAsString("complex", "key", "string");
        $this->assertEquals("string value", $result);
        $this->assertIsString($result);


        $result = $config->getAsString("complex", "key", "int1");
        $this->assertEquals("5", $result);
        $this->assertIsString($result);


        $result = $config->getAsString("complex", "key", "float1");
        $this->assertEquals("121.181", $result);
        $this->assertIsString($result);


        $result = $config->getAsString("complex", "key", "boolTrue");
        $this->assertEquals("1", $result);
        $this->assertIsString($result);


        $result = $config->getAsString("complex", "key", "boolFalse");
        $this->assertEquals("", $result);
        $this->assertIsString($result);


        $result = $config->getAsString("complex", "key", "null");
        $this->assertEquals("", $result);
        $this->assertIsString($result);


        $this->expectException(ConversionException::class);
        $config->getAsString("complex", "key", "array");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanGetAsInt() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);

        $result = $config->getAsInt("complex", "key", "string");
        $this->assertEquals(0, $result);
        $this->assertIsInt($result);


        $result = $config->getAsInt("complex", "key", "int1");
        $this->assertEquals(5, $result);
        $this->assertIsInt($result);


        $result = $config->getAsInt("complex", "key", "float1");
        $this->assertEquals(121, $result);
        $this->assertIsInt($result);


        $result = $config->getAsInt("complex", "key", "boolTrue");
        $this->assertEquals(1, $result);
        $this->assertIsInt($result);


        $result = $config->getAsInt("complex", "key", "boolFalse");
        $this->assertEquals(0, $result);
        $this->assertIsInt($result);


        $result = $config->getAsInt("complex", "key", "null");
        $this->assertEquals(0, $result);
        $this->assertIsInt($result);


        $this->expectException(ConversionException::class);
        $config->getAsInt("complex", "key", "array");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanGetAsFloat() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);

        $result = $config->getAsFloat("complex", "key", "string");
        $this->assertEquals(0.0, $result);
        $this->assertIsFloat($result);


        $result = $config->getAsFloat("complex", "key", "int1");
        $this->assertEquals(5.0, $result);
        $this->assertIsFloat($result);


        $result = $config->getAsFloat("complex", "key", "float1");
        $this->assertEquals(121.181, $result);
        $this->assertIsFloat($result);


        $result = $config->getAsFloat("complex", "key", "boolTrue");
        $this->assertEquals(1.0, $result);
        $this->assertIsFloat($result);


        $result = $config->getAsFloat("complex", "key", "boolFalse");
        $this->assertEquals(0.0, $result);
        $this->assertIsFloat($result);


        $result = $config->getAsFloat("complex", "key", "null");
        $this->assertEquals(0.0, $result);
        $this->assertIsFloat($result);


        $this->expectException(ConversionException::class);
        $config->getAsFloat("complex", "key", "array");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanGetAsBool() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);

        $result = $config->getAsBool("complex", "key", "string");
        $this->assertEquals(true, $result);
        $this->assertIsBool($result);


        $result = $config->getAsBool("complex", "key", "int1");
        $this->assertEquals(true, $result);
        $this->assertIsBool($result);


        $result = $config->getAsBool("complex", "key", "float1");
        $this->assertEquals(true, $result);
        $this->assertIsBool($result);


        $result = $config->getAsBool("complex", "key", "boolTrue");
        $this->assertEquals(true, $result);
        $this->assertIsBool($result);


        $result = $config->getAsBool("complex", "key", "boolFalse");
        $this->assertEquals(false, $result);
        $this->assertIsBool($result);


        $result = $config->getAsBool("complex", "key", "null");
        $this->assertEquals(false, $result);
        $this->assertIsBool($result);


        $this->expectException(ConversionException::class);
        $config->getAsBool("complex", "key", "array");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCannotGetStringAsArray() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);


        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "string");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCannotGetIntAsArray() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);


        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "int1");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCannotGetFloatAsArray() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);


        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "float1");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCannotGetBoolTrueAsArray() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);


        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "boolTrue");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCannotGetBoolFalseAsArray() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);


        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "boolFalse");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCannotGetNullAsArray() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);


        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "null");
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testCanGetAsArray() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);

        $result = $config->getAsArray("complex", "key", "array");
        $this->assertEquals([
            "value1",
            "value2",
            "value3",
            "value4",
            "value5"
        ], $result);
        $this->assertIsArray($result);
    }

    /** @covers \DavidLienhard\Config\Config */
    public function testJsonAndYmlAreIdentical() : void
    {
        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.json", self::$files['complex']);

        $config = new Config("/", $filesystem);
        $jsonData = $config->get("complex");


        $filesystem = $this->getFilesystem();
        $filesystem->write("complex.yml", self::$files['complexYaml']);

        $config = new Config("/", $filesystem);
        $ymlData = $config->get("complex");

        $this->assertEquals($jsonData, $ymlData);
    }
}
