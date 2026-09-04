<?php

/**
 * GsOneDataBarTruncatedTest.php
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
 * GS1 DataBar Truncated Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarTruncatedTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['00012345678905', '191f46438eaadece4cfb0f62813104fe'],
            ['1',              '3ba365dbc203203ba81c0266de4737fb'],
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
        $type = $barcode->getBarcodeObj('DATABARTRUNC', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol of Figure 5-41 of the GS1 General Specifications.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbol(): void
    {
        $grid = $this->getTestObject()->getBarcodeObj('DATABARTRUNC', '00012345678905')->getGridArray();
        $this->assertSame('010101001000000001001111111000010111001011011110111001010110000101111111000111001100111101110101', \implode(
            '',
            $grid[0] ?? [],
        ));
    }

    /**
     * The symbol is the one of GS1 DataBar Omnidirectional, 13 modules high
     * instead of 33.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('DATABARTRUNC', '00012345678905')->getArray();
        $this->assertSame(96, $data['ncols']);
        $this->assertSame(13, $data['nrows']);

        $omni = $barcode->getBarcodeObj('DATABAR', '00012345678905')->getGridArray();
        $trunc = $barcode->getBarcodeObj('DATABARTRUNC', '00012345678905')->getGridArray();
        $this->assertSame($omni[0] ?? [], $trunc[0] ?? []);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetExtendedCode(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            '(01)00012345678905',
            $barcode->getBarcodeObj('DATABARTRUNC', '0001234567890')->getExtendedCode(),
        );
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABARTRUNC', 'GHI');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCodeTooLong(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABARTRUNC', '000123456789051');
    }
}
