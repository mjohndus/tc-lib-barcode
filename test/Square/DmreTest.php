<?php

/**
 * DmreTest.php
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

namespace Test\Square;

use Com\Tecnick\Barcode\Type\Square\Datamatrix\Data;
use Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode;
use Com\Tecnick\Barcode\Type\Square\Datamatrix\ErrorCorrection;
use Com\Tecnick\Barcode\Type\Square\Dmre\DmreSize;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\DatamatrixDecoder;
use Test\Fixture\DmreReferenceSymbols;
use Test\Fixture\InternalDmre;
use Test\TestUtil;

/**
 * Data Matrix Rectangular Extension Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class DmreTest extends TestUtil
{
    /**
     * The eighteen symbols of Table 1 of ISO/IEC 21471: rows, columns, data
     * region rows, data region columns, number of data regions, data codewords
     * and error correction codewords.
     *
     * @var array<int, array{int, int, int, int, int, int, int}>
     */
    private const TABLE_ONE = [
        [8,  48,  6,  22, 2, 18,  15],
        [8,  64,  6,  14, 4, 24,  18],
        [8,  80,  6,  18, 4, 32,  22],
        [8,  96,  6,  22, 4, 38,  28],
        [12, 64,  10, 14, 4, 43,  27],
        [20, 36,  18, 16, 2, 44,  28],
        [8,  120, 6,  18, 6, 49,  32],
        [20, 44,  18, 20, 2, 56,  34],
        [16, 64,  14, 14, 4, 62,  36],
        [8,  144, 6,  22, 6, 63,  36],
        [12, 88,  10, 20, 4, 64,  36],
        [26, 40,  24, 18, 2, 70,  38],
        [22, 48,  20, 22, 2, 72,  38],
        [24, 48,  22, 22, 2, 80,  41],
        [20, 64,  18, 14, 4, 84,  42],
        [26, 48,  24, 22, 2, 90,  42],
        [24, 64,  22, 14, 4, 108, 46],
        [26, 64,  24, 14, 4, 118, 50],
    ];

    /**
     * Rows of the reference symbol.
     */
    private const REFERENCE_ROWS = 8;

    /**
     * Columns of the reference symbol.
     */
    private const REFERENCE_COLS = 64;

    /**
     * Columns of a data region of the reference symbol, finder pattern included.
     */
    private const REFERENCE_REGION_COLS = 16;

    /**
     * Data regions of the reference symbol.
     */
    private const REFERENCE_REGIONS = 4;

    /**
     * Rows of the mapping matrix of the reference symbol.
     */
    private const REFERENCE_MAP_ROWS = 6;

    /**
     * Columns of the mapping matrix of the reference symbol.
     */
    private const REFERENCE_MAP_COLS = 56;

    /**
     * Data codewords of the reference symbol.
     */
    private const REFERENCE_DATA_CODEWORDS = 24;

    /**
     * Error correction codewords of the reference symbol.
     */
    private const REFERENCE_ERROR_CODEWORDS = 18;

    /**
     * The 8x64 symbol of Figure 1 of ISO/IEC 21471:2025, which encodes
     * "A1B2C3D4E5F6G7H8I9J0K1L2".
     *
     * @var array<int, string>
     */
    private const REFERENCE = [
        '1010101010101010101010101010101010101010101010101010101010101010',
        '1010001010111001101010111011111110001110000000011101010100010101',
        '1011000000000110110111111110111010110000101001101101001000010110',
        '1000001000110011110001111011111110001100101010111011111111001111',
        '1001000111011100101010110001011010010111001000101111101011101100',
        '1010101011111011100000000001000110101110011111011000101011000111',
        '1101101100110110111100110000001011100011001001101011001001001000',
        '1111111111111111111111111111111111111111111111111111111111111111',
    ];

    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DMRE', '');
    }

    /**
     * The largest DMRE symbol holds 118 data codewords.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCapacityException(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DMRE,N,BASE256', \str_pad('', 117, 'X'));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getGridDataProvider')]
    public function testGetGrid(string $mode, string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj($mode, $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * @return array<array{string, string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            [
                'DMRE',
                'A1B2C3D4E5F6G7H8I9J0K1L2',
                'afcdb39221789e9fec9adb6efc19c69c',
            ],
            [
                'DMRE',
                'DMRE',
                'a7e4abcffc134efbbe048dbb2969e523',
            ],
            [
                'DMRE',
                '0123456789',
                '3dceb6c505c7ad2feda96c87dcaefe73',
            ],
            [
                'DMRE',
                'Hello World',
                '0d622f7ed81f551bbeb786a1da06acb6',
            ],
            [
                'DMRE',
                'https://github.com/tecnickcom/tc-lib-barcode',
                '7c859a461fd33783804e205a50aa294e',
            ],
            [
                'DMRE',
                'abcdefghijklmnopqrstuvwxyz',
                '379c96af240c8e1e0d36a018fb3f545e',
            ],
            [
                'DMRE',
                "\x80\x8A\x94\x9E",
                'a5cb83bf1ac838ed266a0350fb9bf512',
            ],
            [
                'DMRE,N,ASCII',
                '01234567890',
                '7d5e354f20a9ae513b46e0ccfc329826',
            ],
            [
                'DMRE,N,C40',
                '01234567890',
                'f5729ffa409d3b6269c880b008c00ce9',
            ],
            [
                'DMRE,N,TXT',
                '01234567890',
                '8be9c559cd3579f646526135765a710f',
            ],
            [
                'DMRE,N,X12',
                '01234567890',
                '9815136af2e46b9f2751989beba5cde2',
            ],
            [
                'DMRE,N,EDF',
                '01234567890',
                '5e3322503f09d1c3a807e4634a01150d',
            ],
            [
                'DMRE,N,BASE256',
                '01234567890',
                '3846d63ca676d95b18fdee5baa15ea8b',
            ],
            [
                'DMRE,GS1',
                // \xE8 is the control character FNC1 (ASCII 232)
                "\xE8" . '01034531200000111719112510ABCD1234',
                '84ca0c812edf9c4c5927008fd85f06b4',
            ],
            [
                'DMRE,GS1,C40',
                "\xE8" . '01095011010209171719050810ABCD1234' . "\xE8" . '2110',
                '873d77cabecba2062649d6f7ef3b1d97',
            ],
        ];
    }

    /**
     * The eighteen symbol sizes of Table 1 of ISO/IEC 21471, in the order the
     * automatic selection reaches them.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('symbolSizeDataProvider')]
    public function testSymbolSize(int $length, int $rows, int $cols): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('DMRE,N,BASE256', \str_pad('', $length, 'X'));
        $array = $type->getArray();

        $this->assertSame($rows, $array['nrows']);
        $this->assertSame($cols, $array['ncols']);
    }

    /**
     * @return array<string, array{int, int, int}>
     */
    public static function symbolSizeDataProvider(): array
    {
        // The BASE256 encodation spends two codewords on the latch and the
        // length, so a payload of two less than the data codewords of a size
        // fills it exactly.
        return [
            'shortest' => [1, 8, 48],
            '8x48' => [16, 8, 48],
            '8x64' => [22, 8, 64],
            '8x80' => [30, 8, 80],
            '8x96' => [36, 8, 96],
            '12x64' => [41, 12, 64],
            '20x36' => [42, 20, 36],
            '8x120' => [47, 8, 120],
            '20x44' => [54, 20, 44],
            '16x64' => [60, 16, 64],
            '8x144' => [61, 8, 144],
            '12x88' => [62, 12, 88],
            '26x40' => [68, 26, 40],
            '22x48' => [70, 22, 48],
            '24x48' => [78, 24, 48],
            '20x64' => [82, 20, 64],
            '26x48' => [88, 26, 48],
            '24x64' => [106, 24, 64],
            '26x64' => [116, 26, 64],
        ];
    }

    /**
     * The symbol attribute table must hold the eighteen symbols of Table 1 of
     * ISO/IEC 21471.
     */
    public function testSymbolAttributesMatchTableOne(): void
    {
        $expected = [];
        foreach (self::TABLE_ONE as $size) {
            [$rows, $cols, $drows, $dcols, $regions, $data, $ecc] = $size;
            $this->assertSame($rows, $drows + 2, 'matrix rows of the ' . $rows . 'x' . $cols);
            $this->assertSame($cols, ($dcols + 2) * $regions, 'matrix columns of the ' . $rows . 'x' . $cols);
            $expected[] = [
                $rows,
                $cols,
                $drows,
                $dcols * $regions,
                $drows + 2,
                $dcols + 2,
                $drows,
                $dcols,
                1,
                $regions,
                $regions,
                $data,
                $ecc,
                1,
                $data,
                $ecc,
            ];
        }

        $this->assertSame($expected, Data::SYMBATTR['E'] ?? []);
    }

    /**
     * The reference symbol of ISO/IEC 21471:2025 must read back as its stated
     * data content, and its error correction codewords must be reproduced.
     *
     * @throws \RuntimeException
     */
    public function testReferenceSymbol(): void
    {
        $codewords = self::readReferenceCodewords();

        $data = \array_slice($codewords, 0, self::REFERENCE_DATA_CODEWORDS);
        $decoder = new DatamatrixDecoder();
        $this->assertSame('A1B2C3D4E5F6G7H8I9J0K1L2', $decoder->decode($data));

        $errorCorrection = new ErrorCorrection();
        $this->assertSame($codewords, $errorCorrection->getErrorCorrection(
            $data,
            1,
            self::REFERENCE_DATA_CODEWORDS,
            self::REFERENCE_ERROR_CODEWORDS,
        ));
    }

    /**
     * The encoder must draw the reference symbol of ISO/IEC 21471 module for
     * module, given its content, its encodation and its size.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbolIsDrawn(): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj(
            'DMRE,N,C40,' . self::REFERENCE_ROWS . 'x' . self::REFERENCE_COLS,
            'A1B2C3D4E5F6G7H8I9J0K1L2',
        );
        $this->assertSame(self::REFERENCE, \explode("\n", \trim($type->getGrid())));
    }

    /**
     * The finder pattern of every region of the reference symbol must match the
     * one the encoder draws: a solid left column and bottom row, and alternating
     * top row and right column.
     */
    public function testReferenceSymbolFinderPattern(): void
    {
        foreach (self::REFERENCE as $row => $line) {
            for ($col = 0; $col < self::REFERENCE_COLS; ++$col) {
                $module = (int) ($line[$col] ?? '0');
                $regionCol = $col % self::REFERENCE_REGION_COLS;
                if ($row === (self::REFERENCE_ROWS - 1) || $regionCol === 0) {
                    $this->assertSame(1, $module, 'L pattern');
                } elseif ($row === 0) {
                    $this->assertSame((int) (($regionCol % 2) === 0), $module, 'top pattern');
                } elseif ($regionCol === (self::REFERENCE_REGION_COLS - 1)) {
                    $this->assertSame((int) (($row % 2) > 0), $module, 'right pattern');
                }
            }
        }
    }

    /**
     * The encoder must draw every published reference symbol module for module,
     * given the content and the size the published symbol has.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbolsAreDrawn(): void
    {
        $barcode = $this->getTestObject();
        foreach (DmreReferenceSymbols::SYMBOLS as $name => $symbol) {
            $type = $barcode->getBarcodeObj('DMRE,N,ASCII,' . $name, DmreReferenceSymbols::CONTENT);
            $this->assertSame($symbol, \explode("\n", \trim($type->getGrid())), 'modules of the ' . $name);
        }
    }

    /**
     * The size parameter must select the named symbol size, whatever the length
     * of the data.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSizeParameter(): void
    {
        $barcode = $this->getTestObject();
        foreach (self::TABLE_ONE as $size) {
            [$rows, $cols] = $size;
            $name = $rows . 'x' . $cols;
            $array = $barcode->getBarcodeObj('DMRE,N,ASCII,' . $name, '1234')->getArray();
            $this->assertSame($rows, $array['nrows'], 'rows of the ' . $name);
            $this->assertSame($cols, $array['ncols'], 'columns of the ' . $name);
        }
    }

    /**
     * The size parameter must accept an upper case or a padded spelling, and
     * must fall back to the automatic size when it names no symbol.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('looseSizeDataProvider')]
    public function testLooseSizeParameter(string $size, int $rows, int $cols): void
    {
        $barcode = $this->getTestObject();
        $array = $barcode->getBarcodeObj('DMRE,N,ASCII,' . $size, '1234')->getArray();
        $this->assertSame($rows, $array['nrows']);
        $this->assertSame($cols, $array['ncols']);
    }

    /**
     * @return array<string, array{string, int, int}>
     */
    public static function looseSizeDataProvider(): array
    {
        return [
            'canonical' => ['20x36', 20, 36],
            'upper case' => ['20X36', 20, 36],
            'padded' => [' 20x36 ', 20, 36],
            'empty' => ['', 8, 48],
            'unknown' => ['7x7', 8, 48],
        ];
    }

    /**
     * A payload longer than the named symbol size holds must be rejected.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSizeParameterCapacityException(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DMRE,N,BASE256,8x48', \str_pad('', 17, 'X'));
    }

    /**
     * The symbol character placement must cover the mapping matrix of every
     * square, rectangular and DMRE size exactly once, with no module left
     * unplaced and none placed outside the matrix.
     */
    public function testPlacementMapCoversEverySize(): void
    {
        foreach (Data::SYMBATTR as $shape => $sizes) {
            foreach ($sizes as $size) {
                $name = $shape . ' ' . $size[0] . 'x' . $size[1];
                $places = (new Encode())->getPlacementMap($size[2], $size[3]);
                $cells = $size[2] * $size[3];
                $this->assertCount($cells, $places, 'modules of the ' . $name);
                $this->assertSame($cells - 1, \max(\array_keys($places)), 'last module of the ' . $name);

                // Every bit of every codeword lies inside the matrix and lies
                // there once. The modules that hold none are the fixed pattern
                // of an untouched lower right corner.
                $bits = \array_filter($places, static fn(int $place): bool => $place > 1);
                $this->assertCount(8 * ($size[11] + $size[12]), $bits, 'placed bits of the ' . $name);
                $this->assertSameSize($bits, \array_unique($bits), 'twice placed bits of the ' . $name);
            }
        }
    }

    /**
     * Every symbol size must read back its published reference symbol: the
     * finder pattern of its data regions, the encoded content and the error
     * correction codewords.
     *
     * @throws \RuntimeException
     */
    public function testPublishedReferenceSymbols(): void
    {
        $decoder = new DatamatrixDecoder();
        $errorCorrection = new ErrorCorrection();

        foreach (self::TABLE_ONE as $size) {
            [$rows, $cols, $drows, $dcols, $regions, $data, $ecc] = $size;
            $name = $rows . 'x' . $cols;
            $symbol = DmreReferenceSymbols::SYMBOLS[$name] ?? [];
            $this->assertCount($rows, $symbol, 'rows of the ' . $name);
            $regionCols = \intdiv($cols, $regions);

            foreach ($symbol as $row => $line) {
                $this->assertSame($cols, \strlen($line), 'columns of the ' . $name);
                for ($col = 0; $col < $cols; ++$col) {
                    $module = (int) $line[$col];
                    $regionCol = $col % $regionCols;
                    if ($row === ($rows - 1) || $regionCol === 0) {
                        $this->assertSame(1, $module, 'L pattern of the ' . $name);
                    } elseif ($row === 0) {
                        $this->assertSame((int) (($regionCol % 2) === 0), $module, 'top pattern of the ' . $name);
                    } elseif ($regionCol === ($regionCols - 1)) {
                        $this->assertSame((int) (($row % 2) > 0), $module, 'right pattern of the ' . $name);
                    }
                }
            }

            $codewords = self::readSymbolCodewords($symbol, $drows, $dcols, $regions, $regionCols, $data + $ecc);
            $payload = \array_slice($codewords, 0, $data);
            $this->assertSame(DmreReferenceSymbols::CONTENT, $decoder->decode($payload), 'content of the ' . $name);
            $this->assertSame(
                $codewords,
                $errorCorrection->getErrorCorrection($payload, 1, $data, $ecc),
                'error correction of the ' . $name,
            );
        }
    }

    /**
     * The data codeword region must decode back to the encoded payload in every
     * symbol size.
     *
     * @param array<int, string> $params  DMRE parameters.
     * @param string             $payload Data to encode.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \RuntimeException
     */
    #[DataProvider('roundTripDataProvider')]
    public function testCodewordsRoundTrip(array $params, string $payload): void
    {
        $dmre = new InternalDmre('X', -1, -1, 'black', $params);
        $decoder = new DatamatrixDecoder();

        $this->assertSame($payload, $decoder->decode($dmre->exposeDataCodewords($payload)));
    }

    /**
     * @return array<string, array{array<int, string>, string}>
     */
    public static function roundTripDataProvider(): array
    {
        $cases = [
            'ascii' => [['N', 'ASCII'], 'A1B2C3D4E5F6G7H8I9J0K1L2'],
            'c40' => [['N', 'C40'], 'DATA MATRIX RECTANGULAR'],
            'text' => [['N', 'TXT'], 'rectangular extension'],
            'x12' => [['N', 'X12'], 'ABC*DEF>GHI'],
            'edifact' => [['N', 'EDF'], 'EDIFACT/DMRE+21471'],
            'base 256' => [['N', 'BASE256'], "\x00\x01\xFE\xFF"],
            'digits' => [[], '01234567890123456789'],
        ];

        foreach ([1, 17, 23, 31, 37, 42, 48, 62] as $length) {
            $cases['base 256 of ' . $length . ' bytes'] = [['N', 'BASE256'], \str_pad('', $length, 'X')];
        }

        return $cases;
    }

    /**
     * An explicitly requested symbol size must reach the high level encoder, so
     * that the rules that end an encodation without an unlatch are decided
     * against the capacity of the requested symbol and not of the smallest one
     * that fits.
     *
     * @return array<string, array{string}>
     */
    public static function explicitSizeProvider(): array
    {
        $cases = [];
        foreach (DmreSize::cases() as $size) {
            if ($size === DmreSize::Auto) {
                continue;
            }

            $cases[$size->value] = [$size->value];
        }

        return $cases;
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \RuntimeException
     */
    #[DataProvider('explicitSizeProvider')]
    public function testExplicitSizeRoundTrip(string $size): void
    {
        $decoder = new DatamatrixDecoder();
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $checked = 0;

        foreach (['ASCII', 'C40', 'TXT', 'X12', 'EDIFACT', 'BASE256'] as $encoding) {
            for ($len = 1; $len <= 30; ++$len) {
                $payload = \substr(\str_repeat($alphabet, 2), 0, $len);
                $dmre = new InternalDmre('X', -1, -1, 'black', ['N', $encoding, $size]);

                try {
                    $codewords = $dmre->exposeDataCodewords($payload);
                } catch (\Com\Tecnick\Barcode\Exception) {
                    // the payload does not fit this symbol size
                    continue;
                }

                $this->assertSame(
                    $payload,
                    $decoder->decode($codewords),
                    $encoding . ' of ' . $len . ' characters in ' . $size,
                );
                ++$checked;
            }
        }

        $this->assertGreaterThan(0, $checked);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getStringDataProvider')]
    public function testStrings(string $code): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('DMRE', \substr($code, 0, 16));
        $this->assertNotNull($type); // @phpstan-ignore method.alreadyNarrowedType
    }

    /**
     * @return array<array{string}>
     */
    public static function getStringDataProvider(): array
    {
        return \Test\TestStrings::$data;
    }

    /**
     * Read the codewords of a symbol out of its mapping matrix.
     *
     * @param array<int, string> $symbol     Module rows of the symbol.
     * @param int                $mapRows    Rows of the mapping matrix.
     * @param int                $dataCols   Columns of a data region without the finder pattern.
     * @param int                $regions    Number of data regions.
     * @param int                $regionCols Columns of a data region with the finder pattern.
     * @param int                $total      Number of data and error correction codewords.
     *
     * @return array<int, int>
     */
    private static function readSymbolCodewords(
        array $symbol,
        int $mapRows,
        int $dataCols,
        int $regions,
        int $regionCols,
        int $total,
    ): array {
        $mapCols = $dataCols * $regions;
        $places = (new Encode())->getPlacementMap($mapRows, $mapCols);
        $codewords = \array_fill(0, \max(0, $total), 0);
        for ($row = 0; $row < $mapRows; ++$row) {
            $bits = '';
            for ($region = 0; $region < $regions; ++$region) {
                $bits .= \substr($symbol[$row + 1] ?? '', ($region * $regionCols) + 1, $dataCols);
            }

            for ($col = 0; $col < $mapCols; ++$col) {
                $place = $places[($row * $mapCols) + $col] ?? 0;
                if ($place < 2 || ($bits[$col] ?? '0') === '0') {
                    continue;
                }

                $idx = \intdiv($place, 10) - 1;
                $codewords[$idx] = ($codewords[$idx] ?? 0) | (1 << (8 - ($place % 10)));
            }
        }

        return $codewords;
    }

    /**
     * Read the codewords of the reference symbol out of its mapping matrix.
     *
     * @return array<int, int>
     */
    private static function readReferenceCodewords(): array
    {
        $dataCols = \intdiv(self::REFERENCE_MAP_COLS, self::REFERENCE_REGIONS);
        $map = [];
        foreach (self::REFERENCE as $row => $line) {
            if ($row < 1 || $row > self::REFERENCE_MAP_ROWS) {
                continue;
            }

            $bits = '';
            for ($region = 0; $region < self::REFERENCE_REGIONS; ++$region) {
                $bits .= \substr($line, ($region * self::REFERENCE_REGION_COLS) + 1, $dataCols);
            }

            $map[$row - 1] = $bits;
        }

        $places = (new Encode())->getPlacementMap(self::REFERENCE_MAP_ROWS, self::REFERENCE_MAP_COLS);
        $codewords = \array_fill(0, self::REFERENCE_DATA_CODEWORDS + self::REFERENCE_ERROR_CODEWORDS, 0);
        foreach ($map as $row => $bits) {
            for ($col = 0; $col < self::REFERENCE_MAP_COLS; ++$col) {
                $place = $places[($row * self::REFERENCE_MAP_COLS) + $col] ?? 0;
                if ($place < 2 || ($bits[$col] ?? '0') === '0') {
                    continue;
                }

                $idx = \intdiv($place, 10) - 1;
                $codewords[$idx] = ($codewords[$idx] ?? 0) | (1 << (8 - ($place % 10)));
            }
        }

        return $codewords;
    }
}
