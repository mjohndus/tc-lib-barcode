<?php

/**
 * GsOneDataBarStackedOmniTest.php
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
 * GS1 DataBar Stacked Omnidirectional Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarStackedOmniTest extends TestUtil
{
    /**
     * Height in modules of each of the two rows
     */
    private const ROW_HEIGHT = 33;

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
            ['00034567890125', '84c2c1d762ab17924c3b6d06cf4ca349'],
            ['1',              'd5298ecc048eef84fbb9d3d98603f1c6'],
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
        $type = $barcode->getBarcodeObj('DATABARSTACKOMNI', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol of Figure 5-43 of the GS1 General Specifications: two rows of
     * 33 modules divided by a separator pattern of 3 modules.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbol(): void
    {
        $rows = $this->getGridRows('DATABARSTACKOMNI', '00034567890125');
        $this->assertCount(69, $rows);
        $this->assertSame('01010100100000000100111110000001010011100110011010', $rows[0] ?? '');
        $this->assertSame('10101011011111111011010101010110101100011001100101', $rows[33] ?? '');
        $this->assertSame('01010101010101010101010101010101010101010101010101', $rows[34] ?? '');
        $this->assertSame('01001000100010111010101010101000111101001101110010', $rows[35] ?? '');
        $this->assertSame('10110111011101000101100000000111000010110010001101', $rows[36] ?? '');

        foreach ($rows as $idx => $row) {
            if ($idx < self::ROW_HEIGHT) {
                $this->assertSame($rows[0] ?? '', $row);
            } elseif ($idx > (self::ROW_HEIGHT + 2)) {
                $this->assertSame($rows[36] ?? '', $row);
            }
        }
    }

    /**
     * The middle row of the separator pattern alternates over its whole width.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSeparatorMiddleRow(): void
    {
        foreach (['00034567890125', '09501101530010', '1'] as $code) {
            $rows = $this->getGridRows('DATABARSTACKOMNI', $code);
            $this->assertSame(\str_repeat('01', 25), $rows[34] ?? '');
        }
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('DATABARSTACKOMNI', '00034567890125')->getArray();
        $this->assertSame(50, $data['ncols']);
        $this->assertSame(69, $data['nrows']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetExtendedCode(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            '(01)00034567890125',
            $barcode->getBarcodeObj('DATABARSTACKOMNI', '0003456789012')->getExtendedCode(),
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
        $barcode->getBarcodeObj('DATABARSTACKOMNI', 'GHI');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCodeTooLong(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABARSTACKOMNI', '000345678901251');
    }
}
