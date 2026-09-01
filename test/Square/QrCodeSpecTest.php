<?php

/**
 * QrCodeSpecTest.php
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

use Com\Tecnick\Barcode\Type\Square\QrCode\Spec;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\TestUtil;

/**
 * QR Code specification tables test
 *
 * @since       2026-09-01
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
     * The BCH (15,5) format information of Table C.1 of ISO/IEC 18004.
     *
     * @return array<int, array{int, int, int}>
     */
    public static function formatInfoProvider(): array
    {
        return [
            // mask, level, format information
            [0, 0, 0b111011111000100],
            [1, 0, 0b111001011110011],
            [7, 0, 0b110100101110110],
            [0, 1, 0b101010000010010],
            [3, 1, 0b101101101001011],
            [0, 2, 0b011010101011111],
            [3, 2, 0b011101000000110],
            [0, 3, 0b001011010001001],
            [7, 3, 0b000100000111011],
        ];
    }

    #[DataProvider('formatInfoProvider')]
    public function testGetFormatInfo(int $maskNo, int $level, int $expected): void
    {
        $spec = new Spec();

        $this->assertSame($expected, $spec->getFormatInfo($maskNo, $level));
    }

    /**
     * The format information is defined for the eight masks and the four error
     * correction levels only.
     *
     * @return array<int, array{int, int}>
     */
    public static function invalidFormatInfoProvider(): array
    {
        return [[-1, 0], [8, 0], [0, -1], [0, 4], [-1, -1], [8, 4]];
    }

    #[DataProvider('invalidFormatInfoProvider')]
    public function testGetFormatInfoOutOfRange(int $maskNo, int $level): void
    {
        $spec = new Spec();

        $this->assertSame(0, $spec->getFormatInfo($maskNo, $level));
    }

    /**
     * The BCH (18,6) version information of Table D.1 of ISO/IEC 18004, which
     * the symbols of version 7 and above carry.
     *
     * @return array<int, array{int, int}>
     */
    public static function versionPatternProvider(): array
    {
        return [
            [7,  0b000111110010010100],
            [8,  0b001000010110111100],
            [21, 0b010101011010000011],
            [40, 0b101000110001101001],
        ];
    }

    #[DataProvider('versionPatternProvider')]
    public function testGetVersionPattern(int $version, int $expected): void
    {
        $spec = new Spec();

        $this->assertSame($expected, $spec->getVersionPattern($version));
    }

    /**
     * @return array<int, array{int}>
     */
    public static function invalidVersionProvider(): array
    {
        return [[-1], [0], [1], [6], [41], [100]];
    }

    #[DataProvider('invalidVersionProvider')]
    public function testGetVersionPatternOutOfRange(int $version): void
    {
        $spec = new Spec();

        $this->assertSame(0, $spec->getVersionPattern($version));
    }

    /**
     * The error correction blocks of Table 9 of ISO/IEC 18004.
     *
     * @return array<int, array{int, int, array<int, int>}>
     */
    public static function eccSpecProvider(): array
    {
        return [
            // version, level, blocks of type 1, data codewords, ecc codewords,
            // blocks of type 2, data codewords
            [1, 0, [1, 19, 7, 0, 0]],
            [1, 3, [1, 9, 17, 0, 0]],
            [5, 2, [2, 15, 18, 2, 16]],
            [40, 0, [19, 118, 30, 6, 119]],
        ];
    }

    /**
     * @param array<int, int> $expected
     */
    #[DataProvider('eccSpecProvider')]
    public function testGetEccSpec(int $version, int $level, array $expected): void
    {
        $spec = new Spec();

        $this->assertSame($expected, $spec->getEccSpec($version, $level, [0, 0, 0, 0, 0]));
    }

    /**
     * An array with fewer than the five entries of the specification is filled
     * in before it is used.
     */
    public function testGetEccSpecFromShortArray(): void
    {
        $spec = new Spec();
        /** @var array{0: int, 1: int, 2: int, 3: int, 4: int} $short */
        $short = \array_fill(0, 2, 0);

        $this->assertSame($spec->getEccSpec(1, 0, [0, 0, 0, 0, 0]), $spec->getEccSpec(1, 0, $short));
        $this->assertSame($spec->getEccSpec(5, 2, [0, 0, 0, 0, 0]), $spec->getEccSpec(5, 2, $short));
    }
}
