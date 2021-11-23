<?php declare(strict_types=1);

/**
 * contains the parser for neon files
 *
 * @package         tourBase
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
 */

namespace DavidLienhard\Config\Parser;

use DavidLienhard\Config\Exceptions\FileMismatch as FileMismatchException;
use DavidLienhard\Config\Exceptions\Parse as ParseException;
use DavidLienhard\Config\Parser\ParserAbstract;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon as NetteNeon;

/**
 * parses neon data to an array
 *
 * @author          David Lienhard <github@lienhard.win>
 * @copyright       David Lienhard
*/
class Neon extends ParserAbstract implements ParserInterface
{
    /**
     * list of supported file-endings of this parser
     * @var array<int, string>
     */
    public static array $supportedFiletypes = [ "neon" ];

    /**
     * expects the file-content and returns it as an array
     *
     * @author          David Lienhard <github@lienhard.win>
     * @copyright       David Lienhard
     * @param           string          $fileContent    content of the file to parse
     * @throws          ParseException                  if neon file cannot be parsed
     */
    public function parse(string $fileContent) : array
    {
        try {
            $config = NetteNeon::decode($fileContent) ?? [];
        } catch (NeonException $e) {
            throw new ParseException(
                "could not parse config file: ".$e->getMessage(),
                \intval($e->getCode()),
                $e
            );
        }

        if (!\is_array($config)) {
            throw new FileMismatchException("data must be array at this point");
        }

        return $config;
    }
}
