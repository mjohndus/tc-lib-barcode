<?php

/**
 * QrCodeSpecTest.php
 *
 * @since       2026-09-02
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

use Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use Com\Tecnick\Barcode\Type\Square\QrCode\Encode;
use Com\Tecnick\Barcode\Type\Square\QrCode\Geometry;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalQrEncode;
use Test\TestUtil;

/**
 * QR Code specification tables test.
 *
 * Every table of Data is checked against the table of ISO/IEC 18004 it is
 * transcribed from, or against the invariants the standard states for it. The
 * tables the encoder computes rather than tabulates are checked against the
 * published values of the same tables.
 *
 * @since       2026-09-02
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class QrCodeSpecTest extends TestUtil
{
    /**
     * Table 1 of ISO/IEC 18004: the number of modules of the encoding region,
     * the codeword capacity and the remainder bits of every version. The encoder
     * derives all three from the geometry of the symbol.
     *
     * @return array<string, array{int, int, int, int}>
     */
    public static function codewordCapacityProvider(): array
    {
        $table = [
            [208, 26, 0],
            [359, 44, 7],
            [567, 70, 7],
            [807, 100, 7],
            [1079, 134, 7],
            [1383, 172, 7],
            [1568, 196, 0],
            [1936, 242, 0],
            [2336, 292, 0],
            [2768, 346, 0],
            [3232, 404, 0],
            [3728, 466, 0],
            [4256, 532, 0],
            [4651, 581, 3],
            [5243, 655, 3],
            [5867, 733, 3],
            [6523, 815, 3],
            [7211, 901, 3],
            [7931, 991, 3],
            [8683, 1085, 3],
            [9252, 1156, 4],
            [10068, 1258, 4],
            [10916, 1364, 4],
            [11796, 1474, 4],
            [12708, 1588, 4],
            [13652, 1706, 4],
            [14628, 1828, 4],
            [15371, 1921, 3],
            [16411, 2051, 3],
            [17483, 2185, 3],
            [18587, 2323, 3],
            [19723, 2465, 3],
            [20891, 2611, 3],
            [22091, 2761, 3],
            [23008, 2876, 0],
            [24272, 3034, 0],
            [25568, 3196, 0],
            [26896, 3362, 0],
            [28256, 3532, 0],
            [29648, 3706, 0],
        ];
        $cases = [];
        foreach ($table as $index => $row) {
            $cases['version ' . ($index + 1)] = [$index + 1, $row[0], $row[1], $row[2]];
        }

        return $cases;
    }

    #[DataProvider('codewordCapacityProvider')]
    public function testCodewordCapacity(int $version, int $modules, int $codewords, int $remainder): void
    {
        $this->assertSame($modules, Encode::getEncodingModules($version));
        $this->assertSame($codewords, Encode::getTotalCodewords($version));
        $this->assertSame($remainder, Encode::getRemainderBits($version));
    }

    /**
     * The block structure Table 9 of ISO/IEC 18004 prints as the (c, k, r) code
     * of each block, against the one the encoder derives from the number of
     * error correction codewords per block and the number of blocks.
     *
     * @return array<string, array{int, int, array<int, array{int, int, int}>}>
     */
    public static function blockStructureProvider(): array
    {
        $table = [
            [1, 0, [[1, 26, 19]]],
            [1, 3, [[1, 26, 9]]],
            [3, 2, [[2, 35, 17]]],
            [4, 3, [[4, 25, 9]]],
            [5, 2, [[2, 33, 15], [2, 34, 16]]],
            [5, 3, [[2, 33, 11], [2, 34, 12]]],
            [7, 2, [[2, 32, 14], [4, 33, 15]]],
            [7, 3, [[4, 39, 13], [1, 40, 14]]],
            [8, 1, [[2, 60, 38], [2, 61, 39]]],
            [10, 0, [[2, 86, 68], [2, 87, 69]]],
            [11, 3, [[3, 36, 12], [8, 37, 13]]],
            [12, 2, [[4, 46, 20], [6, 47, 21]]],
            [13, 3, [[12, 33, 11], [4, 34, 12]]],
            [17, 0, [[1, 135, 107], [5, 136, 108]]],
            [21, 1, [[17, 68, 42]]],
            [22, 3, [[34, 37, 13]]],
            [25, 1, [[8, 75, 47], [13, 76, 48]]],
            [32, 0, [[17, 145, 115]]],
            [34, 3, [[59, 46, 16], [1, 47, 17]]],
            [40, 0, [[19, 148, 118], [6, 149, 119]]],
            [40, 3, [[20, 45, 15], [61, 46, 16]]],
        ];
        $cases = [];
        foreach ($table as $row) {
            $cases[$row[0] . '-' . (\array_keys(Data::ECC_LEVELS)[$row[1]] ?? '')] = $row;
        }

        return $cases;
    }

    /**
     * @param array<int, array{int, int, int}> $expected
     */
    #[DataProvider('blockStructureProvider')]
    public function testBlockStructure(int $version, int $level, array $expected): void
    {
        $ecc = Data::ECC_BLOCKS[$version][$level][0] ?? 0;
        $blocks = [];
        foreach (InternalQrEncode::forSymbol($version, $level)->exposeBlockSizes() as $size) {
            $key = ($size + $ecc) . ',' . $size;
            $blocks[$key] = ($blocks[$key] ?? 0) + 1;
        }

        $want = [];
        foreach ($expected as $row) {
            $want[$row[1] . ',' . $row[2]] = $row[0];
        }

        $this->assertSame($want, $blocks);
    }

    /**
     * Every row of Table 9 accounts for the whole codeword capacity of its
     * version, and the number of error correction codewords is the count of
     * blocks times the count per block.
     *
     * @return array<string, array{int, int}>
     */
    public static function everySymbolProvider(): array
    {
        $cases = [];
        for ($version = Data::VERSION_MIN; $version <= Data::VERSION_MAX; ++$version) {
            foreach (Data::ECC_LEVELS as $name => $level) {
                $cases[$version . '-' . $name] = [$version, $level];
            }
        }

        return $cases;
    }

    #[DataProvider('everySymbolProvider')]
    public function testBlocksFillTheSymbol(int $version, int $level): void
    {
        [$ecc, $count] = Data::ECC_BLOCKS[$version][$level] ?? [0, 1];
        $sizes = InternalQrEncode::forSymbol($version, $level)->exposeBlockSizes();

        $this->assertCount($count, $sizes);
        $this->assertSame(Encode::getDataCodewords($version, $level), \array_sum($sizes));
        $this->assertSame(Encode::getTotalCodewords($version), \array_sum($sizes) + ($ecc * $count));
        // the data codewords are shared out as evenly as possible, so the blocks
        // take at most two lengths and they differ by one
        $this->assertLessThanOrEqual(2, \count(\array_unique($sizes)));
        $this->assertSame(1, (\max($sizes) - \min($sizes)) === 0 ? 1 : \max($sizes) - \min($sizes));
        // the shortest blocks come first
        $sorted = $sizes;
        \sort($sorted);
        $this->assertSame($sorted, $sizes);
    }

    /**
     * Table E.1 of ISO/IEC 18004 states the coordinates of the centre module of
     * the alignment patterns. The standard states four properties of them: the
     * first coordinate is always 6, the last is always seven modules from the
     * edge, the count grows by one every seven versions, and the patterns are
     * spaced evenly between the timing pattern and the far side.
     *
     * @return array<string, array{int}>
     */
    public static function versionProvider(): array
    {
        $cases = [];
        for ($version = Data::VERSION_MIN; $version <= Data::VERSION_MAX; ++$version) {
            $cases['version ' . $version] = [$version];
        }

        return $cases;
    }

    #[DataProvider('versionProvider')]
    public function testAlignmentCentres(int $version): void
    {
        $centres = Data::ALIGN_CENTRES[$version] ?? [];
        $size = (4 * $version) + 17;
        if ($version === 1) {
            $this->assertSame([], $centres);

            return;
        }

        $this->assertCount(\intdiv($version, 7) + 2, $centres);
        $this->assertSame(6, $centres[0] ?? 0);
        $this->assertSame($size - 7, $centres[\count($centres) - 1] ?? 0);
        $steps = [];
        foreach ($centres as $index => $centre) {
            $this->assertSame(0, $centre % 2, 'the coordinates are even');
            if ($index > 1) {
                $steps[] = $centre - ($centres[$index - 1] ?? 0);
            }
        }

        $this->assertLessThanOrEqual(1, \count(\array_unique($steps)), 'the spacing is even');
    }

    /**
     * The number of alignment patterns placed is the square of the number of
     * coordinates less the three the finder patterns cover, which is the count
     * Table E.1 states in its second column.
     */
    #[DataProvider('versionProvider')]
    public function testAlignmentPatternCount(int $version): void
    {
        $centres = \count(Data::ALIGN_CENTRES[$version] ?? []);
        $expected = $centres === 0 ? 0 : ($centres * $centres) - 3;

        $this->assertCount($expected, (new Geometry($version))->getAlignmentCentres());
    }

    /**
     * Table C.1 of ISO/IEC 18004: the fifteen bit format information of each
     * error correction level and data mask pattern, which the encoder computes
     * with the BCH (15, 5) code of Annex C.
     *
     * @return array<string, array{int, int, int}>
     */
    public static function formatInfoProvider(): array
    {
        // the first eight rows of Table C.1, that is the data bits 00000 to
        // 00111, which are the level M over the eight masks
        $table = [0x5412, 0x5125, 0x5E7C, 0x5B4B, 0x45F9, 0x40CE, 0x4F97, 0x4AA0];
        $cases = [];
        foreach ($table as $mask => $expected) {
            $cases['M mask ' . $mask] = [Data::ECC_LEVELS['M'] ?? 1, $mask, $expected];
        }

        return $cases;
    }

    #[DataProvider('formatInfoProvider')]
    public function testFormatInformation(int $level, int $mask, int $expected): void
    {
        $this->assertSame($expected, InternalQrEncode::forSymbol()->exposeFormatInfo($level, $mask));
    }

    /**
     * The thirty two format information values differ from one another in at
     * least three bits, as section C.2 of ISO/IEC 18004 states.
     */
    public function testFormatInformationDistance(): void
    {
        $encode = InternalQrEncode::forSymbol();
        $values = [];
        foreach (\array_values(Data::ECC_LEVELS) as $level) {
            for ($mask = 0; $mask < Encode::MASKS; ++$mask) {
                $values[] = $encode->exposeFormatInfo($level, $mask);
            }
        }

        $this->assertCount(32, \array_unique($values));
        foreach ($values as $index => $first) {
            foreach (\array_slice($values, $index + 1) as $second) {
                $this->assertGreaterThanOrEqual(3, \substr_count(\decbin($first ^ $second), '1'));
            }
        }
    }

    /**
     * Table D.1 of ISO/IEC 18004: the eighteen bit version information of the
     * versions 7 to 40, which the encoder computes with the BCH (18, 6) code of
     * Annex D.
     *
     * @return array<string, array{int, int}>
     */
    public static function versionInfoProvider(): array
    {
        $table = [
            7 => 0x07C94,
            8 => 0x085BC,
            9 => 0x09A99,
            10 => 0x0A4D3,
            11 => 0x0BBF6,
            12 => 0x0C762,
            13 => 0x0D847,
            14 => 0x0E60D,
            15 => 0x0F928,
            16 => 0x10B78,
            17 => 0x1145D,
            18 => 0x12A17,
            19 => 0x13532,
            20 => 0x149A6,
            21 => 0x15683,
            22 => 0x168C9,
            23 => 0x177EC,
            24 => 0x18EC4,
            25 => 0x191E1,
            26 => 0x1AFAB,
            27 => 0x1B08E,
            28 => 0x1CC1A,
            29 => 0x1D33F,
            30 => 0x1ED75,
            31 => 0x1F250,
            32 => 0x209D5,
            33 => 0x216F0,
            34 => 0x228BA,
            35 => 0x2379F,
            36 => 0x24B0B,
            37 => 0x2542E,
            38 => 0x26A64,
            39 => 0x27541,
            40 => 0x28C69,
        ];
        $cases = [];
        foreach ($table as $version => $expected) {
            $cases['version ' . $version] = [$version, $expected];
        }

        return $cases;
    }

    #[DataProvider('versionInfoProvider')]
    public function testVersionInformation(int $version, int $expected): void
    {
        $this->assertSame($expected, InternalQrEncode::forSymbol()->exposeBchCode(
            $version,
            Data::VERSION_GENERATOR,
            6,
            12,
        ));
    }

    /**
     * Table 3 of ISO/IEC 18004 groups the versions 1 to 9, 10 to 26 and 27 to 40.
     *
     * @return array<string, array{int, int}>
     */
    public static function versionGroupProvider(): array
    {
        return [
            'version 1' => [1, 0],
            'version 9' => [9, 0],
            'version 10' => [10, 1],
            'version 26' => [26, 1],
            'version 27' => [27, 2],
            'version 40' => [40, 2],
        ];
    }

    #[DataProvider('versionGroupProvider')]
    public function testVersionGroup(int $version, int $expected): void
    {
        $this->assertSame($expected, InternalQrEncode::forSymbol()->exposeVersionGroup($version));
    }

    /**
     * The number of bits of a run of characters of each mode, sections 7.4.3 to
     * 7.4.6 of ISO/IEC 18004.
     *
     * @return array<string, array{int, int, int}>
     */
    public static function dataBitsProvider(): array
    {
        return [
            'numeric 0' => [Data::MODE_NUMERIC, 0, 0],
            'numeric 1' => [Data::MODE_NUMERIC, 1, 4],
            'numeric 2' => [Data::MODE_NUMERIC, 2, 7],
            'numeric 3' => [Data::MODE_NUMERIC, 3, 10],
            'numeric 4' => [Data::MODE_NUMERIC, 4, 14],
            'numeric 8' => [Data::MODE_NUMERIC, 8, 27],
            'alphanumeric 0' => [Data::MODE_ALPHANUM, 0, 0],
            'alphanumeric 1' => [Data::MODE_ALPHANUM, 1, 6],
            'alphanumeric 2' => [Data::MODE_ALPHANUM, 2, 11],
            'alphanumeric 3' => [Data::MODE_ALPHANUM, 3, 17],
            'alphanumeric 45' => [Data::MODE_ALPHANUM, 45, 248],
            'byte 5' => [Data::MODE_BYTE, 5, 40],
            'kanji 3' => [Data::MODE_KANJI, 3, 39],
        ];
    }

    #[DataProvider('dataBitsProvider')]
    public function testDataBits(int $mode, int $count, int $expected): void
    {
        $this->assertSame($expected, InternalQrEncode::forSymbol()->exposeDataBits($mode, $count));
    }

    /**
     * The alphanumeric character set of Table 5 of ISO/IEC 18004 holds
     * forty five characters and the value of each is its position in the set.
     */
    public function testAlphanumericCharacterSet(): void
    {
        $encode = InternalQrEncode::forSymbol();

        $this->assertSame(45, \strlen(Data::AN_CHARS));
        $this->assertCount(45, \array_unique(\str_split(Data::AN_CHARS)));
        $this->assertSame(0, $encode->exposeAlphanumericValue(\ord('0')));
        $this->assertSame(9, $encode->exposeAlphanumericValue(\ord('9')));
        $this->assertSame(10, $encode->exposeAlphanumericValue(\ord('A')));
        $this->assertSame(35, $encode->exposeAlphanumericValue(\ord('Z')));
        $this->assertSame(36, $encode->exposeAlphanumericValue(\ord(' ')));
        $this->assertSame(44, $encode->exposeAlphanumericValue(\ord(':')));
        $this->assertSame(-1, $encode->exposeAlphanumericValue(\ord('a')));
        $this->assertSame(-1, $encode->exposeAlphanumericValue(0x80));
    }

    /**
     * The worked example of section 7.4.6 of ISO/IEC 18004: the Shift JIS
     * characters 935F and E4AA are encoded as 0D9F and 1AAA.
     *
     * @return array<string, array{string, int}>
     */
    public static function kanjiProvider(): array
    {
        return [
            'the example 935F' => ["\x93\x5F", 0x0D9F],
            'the example E4AA' => ["\xE4\xAA", 0x1AAA],
            'the first character of the first range' => ["\x81\x40", 0x0000],
            'below the range' => ["\x81\x3F", -1],
            'between the ranges' => ["\xA0\x00", -1],
            'above the range' => ["\xEB\xC0", -1],
            'one byte' => ["\x93", -1],
        ];
    }

    #[DataProvider('kanjiProvider')]
    public function testKanjiValue(string $code, int $expected): void
    {
        $this->assertSame($expected, InternalQrEncode::forSymbol()->exposeKanjiValueAt($code, 0));
    }

    /**
     * The eight data mask conditions of Table 10 of ISO/IEC 18004, checked on
     * the module in the row 3 and the column 4.
     *
     * @return array<string, array{int, bool}>
     */
    public static function maskProvider(): array
    {
        return [
            'mask 000' => [0, false],
            'mask 001' => [1, false],
            'mask 010' => [2, false],
            'mask 011' => [3, false],
            'mask 100' => [4, true],
            'mask 101' => [5, true],
            'mask 110' => [6, true],
            'mask 111' => [7, false],
        ];
    }

    #[DataProvider('maskProvider')]
    public function testMaskCondition(int $pattern, bool $expected): void
    {
        $this->assertSame($expected, InternalQrEncode::forSymbol()->exposeIsMasked($pattern, 3, 4));
    }

    /**
     * The penalty points of Table 11 of ISO/IEC 18004 on the cases the notes to
     * the table work through: a run of seven modules scores five points, a three
     * by three block scores twelve, and a symbol whose modules are all dark
     * scores the highest departure from one half.
     */
    public function testPenaltyPoints(): void
    {
        $encode = InternalQrEncode::forSymbol();

        // note 1 to Table 11: a run of seven modules of the same colour scores
        // five points, not the sum of the scores of the runs of five, six and
        // seven it contains
        [$run] = $encode->exposePenalties([
            '1111111',
            '0101010',
            '1010101',
            '0101010',
            '1010101',
            '0101010',
            '1010101',
        ]);
        $this->assertSame(Data::N1 + 2, $run);

        // note 2 to Table 11: a three by three block of dark modules holds four
        // two by two blocks and scores twelve points
        [, $block] = $encode->exposePenalties(['111', '111', '111']);
        $this->assertSame(4 * Data::N2, $block);

        // the 1:1:3:1:1 pattern followed by four light modules
        [, , $finder] = $encode->exposePenalties(['10111010000', '00000000000', '00000000000']);
        $this->assertSame(Data::N3, $finder);

        // every module dark is fifty percent away from the balance, that is ten
        // steps of five percent
        [, , , $balance] = $encode->exposePenalties(['111', '111', '111']);
        $this->assertSame(10 * Data::N4, $balance);

        // an evenly balanced symbol scores nothing on the fourth rule
        [, , , $even] = $encode->exposePenalties(['10', '01']);
        $this->assertSame(0, $even);
    }
}
