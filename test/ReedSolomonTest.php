<?php

/**
 * ReedSolomonTest.php
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

namespace Test;

use Com\Tecnick\Barcode\Type\ReedSolomon;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Reed-Solomon error correction test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class ReedSolomonTest extends TestUtil
{
    /**
     * The worked examples of Annex I of ISO/IEC 18004.
     *
     * @return array<int, array{array<int, int>, int, array<int, int>}>
     */
    public static function qrCodeProvider(): array
    {
        return [
            // the version 1-M symbol of Annex I.2
            [
                [0x10, 0x20, 0x0C, 0x56, 0x61, 0x80, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11, 0xEC, 0x11],
                10,
                [0xA5, 0x24, 0xD4, 0xC1, 0xED, 0x36, 0xC7, 0x87, 0x2C, 0x55],
            ],
            // the version M2-L symbol of Annex I.3
            [
                [0x40, 0x18, 0xAC, 0xC3, 0x00],
                5,
                [0x86, 0x0D, 0x22, 0xAE, 0x30],
            ],
        ];
    }

    /**
     * @param array<int, int> $data
     * @param array<int, int> $expected
     */
    #[DataProvider('qrCodeProvider')]
    public function testQrCodeCheckwords(array $data, int $necc, array $expected): void
    {
        // QR Code counts the roots of the generator polynomial from zero
        $reedSolomon = new ReedSolomon(8, ReedSolomon::GF_QRCODE, 0);

        $this->assertSame($expected, $reedSolomon->checkwords($data, $necc));
    }

    /**
     * The word sizes of the Galois fields of the symbologies of the library.
     *
     * @return array<int, array{int, int}>
     */
    public static function wordSizeProvider(): array
    {
        return [[4, 15], [6, 63], [8, 255], [10, 1_023], [12, 4_095]];
    }

    /**
     * Every codeword is inside the field, and the codewords of the same data are
     * always the same.
     */
    #[DataProvider('wordSizeProvider')]
    public function testWordSizes(int $wsize, int $largest): void
    {
        $reedSolomon = new ReedSolomon($wsize);
        $checkwords = $reedSolomon->checkwords([1, 2, 3, 4, 5], 6);

        $this->assertCount(6, $checkwords);
        foreach ($checkwords as $checkword) {
            $this->assertGreaterThanOrEqual(0, $checkword);
            $this->assertLessThanOrEqual($largest, $checkword);
        }

        $this->assertSame($checkwords, $reedSolomon->checkwords([1, 2, 3, 4, 5], 6));
    }

    /**
     * The data of a symbol that carries no error correction is left alone.
     */
    public function testNoCheckwords(): void
    {
        $reedSolomon = new ReedSolomon(8);

        $this->assertSame([], $reedSolomon->checkwords([1, 2, 3], 0));
    }

    /**
     * A word size with no Galois field yields codewords of zero.
     *
     * @return array<int, array{int}>
     */
    public static function unsupportedWordSizeProvider(): array
    {
        return [[0], [3], [7], [9], [16]];
    }

    #[DataProvider('unsupportedWordSizeProvider')]
    public function testUnsupportedWordSize(int $wsize): void
    {
        $reedSolomon = new ReedSolomon($wsize);

        $this->assertSame([0, 0, 0, 0], $reedSolomon->checkwords([1, 2, 3], 4));
    }
}
