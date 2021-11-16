<?php declare(strict_types=1);

/**
 * contains the parser for json files
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Config\Parser;

/**
 * parses json data to an array
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
*/
abstract class ParserAbstract implements ParserInterface
{
    /**
     * list of supported file-endings of this parser
     * @var array<int, string>
     */
    public static array $supportedFiletypes;

    /**
     * expects the file-content and returns it as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $fileContent    content of the file to parse
     */
    abstract public function parse(string $fileContent) : array;

    /**
     * returns list of supported filetypes of this parser
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @return          array<int, string>
     */
    public static function getSupportedFiletypes() : array
    {
        return static::$supportedFiletypes;
    }
}
