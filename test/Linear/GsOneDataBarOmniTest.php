<?php

/**
 * GsOneDataBarOmniTest.php
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
use Test\Fixture\InternalGsOneDataBarOmni;
use Test\TestUtil;

/**
 * GS1 DataBar Omnidirectional Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarOmniTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Get the first row of the barcode grid
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getFirstRow(string $type, string $code): string
    {
        $grid = $this->getTestObject()->getBarcodeObj($type, $code)->getGridArray();
        return \implode('', $grid[0] ?? []);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['09501101530010', 'dc4a469075d57ddf3d8d6e2ae684cc92'],
            ['00012345678905', '30c509678b8a5788186c8d3e8ff89411'],
            ['1',              'f77bf5346662e84c2c192279b7808d66'],
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
        $type = $barcode->getBarcodeObj('DATABAR', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol of Figure 5-40 of the GS1 General Specifications.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbol(): void
    {
        $this->assertSame('010000010100000101000111110000010111101101011100100011011101000101100000000111001110110111001101', $this->getFirstRow(
            'DATABAR',
            '09501101530010',
        ));
    }

    /**
     * The linked symbol of Annex F.1 of ISO/IEC 24724, whose element widths are
     * 11 31111333 13911 31131231 11214222 11553 21231313 11.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testLinkedReferenceSymbol(): void
    {
        $this->assertSame('010001010111000111011100000000010111010001001110101101111001100101111100000111001001110111011101', $this->getFirstRow(
            'DATABAR,1',
            '24012345678905',
        ));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            // full GTIN-14 with a valid check digit
            ['09501101530010', '(01)09501101530010'],
            // 13 data digits, check digit appended
            ['0950110153001',  '(01)09501101530010'],
            // shorter codes are left-padded with zeros
            ['1',              '(01)00000000000017'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('extendedCodeProvider')]
    public function testGetExtendedCode(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame($expected, $barcode->getBarcodeObj('DATABAR', $code)->getExtendedCode());
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('DATABAR', '09501101530010')->getArray();
        $this->assertSame(96, $data['ncols']);
        $this->assertSame(33, $data['nrows']);
    }

    /**
     * The symbol starts with a one module space and ends with a one module bar,
     * and its 46 elements alternate.
     *
     * @return array<int, array{string}>
     */
    public static function codeProvider(): array
    {
        return [['09501101530010'], ['00012345678905'], ['1'], ['9999999999999']];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('codeProvider')]
    public function testGuardPatternsAndElements(string $code): void
    {
        $row = $this->getFirstRow('DATABAR', $code);
        $this->assertSame('01', \substr($row, 0, 2));
        $this->assertSame('01', \substr($row, -2));
        $this->assertSame(46, \preg_match_all('/0+|1+/', $row));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAR', 'GHI');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEmptyInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAR', '');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCodeTooLong(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAR', '095011015300101');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidCheckDigit(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAR', '09501101530011');
    }

    /**
     * The group of a data character value is the last one that the value
     * reaches.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCharacterGroup(): void
    {
        $groups = [
            [0,   12, 5, 7, 2, 87, 4],
            [161, 10, 7, 5, 4, 52, 20],
            [961, 8,  9, 4, 5, 30, 52],
        ];
        $type = new InternalGsOneDataBarOmni('09501101530003');

        $this->assertSame($groups[0], $type->exposeCharacterGroup(0, $groups));
        $this->assertSame($groups[0], $type->exposeCharacterGroup(160, $groups));
        $this->assertSame($groups[1], $type->exposeCharacterGroup(161, $groups));
        $this->assertSame($groups[2], $type->exposeCharacterGroup(961, $groups));
        $this->assertSame($groups[2], $type->exposeCharacterGroup(1_000_000, $groups));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testOutOfRangeCharacterGroup(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $type = new InternalGsOneDataBarOmni('09501101530003');
        $type->exposeCharacterGroup(-1, [[0, 12, 5, 7, 2, 87, 4]]);
    }
}
