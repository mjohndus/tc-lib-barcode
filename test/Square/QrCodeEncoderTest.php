<?php

/**
 * QrCodeEncoderTest.php
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
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalQrEncode;
use Test\Fixture\QrCodeDecoder;
use Test\TestUtil;

/**
 * QR Code encoder test.
 *
 * @since       2026-09-02
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class QrCodeEncoderTest extends TestUtil
{
    /**
     * The symbol of Figure I.2 of ISO/IEC 18004, which encodes 01234567 in the
     * version 1 at the error correction level M with the data mask pattern 010.
     * Read off the figure; its grey cells, which the figure marks as the format
     * information and the always dark modules, are resolved from the format
     * information string the figure prints beside it, 101111001111100.
     *
     * @var array<int, string>
     */
    private const ANNEX_I_SYMBOL = [
        '111111100101101111111',
        '100000100111101000001',
        '101110101000001011101',
        '101110101100001011101',
        '101110101011101011101',
        '100000101000101000001',
        '111111101010101111111',
        '000000001001100000000',
        '101111100100101111100',
        '000101011010100101100',
        '001000110101010011111',
        '000010000100000111100',
        '000111111001010010000',
        '000000001011111001100',
        '111111100110101100000',
        '100000101011111000101',
        '101110101000100101100',
        '101110101100100100000',
        '101110101011010010100',
        '100000100000000110110',
        '111111101111010010100',
    ];

    /**
     * The final codeword sequence of the worked example of Annex I of
     * ISO/IEC 18004: sixteen data codewords and ten error correction codewords.
     *
     * @var array<int, int>
     */
    private const ANNEX_I_CODEWORDS = [
        0x10,
        0x20,
        0x0C,
        0x56,
        0x61,
        0x80,
        0xEC,
        0x11,
        0xEC,
        0x11,
        0xEC,
        0x11,
        0xEC,
        0x11,
        0xEC,
        0x11,
        0xA5,
        0x24,
        0xD4,
        0xC1,
        0xED,
        0x36,
        0xC7,
        0x87,
        0x2C,
        0x55,
    ];

    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Encoding 01234567 in the version 1 at the level M with the mask 010 gives
     * the symbol of Figure I.2 of ISO/IEC 18004, module for module.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testAnnexIReferenceSymbol(): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('QRCODE,M,NM,1,1,0,0,2', '01234567');

        $this->assertSame(self::ANNEX_I_SYMBOL, \explode("\n", \trim($type->getGrid())));
    }

    /**
     * The codewords of the worked example of Annex I of ISO/IEC 18004, read back
     * out of the symbol.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testAnnexICodewords(): void
    {
        $decoder = new QrCodeDecoder(self::ANNEX_I_SYMBOL);

        $this->assertSame(1, $decoder->getVersion());
        $this->assertSame(Data::ECC_LEVELS['M'] ?? 1, $decoder->getLevel());
        $this->assertSame(2, $decoder->getMask());
        $this->assertSame('01234567', $decoder->getMessage());
        $this->assertTrue($decoder->hasValidCheckwords());
        $this->assertSame(self::ANNEX_I_CODEWORDS, $decoder->getCodewords());
    }

    /**
     * The data mask pattern the encoder picks on its own is the one with the
     * fewest penalty points of Table 11 of ISO/IEC 18004, which for this symbol
     * is not the mask 010 the informative example of Annex I uses.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testTheChosenMaskHasTheFewestPenaltyPoints(): void
    {
        $barcode = $this->getTestObject();
        $chosen = \explode("\n", \trim($barcode->getBarcodeObj('QRCODE,M,NM,1', '01234567')->getGrid()));
        $mask = (new QrCodeDecoder($chosen))->getMask();
        $encode = InternalQrEncode::forSymbol();
        $penalties = [];
        for ($pattern = 0; $pattern < Encode::MASKS; ++$pattern) {
            $grid = \explode(
                "\n",
                \trim($barcode->getBarcodeObj('QRCODE,M,NM,1,1,0,0,' . $pattern, '01234567')->getGrid()),
            );
            $penalties[$pattern] = \array_sum($encode->exposePenalties($grid));
        }

        $this->assertSame(\min($penalties), $penalties[$mask] ?? 0);
    }

    /**
     * Every version, error correction level and data mask pattern round trips
     * through the decoder, and every block carries the error correction
     * codewords its data codewords generate.
     *
     * @return array<string, array{string, string}>
     */
    public static function roundTripProvider(): array
    {
        $cases = [
            'numeric' => ['QRCODE,M,NM', '0123456789'],
            'alphanumeric' => ['QRCODE,M,AN', 'HELLO WORLD $%*+-./:'],
            'byte' => ['QRCODE,M,8B', 'https://tecnick.com'],
            'kanji' => ['QRCODE,H,KJ', "\x93\x5F\xE4\xAA\x81\x40"],
            'mixed modes' => ['QRCODE,L,8B', 'ABC123abc456DEF789'],
            'the whole byte range' => ['QRCODE,L,8B', \implode('', \array_map(\chr(...), \range(0, 255)))],
            'a long numeric run' => ['QRCODE,L,NM', \str_pad('', 1500, '0123456789')],
            'a long byte run' => ['QRCODE,H,8B', \str_pad('', 400, 'x')],
        ];
        foreach (['L', 'M', 'Q', 'H'] as $level) {
            $cases['level ' . $level] = ['QRCODE,' . $level, 'Level ' . $level . ' round trip'];
        }

        for ($version = Data::VERSION_MIN; $version <= Data::VERSION_MAX; ++$version) {
            $cases['version ' . $version] = [
                'QRCODE,M,8B,' . $version,
                \str_pad('', \min(80, 2 + $version), 'v'),
            ];
        }

        for ($mask = 0; $mask < Encode::MASKS; ++$mask) {
            $cases['mask ' . $mask] = ['QRCODE,L,8B,0,1,0,0,' . $mask, 'mask ' . $mask];
        }

        return $cases;
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('roundTripProvider')]
    public function testRoundTrip(string $options, string $code): void
    {
        $barcode = $this->getTestObject();
        $grid = \explode("\n", \trim($barcode->getBarcodeObj($options, $code)->getGrid()));
        $decoder = new QrCodeDecoder($grid);

        $this->assertSame($code, $decoder->getMessage());
        $this->assertTrue($decoder->hasValidCheckwords());
    }

    /**
     * The versions 7 and above carry the version information twice, and both
     * copies state the version the symbol size gives.
     *
     * @return array<string, array{int}>
     */
    public static function versionInfoProvider(): array
    {
        $cases = [];
        foreach ([6, 7, 8, 20, 40] as $version) {
            $cases['version ' . $version] = [$version];
        }

        return $cases;
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('versionInfoProvider')]
    public function testVersionInformationIsPlaced(int $version): void
    {
        $barcode = $this->getTestObject();
        $grid = \explode("\n", \trim($barcode->getBarcodeObj('QRCODE,M,8B,' . $version, 'test')->getGrid()));
        $decoder = new QrCodeDecoder($grid);

        $this->assertSame($version, $decoder->getVersion());
        $this->assertSame($version < Data::VERSION_INFO_MIN ? -1 : $version, $decoder->getVersionInfo());
        if ($version < Data::VERSION_INFO_MIN) {
            return;
        }

        // the two copies of the version information hold the same bits
        $size = \count($grid);
        for ($pos = 0; $pos < 18; ++$pos) {
            $first = $grid[$size - 11 + ($pos % 3)][\intdiv($pos, 3)] ?? '';
            $second = $grid[\intdiv($pos, 3)][$size - 11 + ($pos % 3)] ?? '';
            $this->assertSame($first, $second);
        }
    }

    /**
     * The encoder picks the smallest version that carries the data at the
     * requested error correction level.
     *
     * @return array<string, array{string, string, int}>
     */
    public static function versionSelectionProvider(): array
    {
        return [
            'seventeen digits fit the version 1 at the level H' => ['H', \str_pad('', 17, '7'), 1],
            'eighteen digits need the version 2 at the level H' => ['H', \str_pad('', 18, '7'), 2],
            'seventeen bytes fit the version 1 at the level L' => ['L', \str_pad('', 17, 'x'), 1],
            'eighteen bytes need the version 2 at the level L' => ['L', \str_pad('', 18, 'x'), 2],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('versionSelectionProvider')]
    public function testVersionSelection(string $level, string $code, int $expected): void
    {
        $barcode = $this->getTestObject();
        $grid = \explode("\n", \trim($barcode->getBarcodeObj('QRCODE,' . $level, $code)->getGrid()));

        $this->assertCount((4 * $expected) + 17, $grid);
    }

    /**
     * The mode segmentation takes the fewest bits, so it never costs more than
     * encoding the whole input in the byte mode.
     *
     * @return array<string, array{string}>
     */
    public static function segmentationProvider(): array
    {
        return [
            'digits' => ['12345678901234567890'],
            'letters' => ['ABCDEFGHIJKLMNOPQRST'],
            'digits then letters' => ['1234567890ABCDEFGHIJ'],
            'a short digit run inside letters' => ['ABCDEFGH12IJKLMNOPQR'],
            'a long digit run inside letters' => ['ABC1234567890123DEFG'],
            'lower case inside upper case' => ['ABCDEFGHIJklmnOPQRST'],
            'one of each' => ['A1a'],
        ];
    }

    #[DataProvider('segmentationProvider')]
    public function testSegmentationIsNoWorseThanASingleByteSegment(string $code): void
    {
        $encode = InternalQrEncode::forSymbol();
        $segments = $encode->exposeSegments($code, 0, false);
        $chosen = $encode->exposeStreamBits($segments, 0);
        $single =
            $encode->exposeHeaderBits(Data::MODE_BYTE, 0) + $encode->exposeDataBits(Data::MODE_BYTE, \strlen($code));

        $this->assertLessThanOrEqual($single, $chosen);
        // the segments cover the whole input, in order
        $offset = 0;
        foreach ($segments as $segment) {
            $this->assertSame($offset, $segment[1]);
            $offset += $segment[2] * ($segment[0] === Data::MODE_KANJI ? 2 : 1);
        }

        $this->assertSame(\strlen($code), $offset);
    }

    /**
     * A segment longer than its character count indicator can declare is split
     * on a group boundary of its mode.
     */
    public function testLongSegmentsAreSplit(): void
    {
        $encode = InternalQrEncode::forSymbol();
        // the numeric character count indicator of the versions 1 to 9 is ten
        // bits, so a segment holds at most 1023 characters
        $segments = $encode->exposeSegments(\str_pad('', 2000, '1'), 0, false);

        $this->assertGreaterThan(1, \count($segments));
        foreach ($segments as $segment) {
            $this->assertSame(Data::MODE_NUMERIC, $segment[0]);
            $this->assertLessThanOrEqual(1023, $segment[2]);
        }
    }

    /**
     * The blocks are interleaved one codeword at a time, and the blocks that are
     * already exhausted are skipped, section 7.6 of ISO/IEC 18004.
     */
    public function testInterleave(): void
    {
        $encode = InternalQrEncode::forSymbol();

        $this->assertSame([1, 4, 7, 2, 5, 8, 3, 6, 9], $encode->exposeInterleave([[1, 2, 3], [4, 5, 6], [7, 8, 9]]));
        $this->assertSame([1, 3, 6, 2, 4, 7, 5, 8], $encode->exposeInterleave([[1, 2], [3, 4, 5], [6, 7, 8]]));
    }

    /**
     * An input beyond the capacity of the largest symbol is rejected.
     *
     * @return array<string, array{string, string}>
     */
    public static function overflowProvider(): array
    {
        return [
            'beyond the numeric capacity' => ['QRCODE,L,NM', \str_pad('', 7090, '1')],
            'beyond the byte capacity' => ['QRCODE,L,8B', \str_pad('', 3000, 'x')],
            'beyond the capacity of the requested version' => ['QRCODE,H,8B,1', \str_pad('', 100, 'x')],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('overflowProvider')]
    public function testCapacityOverflow(string $options, string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $this->getTestObject()->getBarcodeObj($options, $code);
    }

    /**
     * The random mask option evaluates the given number of masks and still emits
     * a symbol that decodes.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Random\RandomException
     */
    public function testRandomMask(): void
    {
        $barcode = $this->getTestObject();
        for ($idx = 0; $idx < 8; ++$idx) {
            $grid = \explode("\n", \trim($barcode->getBarcodeObj('QRCODE,M,8B,0,1,3', 'random mask')->getGrid()));
            $decoder = new QrCodeDecoder($grid);

            $this->assertSame('random mask', $decoder->getMessage());
            $this->assertTrue($decoder->hasValidCheckwords());
        }
    }

    /**
     * With the case sensitive flag off the input is upper cased, which lets the
     * alphanumeric mode carry what the byte mode would otherwise have to.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCaseInsensitiveInput(): void
    {
        $barcode = $this->getTestObject();
        $grid = \explode("\n", \trim($barcode->getBarcodeObj('QRCODE,M,AN,0,0', 'hello world')->getGrid()));

        $this->assertSame('HELLO WORLD', (new QrCodeDecoder($grid))->getMessage());
    }

    /**
     * With the kanji mode on, the upper casing of a case insensitive input
     * leaves a byte pair that is a kanji character alone and upper cases the
     * rest.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCaseInsensitiveInputWithTheKanjiMode(): void
    {
        $barcode = $this->getTestObject();
        $grid = \explode("\n", \trim($barcode->getBarcodeObj('QRCODE,H,KJ,0,0', 'abc def')->getGrid()));

        $this->assertSame('ABC DEF', (new QrCodeDecoder($grid))->getMessage());
    }

    /**
     * The kanji mode is used only when it is asked for, so the same input is
     * carried by the byte mode otherwise.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testKanjiModeIsOptional(): void
    {
        $barcode = $this->getTestObject();
        $code = \str_repeat("\x93\x5F", 20);
        $kanji = \explode("\n", \trim($barcode->getBarcodeObj('QRCODE,M,KJ', $code)->getGrid()));
        $bytes = \explode("\n", \trim($barcode->getBarcodeObj('QRCODE,M,8B', $code)->getGrid()));

        $this->assertSame($code, (new QrCodeDecoder($kanji))->getMessage());
        $this->assertSame($code, (new QrCodeDecoder($bytes))->getMessage());
        // thirteen bits a character against sixteen, so the kanji symbol is no
        // larger than the byte one
        $this->assertLessThanOrEqual(\count($bytes), \count($kanji));
    }
}
