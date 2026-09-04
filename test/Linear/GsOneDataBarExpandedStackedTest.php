<?php

/**
 * GsOneDataBarExpandedStackedTest.php
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
use Test\Fixture\GsOneDataBarExpandedDecoder;
use Test\Fixture\InternalGsOneDataBarExpandedStacked;
use Test\TestUtil;

/**
 * GS1 DataBar Expanded Stacked Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarExpandedStackedTest extends TestUtil
{
    /**
     * Height in modules of a row
     */
    private const ROW_HEIGHT = 34;

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
     * @return array<int, array{string, string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['DATABAREXPSTACK', '(01)90614141000015(3202)000150', 'be9ce84e6000b5891fd56034276be158'],
            ['DATABAREXPSTACK,0,4', '(01)98898765432106(3202)012345(15)991231', '9590d3b22adddd866a9c6d5ebf897189'],
            ['DATABAREXPSTACK,0,2', '(10)12A', '3419f297233a2a756bb58c7c5c32947c'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getGridDataProvider')]
    public function testGetGrid(string $type, string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $bctype = $barcode->getBarcodeObj($type, $code);
        $this->assertEquals($expected, \md5($bctype->getGrid()));
    }

    /**
     * The symbol of Figure 5-48 of the GS1 General Specifications: a row of four
     * segments, a separator pattern of three module rows and a last row of two
     * segments moved one module to the right.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbol(): void
    {
        $rows = $this->getGridRows('DATABAREXPSTACK', '(01)90614141000015(3202)000150');
        $this->assertCount(71, $rows);
        $this->assertSame(
            '010110001100110000101111111100001010010001000011110111001110001010001011110000001110011101011111101101',
            $rows[0] ?? '',
        );
        $this->assertSame(
            '000001110011001111010000000010100101101110111100001000110001110101110100001010100001100010100000010000',
            $rows[34] ?? '',
        );
        $this->assertSame(
            '000001010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010000',
            $rows[35] ?? '',
        );
        $this->assertSame(
            '000001011111011111001010000001010010111111011100100000000000000000000000000000000000000000000000000000',
            $rows[36] ?? '',
        );
        $this->assertSame(
            '001010100000100000110001111110000101000000100011010010000000000000000000000000000000000000000000000000',
            $rows[37] ?? '',
        );
    }

    /**
     * The first row is the head of the single row symbol.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testFirstRowMatchesTheSingleRowSymbol(): void
    {
        $code = '(01)90614141000015(3202)000150';
        $stacked = $this->getGridRows('DATABAREXPSTACK', $code);
        $single = $this->getGridRows('DATABAREXP', $code);
        // the row ends with a guard pattern of a one module space and a one module bar
        $this->assertSame(\substr($single[0] ?? '', 0, 100), \substr($stacked[0] ?? '', 0, 100));
        $this->assertSame('01', \substr($stacked[0] ?? '', 100));
    }

    /**
     * The second row and the following even rows start with a bar when they hold
     * a number of segments that is a multiple of four.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testMirroredEvenRow(): void
    {
        $rows = $this->getGridRows('DATABAREXPSTACK,0,4', '(01)98898765432106(3202)012345(15)991231');
        $this->assertCount(71, $rows);
        $this->assertSame('0', \substr($rows[0] ?? '', 0, 1));
        $this->assertSame('1', \substr($rows[37] ?? '', 0, 1));
    }

    /**
     * @return array<int, array{string, int, int, int}>
     */
    public static function layoutProvider(): array
    {
        return [
            // 6 symbol characters over rows of 4 and 2 segments
            ['(01)90614141000015(3202)000150',           4, 102, 71],
            // 6 symbol characters over three rows of 2 segments
            ['(01)90614141000015(3202)000150',           2, 53,  108],
            // 8 symbol characters over two rows of 4 segments
            ['(01)98898765432106(3202)012345(15)991231', 4, 102, 71],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('layoutProvider')]
    public function testLayout(string $code, int $segments, int $ncols, int $nrows): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('DATABAREXPSTACK,0,' . $segments, $code)->getArray();
        $this->assertSame($ncols, $data['ncols']);
        $this->assertSame($nrows, $data['nrows']);
        $this->assertSame(0, ($nrows - self::ROW_HEIGHT) % (self::ROW_HEIGHT + 3));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetExtendedCode(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame('(10)12A', $barcode->getBarcodeObj('DATABAREXPSTACK,0,2', '(10)12A')->getExtendedCode());
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidSegmentsProvider(): array
    {
        return [['3'], ['0'], ['22'], ['-2']];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidSegmentsProvider')]
    public function testInvalidSegments(string $segments): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAREXPSTACK,0,' . $segments, '(10)12A');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEmptyInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAREXPSTACK', '');
    }

    /**
     * The stacked symbol takes one more symbol character when the last row would
     * hold a single one, so the general purpose data compaction has to size the
     * trailing digit against that count. Otherwise the digit takes the four bit
     * form while twelve more bits follow it, and a decoder reads a seven bit
     * value instead.
     *
     * @return array<int, array{string}>
     */
    public static function segmentsProvider(): array
    {
        return [['2'], ['4'], ['6'], ['8'], ['10']];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('segmentsProvider')]
    public function testCompactionRoundTrip(string $segments): void
    {
        $decoder = new GsOneDataBarExpandedDecoder();
        $digits = '12345678901234567890123456789012345678';
        $checked = 0;

        for ($len = 1; $len <= 38; ++$len) {
            foreach ([$digits, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-.'] as $source) {
                $data = \substr($source, 0, $len);

                try {
                    $type = new InternalGsOneDataBarExpandedStacked('(10)' . $data, -1, -1, 'black', ['', $segments]);
                    [$bits, $prefix, $payload] = $type->exposeBits();
                } catch (\Com\Tecnick\Barcode\Exception) {
                    // the data does not fit a symbol of this many segments
                    continue;
                }

                $this->assertSame(
                    $payload,
                    $decoder->decode(\substr($bits, $prefix)),
                    $len . ' characters over ' . $segments . ' segments',
                );
                ++$checked;
            }
        }

        $this->assertGreaterThan(0, $checked);
    }
}
