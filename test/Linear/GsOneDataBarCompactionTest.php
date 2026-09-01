<?php

/**
 * GsOneDataBarCompactionTest.php
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

use Com\Tecnick\Barcode\Type\Linear\GsOneDataBar\Compaction;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\GsOneDataBarExpandedDecoder;
use Test\Fixture\InternalGsOneDataBarCompaction;
use Test\TestUtil;

/**
 * GS1 DataBar general purpose data compaction test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarCompactionTest extends TestUtil
{
    /**
     * Smallest number of symbol characters of GS1 DataBar Expanded
     */
    private const MINIMUM = 4;

    /**
     * Encode a character sequence into a field padded to the capacity of the
     * symbol that holds it.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    private function encodeField(string $data): string
    {
        $compaction = new Compaction();
        $mode = Compaction::NUMERIC;
        $bits = $compaction->encode($data, 0, self::MINIMUM, $mode);
        $capacity = $compaction->getCapacity(\strlen($bits), self::MINIMUM);

        return $bits . $compaction->getPadding($capacity - \strlen($bits), $mode);
    }

    /**
     * Character sequences that cover the three encodation schemes, including the
     * characters that the GS1 encodable character set 82 leaves out.
     *
     * @return array<int, array{string}>
     */
    public static function dataProvider(): array
    {
        return [
            ['1'],
            ['12'],
            ['123456789012'],
            ['A'],
            ['a'],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
            ['abcdefghijklmnopqrstuvwxyz'],
            ['*,-./'],
            ['a!"%&\'()*+,-./'],
            ['a:;<=>?_ '],
            ['ABC DEF'],
            ['a1b2c3'],
            ['abc' . Compaction::FNC1 . '123'],
            ['12' . Compaction::FNC1 . 'AB' . Compaction::FNC1 . 'ab'],
            ['abcdefghij1234'],
            ['abcdefghijABCDE'],
        ];
    }

    /**
     * The field reads back as the characters that went into it.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('dataProvider')]
    public function testRoundTrip(string $data): void
    {
        $decoder = new GsOneDataBarExpandedDecoder();
        $this->assertSame($data, $decoder->decode($this->encodeField($data)));
    }

    /**
     * The field of a symbol of C symbol characters is 12 bits per character, the
     * check character left out.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('dataProvider')]
    public function testCapacity(string $data): void
    {
        $length = \strlen($this->encodeField($data));

        $this->assertSame(0, $length % Compaction::CHARACTER_BITS);
        $this->assertGreaterThanOrEqual(Compaction::CHARACTER_BITS * (self::MINIMUM - 1), $length);
    }

    /**
     * @return array<int, array{int, int, int}>
     */
    public static function capacityProvider(): array
    {
        // number of bits to hold, smallest number of symbol characters, capacity
        return [
            [0, 4, 36],
            [12, 4, 36],
            [35, 4, 36],
            [36, 4, 36],
            [37, 4, 48],
            [48, 4, 48],
            [49, 4, 60],
            [0, 6, 60],
            [96, 4, 96],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('capacityProvider')]
    public function testGetCapacity(int $length, int $minimum, int $expected): void
    {
        $compaction = new Compaction();

        $this->assertSame($expected, $compaction->getCapacity($length, $minimum));
    }

    /**
     * The symbology can count the symbol characters its own way.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function testGetCapacityWithCharacterCount(): void
    {
        $compaction = new Compaction();
        $compaction->setCharacterCount(static fn(int $_length, int $minimum): int => $minimum);

        $this->assertSame(Compaction::CHARACTER_BITS * 3, $compaction->getCapacity(1_000, 4));
    }

    /**
     * @return array<int, array{int, int, string}>
     */
    public static function paddingProvider(): array
    {
        return [
            [0, Compaction::NUMERIC, ''],
            [-5, Compaction::NUMERIC, ''],
            [4, Compaction::NUMERIC, '0000'],
            [6, Compaction::NUMERIC, '000000'],
            [9, Compaction::NUMERIC, '000000100'],
            [5, Compaction::ALPHANUMERIC, '00100'],
            [7, Compaction::ISO646, '0010000'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('paddingProvider')]
    public function testGetPadding(int $missing, int $mode, string $expected): void
    {
        $compaction = new Compaction();

        $this->assertSame($expected, $compaction->getPadding($missing, $mode));
    }

    /**
     * @return array<int, array{string}>
     */
    public static function unencodableProvider(): array
    {
        return [['#'], ['$'], ['@'], ['['], ['{'], ["\x00"], ["\xFE"]];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('unencodableProvider')]
    public function testUnencodableCharacter(string $char): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $mode = Compaction::NUMERIC;

        (new Compaction())->encode('a' . $char, 0, self::MINIMUM, $mode);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('unencodableProvider')]
    public function testUnencodableIso646Character(string $char): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        (new InternalGsOneDataBarCompaction())->exposeIso646Bits($char);
    }

    /**
     * The alphanumeric scheme holds the digits, the upper case letters and five
     * punctuation characters only.
     *
     * @return array<int, array{string}>
     */
    public static function unencodableAlphanumericProvider(): array
    {
        return [['a'], ['z'], [' '], ['!'], [':'], ['_'], ['#'], ["\x00"]];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('unencodableAlphanumericProvider')]
    public function testUnencodableAlphanumericCharacter(string $char): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        (new InternalGsOneDataBarCompaction())->exposeAlphanumericBits($char);
    }

    /**
     * The two schemes that hold the letters spend a different number of bits on
     * the same character.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function testCharacterBits(): void
    {
        $compaction = new InternalGsOneDataBarCompaction();

        $this->assertSame('01111', $compaction->exposeAlphanumericBits(Compaction::FNC1));
        $this->assertSame('01111', $compaction->exposeIso646Bits(Compaction::FNC1));
        $this->assertSame('00101', $compaction->exposeAlphanumericBits('0'));
        $this->assertSame('00101', $compaction->exposeIso646Bits('0'));
        $this->assertSame('100000', $compaction->exposeAlphanumericBits('A'));
        $this->assertSame('1000000', $compaction->exposeIso646Bits('A'));
        $this->assertSame('1011010', $compaction->exposeIso646Bits('a'));
        $this->assertSame('111100', $compaction->exposeAlphanumericBits('-'));
        $this->assertSame('11110010', $compaction->exposeIso646Bits('-'));
    }
}
