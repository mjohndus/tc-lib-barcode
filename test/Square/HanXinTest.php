<?php

/**
 * HanXinTest.php
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

use Com\Tecnick\Barcode\Type\Square\HanXin\Data;
use Com\Tecnick\Barcode\Type\Square\HanXin\Encode;
use Com\Tecnick\Barcode\Type\Square\HanXin\Geometry;
use Com\Tecnick\Barcode\Type\Square\HanXin\HanXinEccLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\HanXinDecoder;
use Test\Fixture\InternalHanXin;
use Test\TestUtil;

/**
 * Han Xin Code Barcode class test
 *
 * @since       2026-09-02
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class HanXinTest extends TestUtil
{
    /**
     * The version 1 symbol of Figure F.8 of GB/T 21049-2022, encoding
     * 1234567890 with the error correction level L1 and the mask 01.
     *
     * @var array<int, string>
     */
    private const SYMBOL_ONE = [
        '11111110001000001111111',
        '10000000110001100000001',
        '10111110001010101111101',
        '10100000111011100000101',
        '10101110010000101110101',
        '10101110110101001110101',
        '10101110001100001110101',
        '00000000011101100000000',
        '00010101001010011000000',
        '01001101111101010101110',
        '10101010101010100101101',
        '01010010111101010101010',
        '10101010101010101010100',
        '10000011010101000011110',
        '00000011001010010101000',
        '00000000110100000000000',
        '11111110011110001110101',
        '00000010010101101110101',
        '11111010101010001110101',
        '00001010110010100000101',
        '11101010100010001111101',
        '11101010110101100000001',
        '11101010001010001111111',
    ];
    /**
     * The version 10 symbol of Figure F.16 of GB/T 21049-2022, encoding
     * 1234567890ABCDEFGabcdefg,Han Xin Code with the error correction level
     * L3 and the mask 10.
     *
     * @var array<int, string>
     */
    private const SYMBOL_TWO = [
        '11111110001011000000100000010101101111111',
        '10000000001011110010000000011010100000001',
        '10111110111111111010111011101111101111101',
        '10100000101001001001001001001001100000101',
        '10101110000100100101010010010111001110101',
        '10101110010111110111111011101100101110101',
        '10101110101111001001011101110011001110101',
        '00000000011001100100100100100100100000000',
        '00011110111111111111111001101111110000000',
        '10110011011100110010001001100000001001001',
        '11001100100100100100100100100100100100100',
        '11111111001110101101011011110011011110110',
        '10100001001001000001100001010010001001001',
        '10100100100100100100100101100100001110011',
        '10011111100010110000001111111111101000001',
        '10110000001001001001001001001001001001001',
        '10101011011110000011001101111001100100100',
        '11111111000110010101111011111111111111111',
        '11001001001001000110011001110111100000010',
        '10111110100100100100100110001110011011000',
        '11111111111111111111111111111111111111101',
        '00000000000000000000101001001001001011000',
        '01110110101001001010101000010110100100100',
        '11111111101110010010100011111111111111111',
        '01001001001001001100110001101111011101011',
        '00101111100100100100100100001101001101110',
        '11111111111111111110111111111111111001111',
        '10010110100000101110111010011001001001001',
        '00101001101100100010100100100100100100100',
        '11111111111110110000111000110000110000101',
        '00000001001001001000101011011100000100101',
        '00100100100100100100100100100100101011111',
        '00000001100101110100111011100010101111000',
        '00000000110001001000101001001111000000000',
        '11111110001011100100100100100100101110101',
        '00000010111111111110111111111111001110101',
        '11111010001001000000111111111011001110101',
        '00001010100101111000101010100100100000101',
        '11101010111111111100101100001111101111101',
        '11101010101001001000101001001001000000001',
        '11101010100100100100111111111111001111111',
    ];

    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Get the barcode grid as an array of rows of binary digits
     *
     * @return array<int, string>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getRows(string $type, string $code): array
    {
        $grid = $this->getTestObject()->getBarcodeObj($type, $code)->getGridArray();

        return \array_map(static fn(array $row): string => \implode('', $row), $grid);
    }

    /**
     * The two worked construction examples of Annex F, module for module.
     *
     * These are the only conformance assertions of this file: HanXinDecoder
     * reads a symbol back through the same Geometry, the same Data tables and
     * the same mask formulas the encoder writes it with, so the round trip
     * proves self consistency, not conformance. The figures pin the geometry of
     * the versions 1 and 10 and the mask patterns 01 and 10; the mask patterns
     * 00 and 11 are pinned by regression only.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbols(): void
    {
        $this->assertSame(self::SYMBOL_ONE, $this->getRows('HANXIN,L1,1,1', '1234567890'));
        $this->assertSame(self::SYMBOL_TWO, $this->getRows('HANXIN,L3,10,2', '1234567890ABCDEFGabcdefg,Han Xin Code'));
    }

    /**
     * The mask the penalty rules of Table 15 select is the one of both worked
     * examples, so the reference symbols come out of the automatic selection.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbolsWithAutomaticMask(): void
    {
        $this->assertSame(self::SYMBOL_ONE, $this->getRows('HANXIN', '1234567890'));
        $this->assertSame(self::SYMBOL_TWO, $this->getRows('HANXIN,L3,10', '1234567890ABCDEFGabcdefg,Han Xin Code'));
    }

    /**
     * The function information of the reference symbols carries their version,
     * error correction level and mask.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceFunctionInformation(): void
    {
        $decoder = new HanXinDecoder(self::SYMBOL_ONE);
        $this->assertSame(1, $decoder->getVersion());
        $this->assertSame(1, $decoder->getLevel());
        $this->assertSame(1, $decoder->getMask());
        $this->assertSame('1234567890', $decoder->getMessage());

        $decoder = new HanXinDecoder(self::SYMBOL_TWO);
        $this->assertSame(10, $decoder->getVersion());
        $this->assertSame(3, $decoder->getLevel());
        $this->assertSame(2, $decoder->getMask());
        $this->assertSame('1234567890ABCDEFGabcdefg,Han Xin Code', $decoder->getMessage());
    }

    /**
     * @return array<int, array{string, string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['HANXIN',         '1234567890',                            '849692e8c20580f3df3c62fa0b85fafb'],
            ['HANXIN,L2',      'ABCDEFGHIJ',                            'e8a811e130471ff6cb45bfe28b6a0ca5'],
            ['HANXIN,L3,10,2', '1234567890ABCDEFGabcdefg,Han Xin Code', '0a63669cc1a7c056adf2a58c6cd607a5'],
            ['HANXIN,L4,5',    'Han Xin',                               '1eaa756dd6f7a3dff2bc8588f09cd730'],
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
        $this->assertSame($expected, \md5($barcode->getBarcodeObj($type, $code)->getGrid()));
    }

    /**
     * The version 1 symbol is 23 modules per side and each version adds two
     * modules, up to the 189 modules of the version 84.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolSize(): void
    {
        $barcode = $this->getTestObject();
        foreach ([1 => 23, 2 => 25, 10 => 41, 24 => 69, 84 => 189] as $version => $size) {
            $data = $barcode->getBarcodeObj('HANXIN,L1,' . $version, '1')->getArray();
            $this->assertSame($size, $data['ncols']);
            $this->assertSame($size, $data['nrows']);
        }
    }

    /**
     * The four position detection patterns, their separators and the function
     * information areas take the four corners of every version.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('versionProvider')]
    public function testPositionDetectionPatterns(int $version): void
    {
        $rows = $this->getRows('HANXIN,L1,' . $version . ',0', '1');
        $size = \count($rows);
        $end = $size - 1;
        $pattern = ['1111111', '1000000', '1011111', '1010000', '1010111', '1010111', '1010111'];
        for ($row = 0; $row < 7; ++$row) {
            $line = $pattern[$row] ?? '';
            $this->assertSame($line, \substr($rows[$row] ?? '', 0, 7));
            $this->assertSame(\strrev($line), \substr($rows[$row] ?? '', -7));
            $this->assertSame(\strrev($line), \substr($rows[$end - 6 + $row] ?? '', 0, 7));
            $this->assertSame(\strrev($line), \substr($rows[$end - $row] ?? '', -7));
        }

        $this->assertSame(\str_repeat('0', 8), \substr($rows[7] ?? '', 0, 8));
        $this->assertSame(\str_repeat('0', 8), \substr($rows[7] ?? '', -8));
        $this->assertSame(\str_repeat('0', 8), \substr($rows[$size - 8] ?? '', 0, 8));
        $this->assertSame(\str_repeat('0', 8), \substr($rows[$size - 8] ?? '', -8));
    }

    /**
     * @return array<int, array{int}>
     */
    public static function versionProvider(): array
    {
        $versions = [];
        for ($version = 1; $version <= Data::VERSION_MAX; ++$version) {
            $versions[] = [$version];
        }

        return $versions;
    }

    /**
     * The alignment pattern parameters of Table A.1 satisfy the relation
     * r + m * k = n of Formula (1).
     */
    #[DataProvider('versionProvider')]
    public function testAlignmentParameters(int $version): void
    {
        $size = Data::SIZE_BASE + (Data::SIZE_STEP * ($version - 1));
        $alignment = Data::ALIGNMENT[$version] ?? [];
        if ($alignment === []) {
            $this->assertLessThan(4, $version);
            return;
        }

        $this->assertSame($size, ($alignment[0] ?? 0) + (($alignment[2] ?? 0) * ($alignment[1] ?? 0)));
    }

    /**
     * The error correction block structures of Table B.1 add up to the number
     * of codewords of the symbol, which does not depend on the error
     * correction level, and the number of data codewords falls as the level
     * rises.
     */
    #[DataProvider('versionProvider')]
    public function testBlockStructure(int $version): void
    {
        $total = Encode::getTotalCodewords($version, 1);
        $previous = $total + 1;
        for ($level = 1; $level <= 4; ++$level) {
            $blocks = Data::BLOCKS[$version][$level] ?? [];
            $this->assertNotSame([], $blocks);
            $data = 0;
            $count = 0;
            foreach ($blocks as $block) {
                $this->assertGreaterThan(0, $block[0]);
                $this->assertGreaterThan($block[2], $block[1]);
                $data += $block[0] * $block[2];
                $count += $block[0];
            }

            $this->assertSame($total, Encode::getTotalCodewords($version, $level));
            $this->assertSame($data, Encode::getDataCodewords($version, $level));
            $this->assertLessThan($previous, $data);
            $this->assertLessThanOrEqual(255, $count === 1 ? 1 : $blocks[0][1] ?? 0);
            $previous = $data;
        }
    }

    /**
     * The encoding region of every version holds the codewords of Table B.1
     * and at most seven padding modules.
     */
    #[DataProvider('versionProvider')]
    public function testEncodingRegionSize(int $version): void
    {
        $geometry = new Geometry($version);
        $cells = \count($geometry->getEncodingCells());
        $bits = Encode::getTotalCodewords($version, 1) * 8;
        $this->assertGreaterThanOrEqual($bits, $cells);
        $this->assertLessThanOrEqual(7, $cells - $bits);
        foreach ($geometry->getInfoCells() as $area) {
            $this->assertCount(Data::INFO_AREA_MODULES, $area);
        }
    }

    /**
     * @return array<int, array{string, string, int}>
     */
    public static function capacityProvider(): array
    {
        // the encoding capacities of 4.1.1.5
        return [
            ['1',                '1',                  7827],
            ['A',                'A',                  4350],
            ["\xC8\xAB",         'hanzi region one',   2174],
            ["\xF3\xA3",         'hanzi region two',   2174],
            ["\x9D\x51",         'GB 18030 two byte',  1739],
            ["\x81\x39\xEF\x30", 'GB 18030 four byte', 1044],
            ["\x1C",             'binary byte',        3261],
        ];
    }

    /**
     * The encoding capacities of 4.1.1.5, which the version 84 with the error
     * correction level L1 carries.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('capacityProvider')]
    public function testDataCapacity(string $char, string $label, int $max): void
    {
        $barcode = $this->getTestObject();
        $grid = $barcode->getBarcodeObj('HANXIN,L1,84', \str_repeat($char, \max(0, $max)))->getGridArray();
        $this->assertCount(189, $grid, $label);
        $this->bcAssertOverflow('HANXIN,L1,84', \str_repeat($char, \max(0, $max + 1)));
    }

    /**
     * Assert that the code does not fit in the requested symbol.
     *
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function bcAssertOverflow(string $type, string $code): void
    {
        $barcode = $this->getTestObject();

        try {
            $barcode->getBarcodeObj($type, $code)->getGrid();
        } catch (\Com\Tecnick\Barcode\Exception) {
            $this->assertTrue(true);
            return;
        }

        $this->fail('The code should not fit in ' . $type);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function roundTripProvider(): array
    {
        return [
            ['HANXIN', '1'],
            ['HANXIN', '1234567890'],
            ['HANXIN', 'A'],
            ['HANXIN', 'Han Xin Code'],
            ['HANXIN', '1234567890ABCDEFGabcdefg,Han Xin Code'],
            ['HANXIN', 'The quick brown fox jumps over the lazy dog 0123456789'],
            ['HANXIN', 'A1B2C3D4E5F6G7H8I9J0'],
            ['HANXIN', '0123456789012345678901234567890123456789'],
            ['HANXIN', "\x00\x01\x02\x14\x1C\x5B\x7F"],
            ['HANXIN', "\xC8"],
            ['HANXIN', "\xC8\x20"],
            ['HANXIN', "\xC8\xAB\x81"],
            ['HANXIN', \str_repeat("\xC8\xAB", 10) . "\xF3\xA3" . \str_repeat("\xC8\xAB", 10)],
            ['HANXIN', "\xC8\xAB\xA3\xBB\xA8\xBE"],
            ['HANXIN', "\xF3\xA3"],
            ['HANXIN', "\x9D\x51\x9E\xAF"],
            ['HANXIN', "\x81\x39\xEF\x30"],
            ['HANXIN', "\xC8\xAB\xF3\xA3\xC8\xAB"],
            ['HANXIN', "\xC8\xAB\x9D\x51\xC8\xAB\xC8\xAB"],
            ['HANXIN,L2', 'Test data 12345'],
            ['HANXIN,L3', 'Test data 12345'],
            ['HANXIN,L4', 'Test data 12345'],
            ['HANXIN,L1,0,0', 'Test data 12345'],
            ['HANXIN,L1,0,1', 'Test data 12345'],
            ['HANXIN,L1,0,2', 'Test data 12345'],
            ['HANXIN,L1,0,3', 'Test data 12345'],
            ['HANXIN,L1,20', 'Test data 12345'],
            ['HANXIN,L4,84', 'Test data 12345'],
            ['HANXIN', "123\n"],
            ['HANXIN', \str_repeat('9', 500)],
            ['HANXIN', \str_repeat('Za', 300)],
        ];
    }

    /**
     * Every symbol is read back into the message it encodes, and its error
     * correction codewords are the ones of its data codewords.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('roundTripProvider')]
    public function testRoundTrip(string $type, string $code): void
    {
        $decoder = new HanXinDecoder($this->getRows($type, $code));
        $this->assertSame($code, $decoder->getMessage());
        $this->assertTrue($decoder->hasValidCheckwords());
    }

    /**
     * Every version and error correction level builds a symbol that reads back.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('versionProvider')]
    public function testEveryVersion(int $version): void
    {
        for ($level = 1; $level <= 4; ++$level) {
            $type = 'HANXIN,L' . $level . ',' . $version . ',' . ($version % 4);
            $decoder = new HanXinDecoder($this->getRows($type, 'A1'));
            $this->assertSame($version, $decoder->getVersion());
            $this->assertSame($level, $decoder->getLevel());
            $this->assertSame($version % 4, $decoder->getMask());
            $this->assertSame('A1', $decoder->getMessage());
            $this->assertTrue($decoder->hasValidCheckwords());
        }
    }

    /**
     * The Text mode carries the byte values 00 to 1B and 20 to 7F of 5.3.3.
     * The Text1 sub mode of Table 3 carries the digits and the letters, and
     * the codes of the Text2 sub mode of Table 4 fill the values 0 to 61, the
     * two remaining six bit values being the sub mode switch and the mode
     * terminator.
     */
    public function testTextCharacterSet(): void
    {
        $internal = new InternalHanXin();
        $one = [];
        $two = [];
        for ($byte = 0; $byte <= 0xFF; ++$byte) {
            $code = $internal->exposeTextCode($byte);
            $inRange = $byte <= 0x1B || $byte >= 0x20 && $byte <= 0x7F;
            $this->assertSame($inRange, $code >= 0, 'byte ' . $byte);
            if ($code < 0) {
                continue;
            }

            if ($internal->exposeTextOne($byte)) {
                $one[$code] = $byte;
                continue;
            }

            $two[$code] = $byte;
        }

        $this->assertCount(62, $one);
        $this->assertCount(62, $two);
        $this->assertSame(\range(0, 61), \array_keys($one));
        $this->assertSame(\range(0, 61), \array_keys($two));
        $this->assertSame(\bindec(Data::TEXT_SWITCH), 62);
        $this->assertSame(\bindec(Data::TEXT_TERMINATOR), 63);
    }

    /**
     * @return array<int, array{string, array<int, array<int, int|string>>}>
     */
    public static function segmentProvider(): array
    {
        // the modes of the encoder are the classes of Compaction
        return [
            ['1234567890', [[0, '1234567890']]],
            [
                '1234567890ABCDEFGabcdefg,Han Xin Code',
                [
                    [0, '1234567890'],
                    [1, 'ABCDEFGabcdefg,Han Xin Code'],
                ],
            ],
            ['A1', [[1, 'A1']]],
            ["\x1C", [[6, "\x1C"]]],
            ["\xC8\xAB", [[2, "\xC8\xAB"]]],
            ["\x81\x39\xEF\x30", [[5, "\x81\x39\xEF\x30"]]],
            ["\x9D\x51\x9E\xAF\x9D\x52", [[4, "\x9D\x51\x9E\xAF\x9D\x52"]]],
        ];
    }

    /**
     * The data analysis of 5.2 splits the code into the segments of Table 12.
     *
     * @param array<int, array<int, int|string>> $expected Mode and bytes of each segment.
     */
    #[DataProvider('segmentProvider')]
    public function testSegments(string $code, array $expected): void
    {
        $internal = new InternalHanXin();
        $this->assertSame($expected, $internal->exposeSegments($code));
    }

    /**
     * The information bit stream of the two worked examples of Annex F.
     */
    public function testInformationBitStream(): void
    {
        $internal = new InternalHanXin();
        $this->assertSame(
            '000100011110110111001000110001010100000000001111111101',
            $internal->exposeBitStream('1234567890'),
        );
        $this->assertSame(262, \strlen($internal->exposeBitStream('1234567890ABCDEFGabcdefg,Han Xin Code')));
    }

    /**
     * The encoder reports the version, the error correction level and the mask
     * of the symbol it builds.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function testEncoderProperties(): void
    {
        $encode = new Encode('1234567890', 1, 0, -1);
        $this->assertSame(1, $encode->getVersion());
        $this->assertSame(1, $encode->getLevel());
        $this->assertSame(1, $encode->getMask());
        $this->assertSame(self::SYMBOL_ONE, $encode->getGrid());

        $encode = new Encode('Han Xin', 4, 5, 3);
        $this->assertSame(5, $encode->getVersion());
        $this->assertSame(4, $encode->getLevel());
        $this->assertSame(3, $encode->getMask());
    }

    /**
     * The error correction level accepts its name, its number and its own
     * enum case, and falls back to L1.
     */
    public function testEccLevel(): void
    {
        $this->assertSame(HanXinEccLevel::L3, HanXinEccLevel::fromLoose('L3'));
        $this->assertSame(HanXinEccLevel::L3, HanXinEccLevel::fromLoose('3'));
        $this->assertSame(HanXinEccLevel::L3, HanXinEccLevel::fromLoose(HanXinEccLevel::L3));
        $this->assertSame(HanXinEccLevel::L1, HanXinEccLevel::fromLoose('L9'));
        $this->assertSame(4, HanXinEccLevel::L4->getLevel());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            ['HANXIN', ''],
            ['HANXIN,L1,1', \str_repeat('1', 46)],
            ['HANXIN,L4,1', \str_repeat('1', 16)],
            ['HANXIN', \str_repeat('1', 7828)],
            ['HANXIN,L4', \str_repeat('1', 5788)],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidCodeProvider')]
    public function testInvalidInput(string $type, string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj($type, $code)->getGrid();
    }

    /**
     * Out of range parameters fall back to the automatic selection.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testParameterFallback(): void
    {
        $expected = $this->getRows('HANXIN', '1234567890');
        $this->assertSame($expected, $this->getRows('HANXIN,L9,0,9', '1234567890'));
        $this->assertSame($expected, $this->getRows('HANXIN,,85,-1', '1234567890'));
        $this->assertSame($expected, $this->getRows('HANXIN,1,0,', '1234567890'));
    }
}
