<?php declare(strict_types=1);

/**
 * contains the parser for json files
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Config\Parser;

use DavidLienhard\Config\Exceptions\FileMismatch as FileMismatchException;
use DavidLienhard\Config\Exceptions\Parse as ParseException;
use DavidLienhard\Config\Parser\ParserAbstract;

/**
 * parses json data to an array
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
*/
class Json extends ParserAbstract implements ParserInterface
{
    /**
     * list of supported file-endings of this parser
     * @var array<int, string>
     */
    public static array $supportedFiletypes = [ "json" ];

    /**
     * expects the file-content and returns it as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $fileContent    content of the file to parse
     * @throws          ParseException                  if json file cannot be parsed
     */
    public function parse(string $fileContent) : array
    {
        $config = \json_decode($fileContent, true);
        if ($config === null) {
            throw new ParseException(
                "could not parse config file: ".\json_last_error_msg(),
                \json_last_error()
            );
        }

        if (!\is_array($config)) {
            throw new FileMismatchException("data must be array at this point");
        }

        return $config;
    }
}
