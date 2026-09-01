<?php

/**
 * IataTwoOfFiveTest.php
 *
 * @since       2026-09-01
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
 * 2 of 5 IATA Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class IataTwoOfFiveTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * The start pattern is a narrow bar and a narrow bar, the stop pattern a
     * wide bar and a narrow bar. The digit patterns are the ones of Standard 2 of 5.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolStructure(): void
    {
        $barcode = $this->getTestObject();
        $grid = \rtrim($barcode->getBarcodeObj('S25IATA', '1')->getGrid());
        // start pattern and separator, the digit 1 and its separator, stop pattern
        $this->assertSame('10101110101010111011101', $grid);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['0123456789', 'b55d0659f42a401d503cb22a73e5e83d'],
            ['1',          '1d6bf50d49953fff1441ac13811add01'],
            ['9876543210', '0ba56b574e5f9d7a539cd5bb78835112'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getGridDataProvider')]
    public function testGetGrid(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('S25IATA', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The digits are drawn exactly as in Standard 2 of 5: the symbols only
     * differ by the 6 modules of the start pattern and the 4 of the stop pattern.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testDigitsMatchStandardTwoOfFive(): void
    {
        $barcode = $this->getTestObject();
        $iata = \rtrim($barcode->getBarcodeObj('S25IATA', '0123456789')->getGrid());
        $s25 = \rtrim($barcode->getBarcodeObj('S25', '0123456789')->getGrid());
        $this->assertSame(\substr($s25, 10, -9), \substr($iata, 4, -5));
        $this->assertSame(149, \strlen($iata));
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            ['abc'],
            ['12A45'],
            ['12 45'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidCodeProvider')]
    public function testInvalidInput(string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('S25IATA', $code);
    }
}
