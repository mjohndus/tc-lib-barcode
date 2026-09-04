<?php

/**
 * MailmarkTest.php
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

use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalMailmarkPostCode;
use Test\TestUtil;

/**
 * Royal Mail Mailmark 4-state barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class MailmarkTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Read the bar identifiers back off the symbol
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getBarIdentifiers(string $code): string
    {
        $grid = $this->getTestObject()->getBarcodeObj('MAILMARK', $code)->getGridArray();
        $bars = '';
        $ncols = \count($grid[1] ?? []);
        for ($col = 0; $col < $ncols; $col += 2) {
            $ascender = ($grid[0][$col] ?? '0') === '1';
            $descender = ($grid[2][$col] ?? '0') === '1';
            $bars .= match (true) {
                $ascender && $descender => 'F',
                $ascender => 'A',
                $descender => 'D',
                default => 'T',
            };
        }

        return $bars;
    }

    /**
     * The four worked examples of the encoding and decoding instructions.
     *
     * @return array<int, array{string, string}>
     */
    public static function referenceSymbolProvider(): array
    {
        return [
            // barcode L, example 1
            [
                '11000000000000000XY11     ',
                'TTDTTATDDTTATTDTAATTDTAATDDTTATTDTTDATFTAATDDTAATDDTATATFAADDAATAATDDTAADFTFTA',
            ],
            // barcode L, example 2
            [
                '41038422416563762EF61AH8T ',
                'DTTFATTDDTATTTATFTDFFFTFDFDAFTTTADTTFDTFDDDTDFDDFTFAADTFDTDTDTFAATAFDDTAATTDTT',
            ],
            // barcode C, example 1
            ['1100000000000XY11     ', 'TTDTTATTDTAATTDTAATTDTAATTDTTDDAATAADDATAATDDFAFTDDTAADDDTAAFDFAFF'],
            // barcode C, example 2
            ['21B2254800659JW5O9QA6Y', 'DAATATTTADTAATTFADDDDTTFTFDDDDFFDFDAFTADDTFFTDDATADTTFATTDAFDTFDDA'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('referenceSymbolProvider')]
    public function testReferenceSymbols(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame($expected, $barcode->getBarcodeObj('MAILMARK', $code)->getExtendedCode());
        $this->assertSame($expected, $this->getBarIdentifiers($code));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['11000000000000000XY11     ', '7980f8a75a8c9b7c26025f4739dca9dd'],
            ['41038422416563762EF61AH8T ', '783d53147cce865d871208cb54d7eb31'],
            ['1100000000000XY11     ',     'beb9d99c26ace7bcc2bf5a7e6537431c'],
            ['21B2254800659JW5O9QA6Y',     '2a9cf37679f4d0a2aa931063b6f34e21'],
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
        $type = $barcode->getBarcodeObj('MAILMARK', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * @return array<int, array{string, int}>
     */
    public static function symbolLengthProvider(): array
    {
        return [
            ['1100000000000XY11     ',     66],
            ['11000000000000000XY11     ', 78],
        ];
    }

    /**
     * The barcode C is 66 bars and the barcode L 78, each two modules apart.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('symbolLengthProvider')]
    public function testSymbolLength(string $code, int $bars): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('MAILMARK', $code)->getArray();
        $this->assertCount($bars, $data['bars']);
        $this->assertSame((2 * $bars) - 1, $data['ncols']);
        $this->assertSame(3, $data['nrows']);
    }

    /**
     * Every domestic post code pattern encodes, and each gives a distinct symbol.
     *
     * @return array<int, array{string}>
     */
    public static function postCodePatternProvider(): array
    {
        return [
            // F N F N L L N L S
            ['A1B2DE3F '],
            // F F N N L L N L S
            ['AB12DE3F '],
            // F F N N N L L N L
            ['AB123DE4F'],
            // F F N F N L L N L
            ['AB1C2DE3F'],
            // F N N L L N L S S
            ['A12BD3E  '],
            // F N N N L L N L S
            ['A123BD4E '],
            // international
            ['XY11     '],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('postCodePatternProvider')]
    public function testPostCodePatterns(string $postcode): void
    {
        $barcode = $this->getTestObject();
        $bars = $barcode->getBarcodeObj('MAILMARK', '1100000000000' . $postcode)->getExtendedCode();
        $this->assertSame(66, \strlen($bars));
        $this->assertSame(66, \strspn($bars, 'ADFT'));
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            // neither 22 nor 26 characters
            ['110000000000XY11     '],
            // empty
            [''],
            // unsupported version ID
            ['1500000000000XY11     '],
            // the format is not in its character set
            ['5100000000000XY11     '],
            // the post code matches no pattern
            ['1100000000000123456789'],
            // the item ID is not a number
            ['110000000ABCDXY11     '],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidCodeProvider')]
    public function testInvalidInput(string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('MAILMARK', $code);
    }

    /**
     * The value blocks of the destination post code plus DPS field, which
     * follow one another in the order of the patterns.
     *
     * @return array<int, array{string, int}>
     */
    public static function postCodePatternOffsetProvider(): array
    {
        return [
            ['FNFNLLNLS', 1],
            ['FFNNLLNLS', 5_408_000_001],
            ['FFNNNLLNL', 10_816_000_001],
            ['FFNFNLLNL', 64_896_000_001],
            ['FNNLLNLSS', 205_504_000_001],
            ['FNNNLLNLS', 205_712_000_001],
            // an unknown pattern falls past the last block
            ['',          207_792_000_001],
            ['NNNNNNNNN', 207_792_000_001],
        ];
    }

    #[DataProvider('postCodePatternOffsetProvider')]
    public function testPostCodePatternOffset(string $pattern, int $offset): void
    {
        $postcode = new InternalMailmarkPostCode();

        $this->assertSame($offset, $postcode->exposePatternOffset($pattern));
    }
}
