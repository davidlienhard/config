<?php declare(strict_types=1);

namespace DavidLienhard;

use DavidLienhard\Config\ConfigInterface;
use DavidLienhard\Config\Exceptions\Conversion as ConversionException;
use DavidLienhard\Config\Exceptions\FileMismatch as FileMismatchException;
use DavidLienhard\Config\Exceptions\KeyMismatch as KeyMismatchException;
use DavidLienhard\Config\Stub as Config;
use PHPUnit\Framework\TestCase;

class StubTest extends TestCase
{
    protected static array $payload = [];

    public static function setUpBeforeClass() : void
    {
        self::$payload['simple'] = [ "key" => "value" ];

        self::$payload['complex'] = [
            "key" => [
                "string"    => "string value",
                "array"     => [
                    "value1",
                    "value2",
                    "value3",
                    "value4",
                    "value5"
                ],
                "boolTrue"  => true,
                "boolFalse" => false,
                "null"      => null,
                "int1"      => 5,
                "int2"      => -6,
                "float1"    => 121.181,
                "float2"    => -1516.51
            ]
        ];

        /* self::$payload['invalid'] = <<<CODE
        {
            "key
        }
        CODE; */

        self::$payload['env'] = [ "key" => "ENV:TEST_VARIABLE" ];
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanBeCreated(): void
    {
        $config = new Config("/");
        $this->assertInstanceOf(Config::class, $config);
        $this->assertInstanceOf(ConfigInterface::class, $config);
    }
    /** @covers \DavidLienhard\Config\Stub */
    public function testCannotBeCreatedWithoutFolder(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new Config;
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testThrowsExceptionIfMainKeyDoesNotExist() : void
    {
        $config = new Config("/");
        $this->expectException(FileMismatchException::class);
        $this->expectExceptionMessage("file '/doesNotExist.json' does not exist");
        $config->get("doesNotExist");
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanReadSimpleFile() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);
        $this->assertEquals([ "key" => "value" ], $config->get("simple"));
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanReadComplexFile() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

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

    /** @covers \DavidLienhard\Config\Stub */
    public function testNotExistingSubKeyReturnsNull() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        $this->expectException(KeyMismatchException::class);
        $config->get("simple", "doesnotexist");
    }

    /** @covers \DavidLienhard\Config\Stub */
    /* public function testThrowsExceptionOnInvalidJsonFile() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        $this->expectException(FileMismatchException::class);
        $this->expectExceptionMessageMatches("/^could not parse config file:/");
        $this->assertEquals(null, $config->get("invalid"));
    } */

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanReadEnvData() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        putenv("TEST_VARIABLE=testvalue");
        $this->assertEquals("testvalue", $config->get("env", "key"));
        putenv("TEST_VARIABLE");
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanGetDirectory() : void
    {
        $config = new Config("/test/directory");
        $config->addPayload(self::$payload);

        $this->assertEquals("/test/directory", $config->getDirectory());
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanGetAsString() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

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

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanGetAsInt() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

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

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanGetAsFloat() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

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

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanGetAsBool() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

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

    /** @covers \DavidLienhard\Config\Stub */
    public function testCannotGetStringAsArray() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "string");
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCannotGetIntAsArray() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "int1");
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCannotGetFloatAsArray() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "float1");
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCannotGetBoolTrueAsArray() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "boolTrue");
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCannotGetBoolFalseAsArray() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "boolFalse");
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCannotGetNullAsArray() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

        $this->expectException(ConversionException::class);
        $config->getAsArray("complex", "key", "null");
    }

    /** @covers \DavidLienhard\Config\Stub */
    public function testCanGetAsArray() : void
    {
        $config = new Config("/");
        $config->addPayload(self::$payload);

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
}
