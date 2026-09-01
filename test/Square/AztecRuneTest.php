<?php

/**
 * AztecRuneTest.php
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

use Com\Tecnick\Barcode\Type\Square\Aztec\Rune;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\TestUtil;

/**
 * Aztec Rune Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class AztecRuneTest extends TestUtil
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
    protected function getRows(string $code): array
    {
        $grid = $this->getTestObject()->getBarcodeObj('AZTECRUNE', $code)->getGridArray();

        return \array_map(static fn(array $row): string => \implode('', $row), $grid);
    }

    /**
     * The four symbols of Figure A.1 of ISO/IEC 24778, encoding 0, 25, 125 and 255.
     *
     * @return array<array{string, array<int, string>}>
     */
    public static function referenceSymbolProvider(): array
    {
        return [
            [
                '0',
                [
                    '11101010101',
                    '11111111111',
                    '01000000010',
                    '11011111011',
                    '01010001010',
                    '11010101011',
                    '01010001010',
                    '11011111011',
                    '01000000010',
                    '01111111111',
                    '00101010100',
                ],
            ],
            [
                '25',
                [
                    '11101100101',
                    '11111111111',
                    '01000000011',
                    '01011111011',
                    '01010001010',
                    '11010101011',
                    '11010001011',
                    '11011111010',
                    '11000000011',
                    '01111111111',
                    '00100100000',
                ],
            ],
            [
                '125',
                [
                    '11110101101',
                    '11111111111',
                    '11000000011',
                    '11011111011',
                    '01010001010',
                    '01010101010',
                    '01010001011',
                    '01011111011',
                    '11000000010',
                    '01111111111',
                    '00111101000',
                ],
            ],
            [
                '255',
                [
                    '11010101001',
                    '11111111111',
                    '01000000011',
                    '11011111011',
                    '11010001011',
                    '01010101011',
                    '01010001010',
                    '11011111011',
                    '11000000010',
                    '01111111111',
                    '00110011100',
                ],
            ],
        ];
    }

    /**
     * @param array<int, string> $expected
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('referenceSymbolProvider')]
    public function testReferenceSymbols(string $code, array $expected): void
    {
        $this->assertSame($expected, $this->getRows($code));
    }

    /**
     * @return array<array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            [''],
            ['256'],
            ['999'],
            ['1234'],
            ['-1'],
            ['12A'],
            ['1.5'],
            [' 12'],
            ["12\n"],
            ["\n12"],
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
        $this->getTestObject()->getBarcodeObj('AZTECRUNE', $code);
    }

    /**
     * Every value from 0 to 255 yields a distinct 11x11 symbol.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testAllValues(): void
    {
        $symbols = [];
        for ($value = 0; $value <= 255; ++$value) {
            $rows = $this->getRows((string) $value);
            $this->assertCount(11, $rows);
            foreach ($rows as $row) {
                $this->assertSame(11, \strlen($row));
            }

            $symbols[] = \implode('', $rows);
        }

        $this->assertCount(256, \array_unique($symbols));
    }

    /**
     * The fixed patterns do not depend on the encoded value.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testFixedPatterns(): void
    {
        // the outer ring carries the mode message, everything else is fixed
        $core = static fn(array $rows): array => \array_map(
            static fn(string $row): string => \substr($row, 1, -1),
            \array_slice($rows, 1, -1),
        );

        $expected = $core($this->getRows('0'));
        for ($value = 1; $value <= 255; ++$value) {
            $this->assertSame($expected, $core($this->getRows((string) $value)), 'value ' . $value);
        }
    }

    /**
     * The value is transmitted as three decimal digits with leading zeros.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testExtendedCode(): void
    {
        $this->assertSame('000', $this->getTestObject()->getBarcodeObj('AZTECRUNE', '0')->getExtendedCode());
        $this->assertSame('007', $this->getTestObject()->getBarcodeObj('AZTECRUNE', '7')->getExtendedCode());
        $this->assertSame('025', $this->getTestObject()->getBarcodeObj('AZTECRUNE', '025')->getExtendedCode());
        $this->assertSame('255', $this->getTestObject()->getBarcodeObj('AZTECRUNE', '255')->getExtendedCode());
    }

    /**
     * The rune carries one byte.
     *
     * @return array<int, array{int}>
     */
    public static function outOfRangeValueProvider(): array
    {
        return [[-1], [-256], [256], [1_000]];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('outOfRangeValueProvider')]
    public function testOutOfRangeValue(int $value): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        new Rune($value);
    }

    /**
     * The symbol is 11 modules per side.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function testGridSize(): void
    {
        $grid = (new Rune(0))->getGrid();

        $this->assertCount(11, $grid);
        foreach ($grid as $row) {
            $this->assertCount(11, $row);
        }
    }
}
