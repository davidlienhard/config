# davidlienhard/config
🐘 php library to get configuration data from json files

[![Latest Stable Version](https://img.shields.io/packagist/v/davidlienhard/config.svg?style=flat-square)](https://packagist.org/packages/davidlienhard/config)
[![Source Code](https://img.shields.io/badge/source-davidlienhard/config-blue.svg?style=flat-square)](https://github.com/davidlienhard/config)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/davidlienhard/config/blob/master/LICENSE)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.0-8892BF.svg?style=flat-square)](https://php.net/)
[![CI Status](https://github.com/davidlienhard/config/actions/workflows/check.yml/badge.svg)](https://github.com/davidlienhard/config/actions/workflows/check.yml)

## Setup

You can install through `composer` with:

```
composer require davidlienhard/config:^2
```

*Note: davidlienhard/config requires PHP 8.0*

## Examples

### Setup
```php
<?php declare(strict_types=1);
use DavidLienhard\Config\Config;

try {
    $config = new Config("path/to/config");
} catch (\Throwable $t) {
    echo "unable to setup config";
    exit(1);
}
```

### Read Data
**Example Config File**: *system.json*
```json
{
    "name": "test",
    "list1": {
        "key1": "value1",
        "key2": "value2",
        "key3": "value3",
        "key4": "value4"
    },
    "list2": [
        "value1",
        "value2",
        "value3",
        "value4"
    ]
}
```

**get single value**
```php
<?php declare(strict_types=1);

echo $config->get("system", "name");
/* test */

echo $config->get("system", "list1", "key1");
/* value1 */
```

**get single value with a specific type**
```php
<?php declare(strict_types=1);

echo $config->getAsString("system", "name");
/*
 if the value exists and is not an array, it will return a string in any case
 if the value is an array, this will throw an exception
 if the value does not exist this will return null
*/
```

the following methods do exists
 - `getAsString()`
 - `getAsInt()`
 - `getAsFloat()`
 - `getAsBool()`
 - `getAsArray()`


**get associative array**
```php
<?php declare(strict_types=1);

print_r($config->get("system", "list1"));
/*
    Array
    (
        [key1] => value1
        [key2] => value2
        [key3] => value3
        [key4] => value4
    )
*/
```

**get numeric array**
```php
<?php declare(strict_types=1);

print_r($config->get("system", "list2"));
/*
    Array
    (
        [0] => value1
        [1] => value2
        [2] => value3
        [3] => value4
    )
*/
```

**get not existing value**
```php
<?php declare(strict_types=1);

var_dump($config->get("system", "doesnotexist"));
/* NULL */
```

**get data from not existing file**
```php
<?php declare(strict_types=1);

var_dump($config->get("doesnotexist"));
/* throws \Exception */
```

## Exceptions
The library currently contains the following exceptions

 - `Config` - Main Exception that is parent of all other exceptions
  - `Conversion` - Errors that happen during type conversion. eg trying to convert a string to an array
  - `Mismatch` - Trying to access configuration data that is not available
    - `FileMismatch` - Trying to access a file that does not exist
    - `KeyMismatch` - Trying to access a key that does not exists, while the file is present

## License

The MIT License (MIT). Please see [LICENSE](https://github.com/davidlienhard/config/blob/master/LICENSE) for more information.
