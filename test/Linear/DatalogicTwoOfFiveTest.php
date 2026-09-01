<?php

/**
 * DatalogicTwoOfFiveTest.php
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
 * 2 of 5 Datalogic Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class DatalogicTwoOfFiveTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * The start pattern is two narrow bars and the stop pattern a wide bar and
     * a narrow bar. The digit patterns are the ones of 2 of 5 Matrix.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolStructure(): void
    {
        $barcode = $this->getTestObject();
        $grid = \rtrim($barcode->getBarcodeObj('S25DATALOGIC', '1')->getGrid());
        // start pattern and separator, the digit 1 and its separator, stop pattern
        $this->assertSame('1010111010111011101', $grid);
    }

    /**
     * The digits are drawn exactly as in 2 of 5 Matrix: the symbols only differ
     * by the 4 modules of the start pattern and the 2 of the stop pattern.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testDigitsMatchMatrixTwoOfFive(): void
    {
        $barcode = $this->getTestObject();
        $datalogic = \rtrim($barcode->getBarcodeObj('S25DATALOGIC', '0123456789')->getGrid());
        $matrix = \rtrim($barcode->getBarcodeObj('S25MATRIX', '0123456789')->getGrid());
        $this->assertSame(\substr($matrix, 8, -7), \substr($datalogic, 4, -5));
        $this->assertSame(109, \strlen($datalogic));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['0123456789', '2548f46f8b8320e83a35f25919611cdb'],
            ['1',          '436cb88014879700b52ea867cb07b849'],
            ['9876543210', '104b71c1b0bb8b4e3e2483b1e97a3a3e'],
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
        $type = $barcode->getBarcodeObj('S25DATALOGIC', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
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
        $barcode->getBarcodeObj('S25DATALOGIC', $code);
    }
}
