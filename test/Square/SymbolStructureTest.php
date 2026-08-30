<?php

/**
 * SymbolStructureTest.php
 *
 * @since       2026-08-27
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Test\Square;

use Com\Tecnick\Barcode\Barcode;
use Com\Tecnick\Barcode\Exception as BarcodeException;
use Com\Tecnick\Barcode\Type\Square\Datamatrix\Data as DatamatrixData;
use Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalAztec;
use Test\Fixture\InternalPdfFourOneSeven;
use Test\Fixture\InternalQrEstimate;
use Test\TestUtil;

/**
 * Structural checks on the generated square symbols.
 *
 * @since       2026-08-27
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class SymbolStructureTest extends TestUtil
{
    /**
     * A Compact Aztec mode message stores the data codeword count minus one in 6 bits.
     *
     * @var int
     */
    private const MAX_COMPACT_CODEWORDS = 64;

    protected function getTestObject(): Barcode
    {
        return new Barcode();
    }

    /**
     * A Compact symbol declaring more than 64 data codewords truncates the count in its
     * mode message. When the data does not fit, Full Range must be used instead.
     *
     * @return array<int, array{int, string}>
     */
    public static function aztecEccProvider(): array
    {
        $cases = [];
        for ($ecc = 1; $ecc <= 20; ++$ecc) {
            foreach ([40, 55, 62, 70, 76, 90] as $len) {
                $cases[] = [$ecc, \str_repeat('A', $len)];
            }
        }

        return $cases;
    }

    /**
     * @throws BarcodeException
     */
    #[DataProvider('aztecEccProvider')]
    public function testAztecCompactSymbolFitsTheModeMessage(int $ecc, string $code): void
    {
        $encode = new InternalAztec($code, $ecc, -1, 'B', 'A');
        $layout = $encode->exposeLayout();
        if (!$layout['compact']) {
            $this->assertGreaterThan(0, $layout['layers']);
            return;
        }

        $this->assertLessThanOrEqual(
            self::MAX_COMPACT_CODEWORDS,
            $layout['datacdw'],
            'compact symbol with ' . $layout['datacdw'] . ' data codewords',
        );
    }

    /**
     * A 62-byte payload at ECC 1 does not fit the 6-bit Compact data codeword count,
     * so it is encoded as a Full Range symbol.
     *
     * @throws BarcodeException
     */
    public function testAztecLowEccFallsBackToFullRange(): void
    {
        $encode = new InternalAztec(\str_repeat('A', 62), 1, -1, 'B', 'A');
        $layout = $encode->exposeLayout();

        $this->assertFalse($layout['compact']);
        $this->assertLessThanOrEqual(2048, $layout['datacdw']);
    }

    /**
     * The default error correction percentage produces Compact symbols.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testAztecDefaultEccIsUnchanged(): void
    {
        $encode = new InternalAztec('TEST DATA 123', 33, -1, 'A', 'A');
        $this->assertTrue($encode->exposeLayout()['compact']);
    }

    /**
     * ISO/IEC 15438 places the macro control block right after the data codewords, with the
     * pad codewords last.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testPdfFourOneSevenMacroBlockPrecedesThePadding(): void
    {
        $params = ['2', '8', '1', '0', '0', '0', '1', '2'];
        $pdf = new InternalPdfFourOneSeven(\str_repeat('X', 1750), -1, -1, 'black', $params);
        $codewords = $pdf->exposeCodewords();

        $start = \array_search(928, $codewords, true);
        $end = \array_search(922, $codewords, true);
        $this->assertIsInt($start);
        $this->assertIsInt($end);
        $this->assertGreaterThan($start, $end);

        // no pad codeword between the data and the macro control block
        $this->assertNotContains(900, \array_slice($codewords, 1, $start - 1));
        // the padding follows the terminator
        $this->assertSame(900, $codewords[$end + 1] ?? null);
    }

    /**
     * Alphanumeric mode packs a character pair into 11 bits and a trailing odd character
     * into 6.
     *
     * @return array<int, array{int, int}>
     */
    public static function alphanumericSizeProvider(): array
    {
        return [[0, 0], [1, 6], [2, 11], [3, 17], [4, 22], [5, 28], [10, 55], [11, 61], [45, 248]];
    }

    #[DataProvider('alphanumericSizeProvider')]
    public function testQrCodeAlphanumericBitEstimate(int $size, int $expected): void
    {
        $estimate = new InternalQrEstimate();
        $this->assertSame($expected, $estimate->exposeEstimateBitsModeAn($size));
    }

    /**
     * Every Data Matrix symbol size, as shape and attributes of Data::SYMBATTR.
     *
     * @return array<string, array{string, array<int, int>}>
     */
    public static function datamatrixSymbolProvider(): array
    {
        $cases = [];
        foreach (DatamatrixData::SYMBATTR as $shape => $rows) {
            foreach ($rows as $row) {
                $cases[$shape . ' ' . $row[0] . 'x' . $row[1]] = [$shape, $row];
            }
        }

        return $cases;
    }

    /**
     * The declared data and error codeword counts of a Data Matrix symbol match the number
     * of code words its mapping matrix holds.
     *
     * @param string           $shape Data Matrix shape key.
     * @param array<int, int>  $row   Symbol attributes.
     */
    #[DataProvider('datamatrixSymbolProvider')]
    public function testDatamatrixSymbolCapacityMatchesTheMappingMatrix(string $shape, array $row): void
    {
        $rows = $row[2] ?? 0;
        $cols = $row[3] ?? 0;
        $data = $row[11] ?? 0;
        $ecc = $row[12] ?? 0;
        $blocks = $row[13] ?? 0;

        $this->assertSame(\intdiv($rows * $cols, 8), $data + $ecc, 'declared code words');
        $this->assertSame($ecc, $blocks * ($row[15] ?? 0), 'error code words per block');

        // the last blocks hold one code word less when the data does not divide evenly
        $nominal = $blocks * ($row[14] ?? 0);
        $this->assertGreaterThanOrEqual($data, $nominal);
        $this->assertLessThan($blocks, $nominal - $data);

        $places = (new Encode($shape))->getPlacementMap($rows, $cols);
        $slots = 0;
        foreach ($places as $place) {
            if ($place < 2) {
                continue;
            }

            $slots = \max($slots, \intdiv($place, 10));
        }

        $this->assertSame($data + $ecc, $slots, 'code word slots of the placement map');
    }

    /**
     * The data code word count grows with the symbol size.
     */
    public function testDatamatrixSymbolSizesAreMonotonic(): void
    {
        foreach (DatamatrixData::SYMBATTR as $shape => $rows) {
            $prev = 0;
            foreach ($rows as $row) {
                $this->assertGreaterThan($prev, $row[11], $shape . ' symbol ' . $row[0]);
                $prev = $row[11];
            }
        }
    }

    /**
     * A PDF417 codeword row is $row_height modules tall: the pattern is repeated over
     * consecutive rows.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testPdfFourOneSevenRepeatsTheRowsVertically(): void
    {
        $grid = $this->getTestObject()->getBarcodeObj('PDF417', '0123456789')->getGridArray();
        $last = \count($grid) - 2;

        // two rows of vertical quiet zone at each end, then pairs of identical rows
        $this->assertSame(0, ($last - 2) % 2);
        for ($idx = 2; $idx < $last; $idx += 2) {
            $this->assertSame(
                \implode('', $grid[$idx] ?? []),
                \implode('', $grid[$idx + 1] ?? []),
                'row ' . $idx . ' is not repeated',
            );
        }
    }
}
