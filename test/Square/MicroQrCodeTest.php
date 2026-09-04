<?php

/**
 * MicroQrCodeTest.php
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

use Com\Tecnick\Barcode\Type\Square\MicroQrCode\Data;
use Com\Tecnick\Barcode\Type\Square\MicroQrCode\Encode;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\MicroQrCodeDecoder;
use Test\TestUtil;

/**
 * Micro QR Code Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class MicroQrCodeTest extends TestUtil
{
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
     * The version M2-L symbol of Figure 2 and Annex I.3 of ISO/IEC 18004,
     * encoding 01234567 in the numeric mode with the data mask pattern 01.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbol(): void
    {
        $this->assertSame(
            [
                '1111111010101',
                '1000001011101',
                '1011101001101',
                '1011101001111',
                '1011101011100',
                '1000001010001',
                '1111111001111',
                '0000000001100',
                '1101000010001',
                '0110101010101',
                '1110011111110',
                '0001010000110',
                '1110100110111',
            ],
            $this->getRows('MICROQR,L', '01234567'),
        );
    }

    /**
     * Annex I.3 of ISO/IEC 18004 gives the five error correction codewords
     * 86 0D 22 AE 30 for the data codewords 40 18 AC C3 00.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceCheckwords(): void
    {
        $decoder = new MicroQrCodeDecoder($this->getRows('MICROQR,L', '01234567'));
        $this->assertSame([0x86, 0x0D, 0x22, 0xAE, 0x30], $decoder->getCheckwords());
    }

    /**
     * The format information of the reference symbol carries the symbol
     * number 1, that is the version M2 with the error correction level L, and
     * the data mask pattern reference 01.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceFormatInformation(): void
    {
        $decoder = new MicroQrCodeDecoder($this->getRows('MICROQR,L', '01234567'));
        $this->assertSame(1, $decoder->getSymbolNumber());
        $this->assertSame(1, $decoder->getMask());
    }

    /**
     * @return array<int, array{string, string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['MICROQR,L',   '01234567',            'a0f56387c6d9162b733b0c61c13282fb'],
            ['MICROQR',     '12345',               '7e6d5c6f6a3d3d6909f3cf24f053961a'],
            ['MICROQR,L',   'Wikipedia',           '30ecb1874fb7498767a017f3b561a593'],
            ['MICROQR,Q',   'ABCDEFGHIJKLM',       '36ae116093170a641633ad958c24e342'],
            ['MICROQR,M,4', '0123456789012345678', 'ed467e78a6c8ec7c7d5c84e102e787c7'],
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
        $this->assertEquals($expected, \md5($barcode->getBarcodeObj($type, $code)->getGrid()));
    }

    /**
     * @return array<int, array{int, int}>
     */
    public static function symbolSizeProvider(): array
    {
        return [
            [1, 11],
            [2, 13],
            [3, 15],
            [4, 17],
        ];
    }

    /**
     * The four versions are 11, 13, 15 and 17 modules per side.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('symbolSizeProvider')]
    public function testSymbolSize(int $version, int $size): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('MICROQR,,' . $version, '1')->getArray();
        $this->assertSame($size, $data['ncols']);
        $this->assertSame($size, $data['nrows']);
    }

    /**
     * The finder pattern, its separator and the timing patterns of every version.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('symbolSizeProvider')]
    public function testFunctionPatterns(int $version, int $size): void
    {
        $rows = $this->getRows('MICROQR,,' . $version, '1');
        $this->assertSame('1111111', \substr($rows[0] ?? '', 0, 7));
        $this->assertSame('1000001', \substr($rows[1] ?? '', 0, 7));
        $this->assertSame('1011101', \substr($rows[2] ?? '', 0, 7));
        $this->assertSame('1111111', \substr($rows[6] ?? '', 0, 7));
        $this->assertSame('00000000', \substr($rows[7] ?? '', 0, 8));
        for ($pos = 8; $pos < $size; ++$pos) {
            $expected = (string) (1 - ($pos % 2));
            $this->assertSame($expected, \substr($rows[0] ?? '', $pos, 1));
            $this->assertSame($expected, \substr($rows[$pos] ?? '', 0, 1));
        }
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function roundTripProvider(): array
    {
        return [
            ['MICROQR',       '12345'],
            ['MICROQR',       '1'],
            ['MICROQR',       '0123456789'],
            ['MICROQR',       '01234567890123456789012'],
            ['MICROQR',       '01234567890123456789012345678901234'],
            ['MICROQR',       'AB'],
            ['MICROQR',       'ABCDEFGHIJKLMN'],
            ['MICROQR',       'ABCDEFGHIJKLMNOPQRSTU'],
            ['MICROQR',       'A 1$%*+-./:'],
            ['MICROQR',       'Wikipedia'],
            ['MICROQR',       'Hello world!!!!'],
            ['MICROQR,L',     '01234567'],
            ['MICROQR,M',     '0123456789012345678'],
            ['MICROQR,Q',     'ABCDEFGHIJKLM'],
            ['MICROQR,,4',    '1'],
            ['MICROQR,,3,8B', 'abcdefg'],
            ['MICROQR,,4,AN', '1234'],
            ['MICROQR,,2,NM', '01234567'],
            ['MICROQR',       "123\n"],
            ['MICROQR',       "AB\n"],
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
        $decoder = new MicroQrCodeDecoder($this->getRows($type, $code));
        $this->assertSame($code, $decoder->getMessage());
        $this->assertTrue($decoder->hasValidCheckwords());
    }

    /**
     * @return array<int, array{int, int, int, int}>
     */
    public static function capacityProvider(): array
    {
        // symbol number, digits, alphanumeric characters and bytes of Table 7
        return [
            [0, 5, 0, 0],
            [1, 10, 6, 0],
            [2, 8, 5, 0],
            [3, 23, 14, 9],
            [4, 18, 11, 7],
            [5, 35, 21, 15],
            [6, 30, 18, 13],
            [7, 21, 13, 9],
        ];
    }

    /**
     * The input data capacities of Table 7 of ISO/IEC 18004.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('capacityProvider')]
    public function testDataCapacity(int $number, int $numeric, int $alphanum, int $byte): void
    {
        $symbol = Data::SYMBOLS[$number] ?? [];
        $type = 'MICROQR,' . ($symbol[1] ?? '') . ',' . ($symbol[0] ?? 1) . ',';
        $samples = [
            'NM' => ['7', $numeric],
            'AN' => ['A', $alphanum],
            '8B' => ['a', $byte],
        ];
        foreach ($samples as $mode => $sample) {
            [$char, $max] = $sample;
            if ($max > 0) {
                $barcode = $this->getTestObject();
                $barcode->getBarcodeObj($type . $mode, \str_repeat($char, \max(0, $max)))->getGrid();
            }

            $this->bcAssertOverflow($type . $mode, \str_repeat($char, \max(1, $max + 1)));
        }
    }

    /**
     * @return array<int, array{string, int, string}>
     */
    public static function capacityMessageProvider(): array
    {
        return [
            ['MICROQR,M,1', 50, 'Table 13 of ISO/IEC 18004 has no symbol'],
            ['MICROQR,Q,2', 50, 'Table 13 of ISO/IEC 18004 has no symbol'],
            ['MICROQR,L,2', 50, 'The data does not fit in the requested symbol version'],
            ['MICROQR,Q',   50, 'try a lower error correction level'],
            ['MICROQR',     50, 'The data does not fit in a Micro QR Code symbol'],
        ];
    }

    /**
     * A version and error correction level Table 13 does not pair is reported
     * as such rather than as a capacity failure, a code too long for a version
     * the caller asked for names the version, and no capacity message repeats
     * the code it was given.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('capacityMessageProvider')]
    public function testCapacityExceptionMessage(string $options, int $length, string $expected): void
    {
        $code = \str_repeat('8', \max(0, $length));

        try {
            $this->getTestObject()->getBarcodeObj($options, $code);
            self::fail('Expected a capacity exception for ' . $options);
        } catch (\Com\Tecnick\Barcode\Exception $exception) {
            $this->assertStringContainsString($expected, $exception->getMessage());
            $this->assertStringNotContainsString($code, $exception->getMessage());
            $this->assertLessThan(120, \strlen($exception->getMessage()));
        }
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

        $this->fail('The code ' . $code . ' should not fit in ' . $type);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            ['MICROQR',       ''],
            ['MICROQR',       '012345678901234567890123456789012345'],
            ['MICROQR',       'ABCDEFGHIJKLMNOPQRSTUV'],
            ['MICROQR,,1',    'A'],
            ['MICROQR,,2',    'a'],
            ['MICROQR,,3,NM', 'A'],
            ['MICROQR,,3,AN', 'a'],
            ['MICROQR,Q',     '012345678901234567890123456789'],
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
     * @return array<int, array{string, string, int, int, string}>
     */
    public static function encoderProvider(): array
    {
        // code, requested level, requested version, symbol version and level
        return [
            ['01234567',            'L', 0, 2, 'L'],
            // the version M1 carries error detection only
            ['12345',               '',  0, 1, ''],
            ['Wikipedia',           'L', 0, 3, 'L'],
            ['ABCDEFGHIJKLM',       'Q', 0, 4, 'Q'],
            ['0123456789012345678', 'M', 4, 4, 'M'],
        ];
    }

    /**
     * The encoder reports the symbol it built and the mask it chose.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('encoderProvider')]
    public function testEncoderSymbol(string $code, string $level, int $version, int $expver, string $explevel): void
    {
        $encode = new Encode($code, $level, $version, -1);

        $this->assertSame($expver, $encode->getVersion());
        $this->assertSame($explevel, $encode->getLevel());
        $this->assertGreaterThanOrEqual(0, $encode->getMask());
        $this->assertLessThanOrEqual(3, $encode->getMask());

        $decoder = new MicroQrCodeDecoder($encode->getGrid());
        $this->assertSame($encode->getMask(), $decoder->getMask());
        $this->assertSame($code, $decoder->getMessage());
    }
}
