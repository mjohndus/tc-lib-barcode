<?php

/**
 * GsOneDataBarExpandedTest.php
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
use Test\Fixture\InternalGsOneDataBarExpanded;
use Test\TestUtil;

/**
 * GS1 DataBar Expanded Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarExpandedTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Get the first row of the barcode grid
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getFirstRow(string $code): string
    {
        $grid = $this->getTestObject()->getBarcodeObj('DATABAREXP', $code)->getGridArray();
        return \implode('', $grid[0] ?? []);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['(10)12A',                                  '38a560a9ba57af515f7f5ef5f60702fc'],
            ['(01)90614141000015(3202)000150',           '119a47fe080f57c8b079f558a37e6535'],
            ['(01)90012345678908(3103)001750',           'c88ea581747b78b22fbe4f5e67f3d03a'],
            ['(01)98898765432106(3202)012345(15)991231', '70ebbc23f6e259fe83cff024d5d6f31e'],
            ['(01)00012345678905(10)ABC123',             'e9fb13eb25507dab62e8e08827fdbbd8'],
            ['(10)abc',                                  'd65d0ee85fe7fe5cc3d5d2ca5774cadf'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getGridDataProvider')]
    public function testGetGrid(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('DATABAREXP', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol of Annex F.3 of ISO/IEC 24724, whose element widths are
     * 11 11521151 18411 13171121 11521232 11481 23171111 11.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testWorkedExampleSymbol(): void
    {
        $this->assertSame(
            '010100000110100000101111111100001010001000000010110101111100100111001011110000000010011101111111010101',
            $this->getFirstRow('(10)12A'),
        );
    }

    /**
     * The symbol of Figure 5-47 of the GS1 General Specifications.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbol(): void
    {
        $this->assertSame(
            '0101100011001100001011111111000010100100010000111101110011100010100010111100000011100111010111111011'
            . '010100000100000110001111110000101000000100011010010',
            $this->getFirstRow('(01)90614141000015(3202)000150'),
        );
    }

    /**
     * The width of a symbol of S symbol characters is the two guard patterns,
     * S symbol characters of 17 modules and one finder pattern of 15 modules
     * for every pair of symbol characters.
     *
     * @return array<int, array{string, int, int}>
     */
    public static function widthProvider(): array
    {
        return [
            // the general encodation method
            ['(10)12A',                                  102, 4],
            // the item and weight encodation methods
            ['(01)90012345678908(3103)001750',           151, 6],
            ['(01)90614141000015(3202)000150',           151, 6],
            // the item, weight and date encodation method
            ['(01)98898765432106(3202)012345(15)991231', 200, 8],
            // the item identification encodation method
            ['(01)90012345678908',                       134, 5],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('widthProvider')]
    public function testSymbolWidth(string $code, int $expected, int $chars): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('DATABAREXP', $code)->getArray();
        $this->assertSame($expected, $data['ncols']);
        $this->assertSame(34, $data['nrows']);
        $this->assertSame(4 + (17 * $chars) + (15 * (int) \ceil($chars / 2)), $expected);
    }

    /**
     * The symbol starts with a one module space and its elements alternate.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('widthProvider')]
    public function testGuardPatterns(string $code, int $expected, int $chars): void
    {
        $this->assertGreaterThan(0, $chars);
        $row = $this->getFirstRow($code);
        $this->assertSame($expected, \strlen($row));
        $this->assertSame('01', \substr($row, 0, 2));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetExtendedCode(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            '(01)90614141000015(3202)000150',
            $barcode->getBarcodeObj('DATABAREXP', '(01)90614141000015(3202)000150')->getExtendedCode(),
        );
    }

    /**
     * The general purpose data compaction field reads back as the characters
     * that went into it.
     *
     * @return array<int, array{string}>
     */
    public static function compactionProvider(): array
    {
        return [
            ['(10)12A'],
            ['(10)1'],
            ['(10)12'],
            ['(10)123'],
            ['(10)1234'],
            ['(10)12345'],
            ['(10)A'],
            ['(10)a'],
            ['(10)A1'],
            ['(10)1A'],
            ['(10)ABC123'],
            ['(10)abc123'],
            ['(10)A1B2C3'],
            ['(10)-./*,'],
            ['(240)abcdefghijklmnopqrstuvwxyz'],
            ['(21)12345678901234567890'],
            ['(10)ABC(21)123456'],
            ['(11)991231(10)XYZ'],
            ['(01)00012345678905(10)ABC123'],
            ['(01)09501101020917(10)AB-123'],
            ['(10)ABCDEFGHIJ1234567890abcdefghij'],
            // the ISO/IEC 646 scheme, which the lower case letters latch to
            ['(10)abcABC'],
            ['(10)abc-def'],
            ['(10)abc123456789'],
            ['(10)abcABCDEF'],
            ['(10)a!"%&\'*+,-./'],
            ['(10)a:;<=>?_'],
            // the price encodation methods, which leave the price to the field
            ['(01)90012345678908(3922)0299'],
            ['(01)90012345678908(3933)9780299'],
            ['(01)90012345678908(3922)0299(10)ABC'],
        ];
    }

    /**
     * The encodation method of section 5.5.2.3.3 of the GS1 General
     * Specifications, which the bits that follow the linkage flag identify.
     *
     * @return array<int, array{string, string}>
     */
    public static function encodationMethodProvider(): array
    {
        return [
            // general purpose data compaction only
            ['(10)12A',                                         '00'],
            // item identification
            ['(01)90012345678908',                              '1'],
            ['(01)90012345678908(3103)001750(10)ABC',           '1'],
            ['(01)90012345678908(3103)001750(11)991231(10)ABC', '1'],
            // item and weight in kilogrammes
            ['(01)90012345678908(3103)001750',                  '0100'],
            // item and weight in pounds
            ['(01)90614141000015(3202)000150',                  '0101'],
            ['(01)90012345678908(3203)001750',                  '0101'],
            // item, weight and date, the last three bits selecting the pair
            ['(01)90012345678908(3103)001750(11)991231',        '0111000'],
            ['(01)90614141000015(3202)000150(11)991231',        '0111001'],
            ['(01)90012345678908(3103)001750(13)991231',        '0111010'],
            ['(01)98898765432106(3202)012345(15)991231',        '0111101'],
            ['(01)90012345678908(3103)001750(17)991231',        '0111110'],
            // a weight that no fixed length weight method holds, with no date
            ['(01)90012345678908(3103)099999',                  '0111000'],
            // item and price
            ['(01)90012345678908(3922)0299',                    '01100'],
            // item and price with the ISO 4217 currency code
            ['(01)90012345678908(3933)9780299',                 '01101'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('encodationMethodProvider')]
    public function testEncodationMethod(string $code, string $method): void
    {
        $type = new InternalGsOneDataBarExpanded($code);
        [$bits] = $type->exposeBits();

        // the linkage flag is the first bit of the binary string
        $this->assertSame('0' . $method, \substr($bits, 0, 1 + \strlen($method)));
    }

    /**
     * The date of the item, weight and date encodation method is a calendar date.
     *
     * @return array<int, array{string}>
     */
    public static function invalidDateProvider(): array
    {
        return [
            ['(01)98898765432106(3202)012345(15)991331'],
            ['(01)98898765432106(3202)012345(15)990031'],
            ['(01)98898765432106(3202)012345(15)991232'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidDateProvider')]
    public function testInvalidDate(string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAREXP', $code);
    }

    /**
     * The symbol character values run from 0 to 4191, the sum of the values of
     * the last group of the (17,4) characters.
     *
     * @return array<int, array{int}>
     */
    public static function outOfRangeCharacterProvider(): array
    {
        return [[-1], [-4_192]];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('outOfRangeCharacterProvider')]
    public function testOutOfRangeSymbolCharacter(int $value): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $type = new InternalGsOneDataBarExpanded('(10)12A');
        $type->exposeSymbolCharacter($value);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('compactionProvider')]
    public function testCompactionRoundTrip(string $code): void
    {
        $type = new InternalGsOneDataBarExpanded($code);
        [$bits, $prefix, $data] = $type->exposeBits();
        $decoder = new GsOneDataBarExpandedDecoder();
        $this->assertSame($data, $decoder->decode(\substr($bits, $prefix)));
        $this->assertSame(0, \strlen($bits) % 12);
    }

    /**
     * The two bits that repeat the symbol length sit right after the encodation
     * method, ahead of the compressed data field, so the symbol is the prefix
     * with those two bits set and every other bit of the prefix untouched.
     *
     * @return array<int, array{string}>
     */
    public static function variableLengthProvider(): array
    {
        return [
            // the general encodation method, whose compressed field is empty
            ['(10)12A'],
            // the item identification encodation method, whose compressed field
            // carries the Global Trade Item Number
            ['(01)90012345678908'],
            ['(01)00012345678905'],
            ['(01)00012345678905(10)ABC123'],
            // the last two bits of the compressed field are not the two bits
            // that repeat the symbol length
            ['(01)00012345678806'],
            ['(01)09501101020917(10)AB-123'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('variableLengthProvider')]
    public function testSymbolLengthBits(string $code): void
    {
        $type = new InternalGsOneDataBarExpanded($code);
        [$prefix, $offset] = $type->exposePrefix();
        [$bits] = $type->exposeBits();

        $this->assertGreaterThanOrEqual(0, $offset);
        $this->assertSame('00', \substr($prefix, $offset, 2));

        $chars = \intdiv(\strlen($bits), Compaction::CHARACTER_BITS) + 1;
        $this->assertSame((string) ($chars % 2), $bits[$offset]);
        $this->assertSame($chars > 14 ? '1' : '0', $bits[$offset + 1]);

        // every other bit of the prefix survives into the symbol
        $expected = \substr_replace($prefix, \substr($bits, $offset, 2), $offset, 2);
        $this->assertSame($expected, \substr($bits, 0, \strlen($prefix)));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEmptyInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAREXP', '');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAREXP', '0110012345678905');
    }

    /**
     * The check digit of the Global Trade Item Number is not encoded, so a wrong
     * one is rejected instead of being silently corrected.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidCheckDigit(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAREXP', '(01)00012345678900');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCodeTooLong(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABAREXP', '(240)' . \str_repeat('a', 80));
    }
}
