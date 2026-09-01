<?php

/**
 * GsOneDataBarStackedTest.php
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
 * GS1 DataBar Stacked Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarStackedTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Get the barcode grid as one string per row
     *
     * @return array<int, string>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getGridRows(string $type, string $code): array
    {
        $grid = $this->getTestObject()->getBarcodeObj($type, $code)->getGridArray();
        return \array_map(static fn(array $row): string => \implode('', $row), $grid);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['00012345678905', '5d3bbd03049445eaed89bb1d0f0f5fef'],
            ['1',              '57ec7f50b3c6508d84626afb75fa23ac'],
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
        $type = $barcode->getBarcodeObj('DATABARSTACK', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol of Figure 5-42 of the GS1 General Specifications: a top row of
     * 5 modules, a separator pattern of 1 module and a bottom row of 7 modules.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbol(): void
    {
        $rows = $this->getGridRows('DATABARSTACK', '00012345678905');
        $this->assertCount(13, $rows);
        $this->assertSame('01010100100000000100111111100001011100101101111010', $rows[0] ?? '');
        $this->assertSame('00001010101011111010000000111010100011010010000000', $rows[5] ?? '');
        $this->assertSame('10111001010110000101111111000111001100111101110101', $rows[6] ?? '');

        foreach ($rows as $idx => $row) {
            $expected = match (true) {
                $idx < 5 => $rows[0] ?? '',
                $idx === 5 => $rows[5] ?? '',
                default => $rows[6] ?? '',
            };
            $this->assertSame($expected, $row);
        }
    }

    /**
     * The two rows carry the halves of the GS1 DataBar Omnidirectional symbol,
     * each closed by a guard pattern of a one module bar and a one module space.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testRowsMatchTheOmnidirectionalHalves(): void
    {
        $rows = $this->getGridRows('DATABARSTACK', '09501101530010');
        $omni = $this->getGridRows('DATABAR', '09501101530010');
        $this->assertSame(\substr($omni[0] ?? '', 0, 48), \substr($rows[0] ?? '', 0, 48));
        $this->assertSame('10', \substr($rows[0] ?? '', 48));
        $this->assertSame(\substr($omni[0] ?? '', 48), \substr($rows[6] ?? '', 2));
        $this->assertSame('10', \substr($rows[6] ?? '', 0, 2));
    }

    /**
     * The four modules at each end of the separator pattern are spaces.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSeparatorMargins(): void
    {
        foreach (['00012345678905', '09501101530010', '1'] as $code) {
            $rows = $this->getGridRows('DATABARSTACK', $code);
            $this->assertSame('0000', \substr($rows[5] ?? '', 0, 4));
            $this->assertSame('0000', \substr($rows[5] ?? '', -4));
        }
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('DATABARSTACK', '00012345678905')->getArray();
        $this->assertSame(50, $data['ncols']);
        $this->assertSame(13, $data['nrows']);
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
            $barcode->getBarcodeObj('DATABARSTACK', '0001234567890')->getExtendedCode(),
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
        $barcode->getBarcodeObj('DATABARSTACK', 'GHI');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCodeTooLong(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABARSTACK', '000123456789051');
    }
}
