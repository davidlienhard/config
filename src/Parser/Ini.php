<?php declare(strict_types=1);

/**
 * contains the parser for ini files
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Config\Parser;

use DavidLienhard\Config\Exceptions\Parse as ParseException;
use DavidLienhard\Config\Parser\ParserAbstract;

/**
 * parses ini data to an array
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
*/
class Ini extends ParserAbstract implements ParserInterface
{
    /**
     * list of supported file-endings of this parser
     * @var array<int, string>
     */
    public static array $supportedFiletypes = [ "ini" ];

    /**
     * expects the file-content and returns it as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $fileContent    content of the file to parse
     * @throws          ParseException                  if ini file cannot be parsed
     */
    public function parse(string $fileContent) : array
    {
        $config = \parse_ini_string($fileContent, true);
        if ($config === false) {
            throw new ParseException("could not parse config file");
        }

        return $config;
    }
}
