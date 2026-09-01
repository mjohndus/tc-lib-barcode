<?php

/**
 * CodeOneTwoEightTest.php
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
use Test\Fixture\InternalCodeOneTwoEight;
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
class CodeOneTwoEightTest extends TestUtil
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
        $bobj = $barcode->getBarcodeObj('C128', '0123456789');
        $grid = $bobj->getGrid();
        $expected = "110100111001100110110011101101110101110110001000010110011011011110100001101001100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', '1PBK500EI');
        $grid = $bobj->getGrid();
        $expected =
            '110100100001001110011011101110110100010110001011000111011011100100100111011001001'
            . "11011001000110100011000100010111011001001100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', 'SCB500J1C3Y');
        $grid = $bobj->getGrid();
        $expected =
            '110100100001101110100010001000110100010110001101110010010011101100100111011001011'
            . "011100010011100110100010001101100101110011101101000110110111101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', '067023611120229212');
        $grid = $bobj->getGrid();
        $expected =
            '110100111001001100100010110000100111011011101100100001011000100100110010011101100111010010101111'
            . "00010110011100110110111101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', 'Data:28102003');
        $grid = $bobj->getGrid();
        $expected =
            '110100100001011000100010010110000100111101001001011000011100100110101110111101110011010011001000'
            . "1001100100111010010011000100010001101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', '12345678901');
        $grid = $bobj->getGrid();
        $expected =
            '110100100001001110011010111011110111011011101011101100010000101100110110111101100110110011001100'
            . "1101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', '1234');
        $grid = $bobj->getGrid();
        $expected = "110100111001011001110010001011000100100111101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', 'hello');
        $grid = $bobj->getGrid();
        $expected = "110100100001001100001010110010000110010100001100101000010001111010100010011001100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', 'HI345678');
        $grid = $bobj->getGrid();
        $expected =
            '110100100001100010100011000100010101110111101000101100011100010110110000101001000010011011000'
            . "11101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', 'HI34567A');
        $grid = $bobj->getGrid();
        $expected =
            '1101001000011000101000110001000101100101110010111011110101110110001000010110010111101110101000'
            . "11000100100011001100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', 'Barcode 1');
        $grid = $bobj->getGrid();
        $expected =
            '110100100001000101100010010110000100100111101000010110010001111010100001001101011001'
            . "00001101100110010011100110111011000101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', "C1\tC2\tC3");
        $grid = $bobj->getGrid();
        $expected =
            '11010000100100010001101001110011010000110100100010001101100111001010000110100100010001101100101'
            . "1100100110011101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', 'A1b2c3D4e5');
        $grid = $bobj->getGrid();
        $expected =
            '1101001000010100011000100111001101001000011011001110010100001011001100101110010110001'
            . "000110010011101011001000011011100100100001101001100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', \chr(241) . '0000801234999999999');
        $grid = $bobj->getGrid();
        $expected =
            '1101001000011110101110100111011001011101111011011001100100011001001100110110011101101'
            . "1101101000111010111011110101110111101011101111010111011110110100010001100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', \chr(241) . "000000\tABCDEF");
        $grid = $bobj->getGrid();
        $expected =
            '1101001110011110101110110110011001101100110011011001100111010111101000011010010100011000100010'
            . "1100010001000110101100010001000110100010001100010111010111101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', "\tABCD\tEFGH");
        $grid = $bobj->getGrid();
        $expected =
            '1101000010010000110100101000110001000101100010001000110101100010001000011010010001101000100011'
            . "000101101000100011000101000100011101101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', "\tABCD\tabc\tABCdef");
        $grid = $bobj->getGrid();
        $expected =
            '1101000010010000110100101000110001000101100010001000110101100010001000011010010111101110100101'
            . '10000100100001101000010110011101011110100001101001010001100010001011000100010001101011110111010000100'
            . "1101011001000010110000100100001100101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', "\tABCD\tabc\tdef");
        $grid = $bobj->getGrid();
        $expected =
            '1101000010010000110100101000110001000101100010001000110101100010001000011010010111101110100101'
            . '10000100100001101000010110011110100010100001101001000010011010110010000101100001001111010100011000111'
            . "01011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', \chr(241) . "\tABCD");
        $grid = $bobj->getGrid();
        $expected =
            '1101000010011110101110100001101001010001100010001011000100010001101011000100011000101110110001'
            . "1101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', "\ta");
        $grid = $bobj->getGrid();
        $expected = "11010000100100001101001111010001010010110000110111000101100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', \chr(241) . '00123456780000000001');
        $grid = $bobj->getGrid();
        $expected =
            '1101001110011110101110110110011001011001110010001011000111000101101100001010011011001100110110'
            . "01100110110011001101100110011001101100100010011001100011101011\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('C128', \chr(241) . '42029651' . \chr(241) . '9405510200864168997758');
        $grid = $bobj->getGrid();
        $expected =
            '1101001110011110101110101101110001100110011010111100010110111010001011110111011110101110101110'
            . '1111010001011110100010011001101110100011001100110110110011001111010010011000100010100001001101'
            . "01110111101111011101011101100010100101100001100011101011\n";
        $this->assertEquals($expected, $grid);
    }

    /**
     * A leading FNC1 is encoded once, as the symbol character that follows the
     * start character, whichever code set the start character selects.
     *
     * @return array<int, array{string, string}>
     */
    public static function leadingFunctionCharacterProvider(): array
    {
        return [
            // start code set C
            ['C128C', \chr(241) . '0109501101020917'],
            // start code set A
            ['C128A', \chr(241) . "\tABCD"],
            // start code set B
            ['C128B', \chr(241) . 'abcd'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('leadingFunctionCharacterProvider')]
    public function testLeadingFunctionCharacterIsNotRepeated(string $type, string $code): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj($type, $code)->getGrid(),
            $barcode->getBarcodeObj('C128', $code)->getGrid(),
        );
    }

    /**
     * Of the four function characters only FNC1 exists in code set C, as the
     * symbol character 102, so only a leading FNC1 is carried by a start code
     * set C. The other three are encoded in code set B, which the start
     * character selects, and the digits that follow them take a code set C
     * switch of their own.
     *
     * @return array<int, array{string, array<int, int>}>
     */
    public static function leadingFunctionCharacterCodeDataProvider(): array
    {
        return [
            // start code set C, then FNC1
            [\chr(241) . '12345678', [105, 102, 12, 34, 56, 78]],
            // start code set B, then FNC2, then the code set C switch
            [\chr(242) . '12345678', [104, 97, 99, 12, 34, 56, 78]],
            // the same for FNC3
            [\chr(243) . '12345678', [104, 96, 99, 12, 34, 56, 78]],
            // FNC4 is 100 in code set B, not the 101 of code set A
            [\chr(244) . '12345678', [104, 100, 99, 12, 34, 56, 78]],
        ];
    }

    /**
     * @param array<int, int> $expected
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('leadingFunctionCharacterCodeDataProvider')]
    public function testLeadingFunctionCharacterCodeSet(string $code, array $expected): void
    {
        $type = new InternalCodeOneTwoEight($code);
        $this->assertSame($expected, \array_slice($type->exposeCodeData(), 0, \count($expected)));
    }

    /**
     * A leading FNC2 or FNC3 must not read as the digit pair that its code set
     * A value would be in code set C.
     *
     * @return array<int, array{string, string}>
     */
    public static function functionCharacterCollisionProvider(): array
    {
        return [
            [\chr(242) . '12345678', '9712345678'],
            [\chr(243) . '12345678', '9612345678'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('functionCharacterCollisionProvider')]
    public function testFunctionCharacterDoesNotCollideWithDigits(string $code, string $digits): void
    {
        $barcode = $this->getTestObject();
        $this->assertNotSame(
            $barcode->getBarcodeObj('C128', $digits)->getGrid(),
            $barcode->getBarcodeObj('C128', $code)->getGrid(),
        );
    }

    /**
     * A single character of the other code set takes one shift character, while
     * a longer run takes a latch character on each side.
     *
     * @return array<int, array{string, int}>
     */
    public static function shiftProvider(): array
    {
        // code and number of symbol characters, the start and the check
        // character included
        return [
            // the two control characters belong to Code Set A only
            ["\x01\x01", 4],
            // the lower case letter takes a shift to Code Set B
            ["\x01a\x01", 6],
            // two of them take a latch to Code Set B and back
            ["\x01ab\x01", 8],
            ["\x01abc\x01", 9],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('shiftProvider')]
    public function testShiftCharacter(string $code, int $chars): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('C128', $code)->getArray();

        // every symbol character is 11 modules, the stop character is 13
        $this->assertSame((11 * $chars) + 13, $data['ncols']);
    }
}
