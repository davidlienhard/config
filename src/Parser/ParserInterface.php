<?php declare(strict_types=1);

/**
 * interface or file-parsers
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Config\Parser;

/**
 * defines the interface for parsers
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
*/
interface ParserInterface
{
    /**
     * expects the file-content and returns it as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $fileContent    content of the file to parse
     */
    public function parse(string $fileContent) : array;

    /**
     * returns list of supported filetypes of this parser
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @return          array<int, string>
     */
    public static function getSupportedFiletypes() : array;
}
