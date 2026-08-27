<?php

/**
 * UpcETest.php
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
class UpcETest extends TestUtil
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
        $bobj = $barcode->getBarcodeObj('UPCE', '725270');
        $grid = $bobj->getGrid();
        $expected = "101001000100100110110001001101101110110100111010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725271');
        $grid = $bobj->getGrid();
        $expected = "101001000100100110111001001001101110110110011010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725272');
        $grid = $bobj->getGrid();
        $expected = "101001000100100110111001001001100100010010011010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725273');
        $grid = $bobj->getGrid();
        $expected = "101001000100100110110001001101101110110100001010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725274');
        $grid = $bobj->getGrid();
        $expected = "101001000100100110110001001101100100010100011010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725275');
        $grid = $bobj->getGrid();
        $expected = "101001000100100110111001001101101110110110001010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725276');
        $grid = $bobj->getGrid();
        $expected = "101001000100110110110001001101101110110101111010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725277');
        $grid = $bobj->getGrid();
        $expected = "101001000100100110111001001001101110110010001010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725278');
        $grid = $bobj->getGrid();
        $expected = "101001000100100110110001001101100100010110111010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '725279');
        $grid = $bobj->getGrid();
        $expected = "101001000100110110110001001001100100010001011010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '4210000526');
        $grid = $bobj->getGrid();
        $expected = "101001110100100110111001001101101011110011001010101\n";
        $this->assertEquals($expected, $grid);

        // one input per compression case, plus number system 1
        $bobj = $barcode->getBarcodeObj('UPCE', '0120000034');
        $grid = $bobj->getGrid();
        $expected = "101010011101100110100111011110101000110010011010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '01200000345');
        $grid = $bobj->getGrid();
        $expected = "101011001100100110111101001110101110010001101010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '01234500005');
        $grid = $bobj->getGrid();
        $expected = "101011001100100110100001010001101100010111001010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '01234000005');
        $grid = $bobj->getGrid();
        $expected = "101011001100110110111101010001101100010011101010101\n";
        $this->assertEquals($expected, $grid);

        $bobj = $barcode->getBarcodeObj('UPCE', '112000003452');
        $grid = $bobj->getGrid();
        $expected = "101001100100100110100001001110101100010100111010101\n";
        $this->assertEquals($expected, $grid);
    }

    /**
     * Non-numeric input is rejected.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testNonNumericInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('UPCE', 'abcdefg');
    }

    /**
     * A UPC-A code whose product field does not carry the zeros the compression rule
     * requires has no UPC-E representation and is rejected.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('notRepresentableDataProvider')]
    public function testNotRepresentable(string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('UPCE', $code);
    }

    /**
     * @return array<array{string}>
     */
    public static function notRepresentableDataProvider(): array
    {
        return [
            ['0123456789'], // decoded as 00123400009
            ['012345678912'], // decoded as 01210000345
            ['4240000526'], // decoded as 04240000026
            ['4241000526'], // decoded as 04241000006
        ];
    }

    /**
     * UPC-E is defined for number system 0 and 1 only; any other value is rejected.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidNumberSystemDataProvider')]
    public function testInvalidNumberSystem(string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('UPCE', $code);
    }

    /**
     * @return array<array{string}>
     */
    public static function invalidNumberSystemDataProvider(): array
    {
        return [['523456789010'], ['923456789018'], ['212000003457']];
    }

    /**
     * Every 6-digit UPC-E code is representable by construction and is accepted.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testAllSixDigitCodesAreAccepted(): void
    {
        $barcode = $this->getTestObject();
        for ($num = 0; $num < 1_000_000; $num += 4001) {
            $code = \sprintf('%06d', $num);
            $this->assertNotSame('', $barcode->getBarcodeObj('UPCE', $code)->getGrid(), $code);
        }
    }
}
