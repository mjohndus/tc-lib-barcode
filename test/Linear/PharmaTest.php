<?php

/**
 * PharmaTest.php
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
class PharmaTest extends TestUtil
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
        $type = $barcode->getBarcodeObj('PHARMA', '123456');
        $grid = $type->getGrid();
        $expected = "1110011100111001001001001110010010011100100100100100100111\n";
        $this->assertEquals($expected, $grid);
    }

    /**
     * Pharmacode encodes the values 3 to 131070 (3 to 16 bars).
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testRangeBoundaries(): void
    {
        $barcode = $this->getTestObject();
        $this->assertEquals("1001\n", $barcode->getBarcodeObj('PHARMA', '3')->getGrid());
        $this->assertEquals(78, $barcode->getBarcodeObj('PHARMA', '131070')->getArray()['ncols']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('outOfRangeDataProvider')]
    public function testOutOfRange(string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('PHARMA', $code);
    }

    /**
     * @return array<array{string}>
     */
    public static function outOfRangeDataProvider(): array
    {
        return [
            ['0'],
            ['1'],
            ['2'],
            ['131071'],
            ['0123456789'],
            ['999999999999999999999'],
        ];
    }
}
