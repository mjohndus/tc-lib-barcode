<?php

/**
 * InternalHelpersTest.php
 *
 * @since       2026-04-19
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Test;

use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalBarcodeType;
use Test\Fixture\InternalQrByteStream;
use Test\Fixture\InternalQrEstimate;

/**
 * Internal helper methods test
 *
 * @since       2026-04-19
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalHelpersTest extends TestUtil
{
    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testBaseTypeDefaultHooksAreCovered(): void
    {
        $type = new InternalBarcodeType(true);
        $data = $type->getArray();

        $this->assertSame([], $data['params']);
        $this->assertSame([], $data['bars']);
        $this->assertSame(4, $data['width']);
        $this->assertSame(3, $data['height']);
        $this->assertSame(['T' => 6, 'R' => 2, 'B' => 0, 'L' => 1], $data['padding']);

        $type->setBackgroundColor('');
        $this->assertNull($type->getArray()['bg_color_obj']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrInputItemPaddingAndAppendBranches(): void
    {
        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);

        $item = $helper->exposeNewInputItem(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NM, 3, ['1']);

        $this->assertSame(['1', '0', '0'], $item['data']);
        $this->assertSame(-1, $helper->exposeLookAnTable(128));

        $items = $helper->exposeAppendNewInputItem(
            [],
            \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_AN,
            2,
            ['A', 'Z'],
        );

        $this->assertCount(1, $items);
        $firstItem = $items[0] ?? null;
        $this->assertNotNull($firstItem);
        $this->assertSame(['A', 'Z'], $firstItem['data']);
    }

    /**
     * @param array<int, string> $data
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getInvalidQrInputItemProvider')]
    public function testQrInputItemInvalidBranches(int $mode, int $size, array $data): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);

        $helper->exposeNewInputItem($mode, $size, $data);
    }

    /**
     * @return array<array{int, int, array<int, string>}>
     */
    public static function getInvalidQrInputItemProvider(): array
    {
        return [
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_AN, 1, ["\x80"]],
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_KJ, 1, ["\x81"]],
            [999, 1, ['A']],
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NM, 0, ['1']],
            // the numeric mode holds the digits only
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NM, 1, ['A']],
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NM, 2, ['1', ':']],
            // the Kanji mode holds the two byte characters of the Shift JIS
            // ranges 8140 to 9FFC and E040 to EBBF only
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_KJ, 2, ["\x00", "\x00"]],
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_KJ, 2, ["\xEC", "\x00"]],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrByteStreamPaddingAndBitstreamBranches(): void
    {
        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);
        $spec = new \Com\Tecnick\Barcode\Type\Square\QrCode\Spec();
        $maxWords = $spec->getDataLength(1, 0);
        $maxBits = $maxWords * 8;

        $this->assertSame([], $helper->exposeAppendPaddingBit([]));

        $exact = \array_fill(0, \max(0, $maxBits), 0);
        $this->assertSame($exact, $helper->exposeAppendPaddingBit($exact));

        $shortBits = \max(0, $maxBits - 2);
        $short = \array_fill(0, $shortBits, 1);
        $this->assertCount($maxBits, $helper->exposeAppendPaddingBit($short));

        $padded = $helper->exposeAppendPaddingBit([1, 0, 1, 0]);
        $this->assertCount($maxWords, $helper->exposeBitstreamToByte($padded));

        $this->assertSame([], $helper->exposeBitstreamToByte([]));
        $this->assertSame([21], $helper->exposeBitstreamToByte([1, 0, 1, 0, 1]));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrByteStreamCreateAndConvertBranches(): void
    {
        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);

        $item = $helper->exposeNewInputItem(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NM, 3, ['1', '2', '3']);
        $stream = $helper->exposeCreateBitStream([$item]);
        $this->assertGreaterThan(0, $stream[1]);

        $helper->forcedEstVer = 2;
        $helper->forcedMinVer = 2;
        $encoded = $helper->encodeBitStream($item, 2);
        $helper->queuedCbs = [
            [[$encoded], \count($encoded['bstream'])],
        ];

        $converted = $helper->exposeConvertData([$item]);
        $firstConverted = $converted[0] ?? null;
        $this->assertNotNull($firstConverted);
        $this->assertSame($encoded['bstream'], $firstConverted['bstream']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrByteStreamNegativeBitsThrowsException(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);
        $helper->forcedEstVer = 1;
        $helper->queuedCbs = [
            [[], -1],
        ];

        $helper->exposeConvertData([]);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrByteStreamEncodeBranches(): void
    {
        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);

        $spec = new \Com\Tecnick\Barcode\Type\Square\QrCode\Spec();
        $words = $spec->maximumWords(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1);
        $item = $helper->exposeNewInputItem(
            \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B,
            $words + 1,
            \array_fill(0, \max(0, $words + 1), 'A'),
        );

        $encoded = $helper->encodeBitStream($item, 1);
        $this->assertNotEmpty($encoded['bstream']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrByteStreamEncodeInvalidModeThrowsException(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);

        $helper->encodeBitStream([
            'mode' => 999,
            'size' => 1,
            'data' => ['A'],
            'bstream' => [],
        ], 1);
    }

    /**
     * The bit stream is left untouched when there is nothing to append.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrAppendEmptyBitstream(): void
    {
        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);

        $this->assertSame([1, 0, 1], $helper->exposeAppendBitstream([1, 0, 1], []));
        $this->assertSame([], $helper->exposeAppendBitstream([], []));
        $this->assertSame([1, 0, 1], $helper->exposeAppendNum([1, 0, 1], 0, 7));
        $this->assertSame([1, 0, 1], $helper->exposeAppendBytes([1, 0, 1], 0, [65]));

        $this->assertSame([1, 0, 1, 0, 1, 1], $helper->exposeAppendNum([1, 0, 1], 3, 3));
        $this->assertSame([0, 1, 0, 0, 0, 0, 0, 1], $helper->exposeAppendBytes([], 1, [65]));
    }

    /**
     * The structured append header of ISO/IEC 18004 is the mode indicator 0011,
     * the symbol sequence indicator of two groups of four bits, and the parity
     * of the whole message in eight bits.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrStructuredAppendHeader(): void
    {
        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);

        $encoded = $helper->encodeBitStream(
            [
                'mode' => \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_ST,
                'size' => 3,
                // second symbol of two, with the parity 0xA5
                'data' => [\chr(2), \chr(2), \chr(0xA5)],
                'bstream' => [],
            ],
            1,
        );

        $this->assertSame(
            [
                0,
                0,
                1,
                1, // mode indicator
                0,
                0,
                0,
                1, // symbol index, zero based
                0,
                0,
                0,
                1, // number of symbols, zero based
                1,
                0,
                1,
                0,
                0,
                1,
                0,
                1, // parity
            ],
            $encoded['bstream'],
        );
    }

    /**
     * The bit stream is built again when the codewords it holds need a version
     * larger than the current one.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrConvertDataRaisesTheVersion(): void
    {
        $helper = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);
        $helper->forcedEstVer = 1;
        $helper->forcedMinVer = 3;

        $item = $helper->exposeNewInputItem(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NM, 3, ['1', '2', '3']);
        $helper->exposeConvertData([$item]);

        $this->assertSame(3, $helper->version);
    }

    /**
     * @return array<int, array{string, int}>
     */
    public static function getEncModeValueProvider(): array
    {
        return [
            ['NL', \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NL],
            ['NM', \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NM],
            ['AN', \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_AN],
            ['8B', \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B],
            ['KJ', \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_KJ],
            ['ST', \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_ST],
            ['XX', 0],
        ];
    }

    #[DataProvider('getEncModeValueProvider')]
    public function testQrEstimateEncModeValue(string $mode, int $expected): void
    {
        $helper = new InternalQrEstimate();

        $this->assertSame($expected, $helper->exposeEncModeValue($mode));
    }

    /**
     * The capacity table holds the versions 1 to 40.
     *
     * @return array<int, array{int}>
     */
    public static function outOfRangeVersionProvider(): array
    {
        return [[-1], [41], [100]];
    }

    #[DataProvider('outOfRangeVersionProvider')]
    public function testQrCapacityOutOfRange(int $version): void
    {
        $helper = new InternalQrEstimate();

        $this->assertSame(0, $helper->exposeCapacityWordsValue($version));
        $this->assertSame(0, $helper->exposeCapacityEcValue($version, 0));
    }

    /**
     * The length indicator is defined for the four data modes only.
     *
     * @return array<int, array{int}>
     */
    public static function outOfRangeModeProvider(): array
    {
        return [
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_ST],
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_NL - 1],
            [\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_ST + 1],
        ];
    }

    #[DataProvider('outOfRangeModeProvider')]
    public function testQrLengthIndicatorOutOfRange(int $mode): void
    {
        $helper = new InternalQrEstimate();

        $this->assertSame(0, $helper->getLengthIndicator($mode, 1));
    }

    /**
     * The structured append header is 20 bits, and an unknown mode has no size.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrEstimateBitStreamSizeModes(): void
    {
        $helper = new InternalQrEstimate();

        $this->assertSame(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::STRUCTURE_HEADER_BITS, $helper->exposeEstimateBitStreamSize(
            [[
                'mode' => \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_ST,
                'size' => 3,
                'data' => [],
                'bstream' => [],
            ]],
            1,
        ));

        $this->assertSame(0, $helper->exposeEstimateBitStreamSize([[
            'mode' => 999,
            'size' => 1,
            'data' => [],
            'bstream' => [],
        ]], 1));
    }

    /**
     * The estimate fails when no version holds the data.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrEstimateVersionFailure(): void
    {
        $helper = new InternalQrEstimate();
        $helper->forcedMinVer = -1;

        $this->assertSame(-1, $helper->estimateVersion([[
            'mode' => \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B,
            'size' => 1,
            'data' => ['A'],
            'bstream' => [],
        ]], 0));
    }

    /**
     * The Kanji characters are read as 8 bit data when the split does not run in
     * Kanji mode.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrSplitKanjiWithoutKanjiHint(): void
    {
        $encodingMode = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_KJ, 1, 0);
        $split = new \Com\Tecnick\Barcode\Type\Square\QrCode\Split(
            $encodingMode,
            \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B,
            1,
        );

        $items = $split->getSplittedString("\x81\x40\x81\x41");
        $firstItem = $items[0] ?? null;

        $this->assertNotNull($firstItem);
        $this->assertSame(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, $firstItem['mode']);
    }

    /**
     * An empty run ends the split, a negative one is an error.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrSplitEmptyRun(): void
    {
        $encodingMode = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);
        $split = new \Test\Fixture\InternalQrSplit(
            $encodingMode,
            \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B,
            1,
        );
        $split->runs = [0];

        $this->assertSame([], $split->getSplittedString('123'));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQrSplitNegativeRun(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        $encodingMode = new InternalQrByteStream(\Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B, 1, 0);
        $split = new \Test\Fixture\InternalQrSplit(
            $encodingMode,
            \Com\Tecnick\Barcode\Type\Square\QrCode\Data::MODE_8B,
            1,
        );
        $split->runs = [-1];

        $split->getSplittedString('ABC');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testDatamatrixPaddingSizeHelper(): void
    {
        $params = \Com\Tecnick\Barcode\Type\Square\Datamatrix\Data::getPaddingSize('S', 1);
        $this->assertCount(16, $params);

        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        \Com\Tecnick\Barcode\Type\Square\Datamatrix\Data::getPaddingSize('S', PHP_INT_MAX);
    }
}
