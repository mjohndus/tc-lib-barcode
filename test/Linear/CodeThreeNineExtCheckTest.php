<?php

/**
 * CodeThreeNineExtCheckTest.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Test\Linear;

use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalCodeThreeNineExtCheck;
use Test\TestUtil;

/**
 * Barcode class test
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class CodeThreeNineExtCheckTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetGrid(): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('C39E+', '0123456789');
        $grid = $type->getGrid();
        $expected =
            '10001011101110101010001110111010111010001010111010111000101011101110111000101010101000'
            . '111010111011101000111010101011100011101010101000101110111011101000101110101011100010111010101'
            . "1100010101110100010111011101\n";
        $this->assertEquals($expected, $grid);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('C39E+', \chr(218));
    }

    /**
     * The check character alphabet of CODE 39: the digits, the upper case
     * letters and six punctuation characters.
     *
     * @return array<int, array{string, int}>
     */
    public static function checksumAlphabetProvider(): array
    {
        return [
            ['0', 0],
            ['9', 9],
            ['A', 10],
            ['Z', 35],
            ['-', 36],
            ['.', 37],
            [' ', 38],
            ['$', 39],
            ['/', 40],
            ['+', 41],
            ['%', 42],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('checksumAlphabetProvider')]
    public function testChecksumAlphabet(string $char, int $index): void
    {
        $type = new InternalCodeThreeNineExtCheck('A');

        $this->assertSame($index, $type->exposeChecksumIndex($char));
        $this->assertSame($char, $type->exposeChecksumChar($index));
    }

    /**
     * A character outside the alphabet takes the value of the first one.
     *
     * @return array<int, array{string}>
     */
    public static function unknownChecksumCharProvider(): array
    {
        return [['a'], ['*'], ['#'], [''], ["\x00"]];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('unknownChecksumCharProvider')]
    public function testUnknownChecksumChar(string $char): void
    {
        $type = new InternalCodeThreeNineExtCheck('A');

        $this->assertSame(0, $type->exposeChecksumIndex($char));
    }
}
