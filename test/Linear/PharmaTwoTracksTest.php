<?php

/**
 * PharmaTwoTracksTest.php
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
class PharmaTwoTracksTest extends TestUtil
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
        $type = $barcode->getBarcodeObj('PHARMA2T', '123456');
        $grid = $type->getGrid();
        $expected = "001010001010101010101\n100010101010000010101\n";
        $this->assertEquals($expected, $grid);
    }

    /**
     * Two-track Pharmacode encodes the values 4 to 64570080 (2 to 16 tracks).
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testRangeBoundaries(): void
    {
        $barcode = $this->getTestObject();
        $this->assertEquals("000\n101\n", $barcode->getBarcodeObj('PHARMA2T', '4')->getGrid());
        $this->assertEquals(31, $barcode->getBarcodeObj('PHARMA2T', '64570080')->getArray()['ncols']);
    }

    /**
     * Values outside the encodable range are rejected.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('outOfRangeDataProvider')]
    public function testOutOfRange(string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('PHARMA2T', $code);
    }

    /**
     * @return array<array{string}>
     */
    public static function outOfRangeDataProvider(): array
    {
        return [
            ['0'],
            ['3'],
            ['64570081'],
            ['0123456789'],
            ['999999999999999999999'],
        ];
    }
}
