<?php

/**
 * CodeFourNineTest.php
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

use Com\Tecnick\Barcode\Type\Linear\CodeFourNine\Data;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalCodeFourNine;
use Test\TestUtil;

/**
 * CODE 49 Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class CodeFourNineTest extends TestUtil
{
    /**
     * Number of modules of a row, the start pattern, four symbol characters and the stop pattern
     */
    private const ROW_MODULES = 70;

    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Get the rows of the symbol, one string of binary digits per row of
     * symbol characters, without the separator bars.
     *
     * @return array<int, string>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getRows(string $code): array
    {
        $grid = \explode("\n", \trim($this->getTestObject()->getBarcodeObj('C49', $code)->getGrid()));
        $rows = [];
        foreach ($grid as $line => $modules) {
            if (($line % 9) !== 1) {
                continue; // a separator bar
            }

            $rows[] = $modules;
        }

        return $rows;
    }

    /**
     * The symbol of Figure 3 of ANSI/AIM BC6, encoding EXAMPLE 2.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbolExampleTwo(): void
    {
        $this->assertSame(
            [
                '1011000111011100101111001001000110110011110010100010001111000100101111',
                '1011000100110010001100010110010000100001101001111010000001001011101111',
            ],
            $this->getRows('EXAMPLE 2'),
        );
    }

    /**
     * The symbol of Figure 1 of ANSI/AIM BC6, encoding MULTIPLE ROWS IN CODE 49.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbolMultipleRows(): void
    {
        $this->assertSame(
            [
                '1011111011001011101011100110000110111101011011111010111101000100001111',
                '1010100001000010001001111000101110100110001111010010001011100011001111',
                '1011001100000101101101110111000010110010110000111011101011110001101111',
                '1010011001100100001111010010001100101011101111110011010001001111101111',
                '1011001111001011101000000101001110111110111010001011010001101111101111',
            ],
            $this->getRows('MULTIPLE ROWS IN CODE 49'),
        );
    }

    /**
     * The code and symbol characters of the worked example of section 2.3.7.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testWorkedExample(): void
    {
        $internal = new InternalCodeFourNine('EXAMPLE 2');
        [$data, $mode] = $internal->exposeCodeData();
        $this->assertSame([14, 33, 10, 22, 25, 21, 14, 38, 2], $data);
        $this->assertSame(0, $mode);
        $this->assertSame(
            [
                [719, 512, 1246, 727],
                [1864, 1729, 895, 22],
            ],
            $internal->exposeSymbolChars(),
        );
    }

    /**
     * The numeric encodation examples of section 2.2.2.
     *
     * @return array<array{string, array<int, int>}>
     */
    public static function numericProvider(): array
    {
        return [
            ['12345', [5, 17, 9]],
            ['123456', [5, 17, 9, 6]],
            ['12345678', [5, 17, 9, 14, 6]],
            ['123456789', [5, 17, 9, 46, 16, 37]],
            ['1234567', [43, 45, 2, 11, 39]],
        ];
    }

    /**
     * @param array<int, int> $expected
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('numericProvider')]
    public function testNumericEncodation(string $digits, array $expected): void
    {
        $internal = new InternalCodeFourNine('0');
        $this->assertSame($expected, $internal->exposeNumeric($digits));
    }

    /**
     * A symbol of only digits starts in the numeric encodation.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testNumericStartingMode(): void
    {
        $internal = new InternalCodeFourNine('123456789012345');
        [$data, $mode] = $internal->exposeCodeData();
        $this->assertSame(2, $mode);
        $this->assertCount(9, $data);
    }

    /**
     * The largest numeric and alphanumeric messages of Table 1.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCapacity(): void
    {
        $this->assertCount(2, $this->getRows(\str_repeat('1', 15)));
        $this->assertCount(2, $this->getRows(\str_repeat('A', 9)));
        $this->assertCount(8, $this->getRows(\str_repeat('1', 81)));
        $this->assertCount(8, $this->getRows(\str_repeat('A', 49)));
    }

    /**
     * @return array<array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            [''],
            [\str_repeat('A', 50)],
            [\str_repeat('1', 82)],
            ["\xc3\xa8"],
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
        $this->getTestObject()->getBarcodeObj('C49', $code);
    }

    /**
     * Every symbol character value has an even parity and an odd parity
     * encodation pattern of eight elements of one to six modules that sum to
     * sixteen, the bars of the even one summing to an even number of modules
     * and those of the odd one to an odd number, and no two patterns are equal.
     */
    public function testEncodationPatterns(): void
    {
        $this->assertCount(2401, Data::PATTERNS);
        $seen = [];
        foreach (Data::PATTERNS as $value => $pair) {
            $this->assertSame(16, \strlen($pair), 'value ' . $value);
            foreach ([0, 1] as $parity) {
                $pattern = \substr($pair, $parity * 8, 8);
                $seen[$pattern] = true;
                $bars = 0;
                $sum = 0;
                foreach (\str_split($pattern) as $pos => $digit) {
                    $this->assertGreaterThanOrEqual(1, (int) $digit, 'value ' . $value);
                    $this->assertLessThanOrEqual(6, (int) $digit, 'value ' . $value);
                    $sum += (int) $digit;
                    $bars += ($pos % 2) === 0 ? (int) $digit : 0;
                }

                $this->assertSame(16, $sum, 'value ' . $value);
                $this->assertSame($parity, $bars % 2, 'value ' . $value);
            }
        }

        $this->assertCount(4802, $seen);
    }

    /**
     * @return array<array{string}>
     */
    public static function structureProvider(): array
    {
        return [
            ['A'],
            ['EXAMPLE 2'],
            ['MULTIPLE ROWS IN CODE 49'],
            ['1234567890'],
            ['abcdefghijklmnopqrstuvw'],
            ["\x00\x01\x1f\x7f"],
            ['0123456789ABCDEFGHIJ-. $/+%'],
            [\str_repeat('A', 49)],
        ];
    }

    /**
     * Every row is seventy modules wide, starts with the start pattern and ends
     * with the stop pattern, and carries the parity pattern of its row number.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('structureProvider')]
    public function testRowStructure(string $code): void
    {
        $rows = $this->getRows($code);
        $this->assertGreaterThanOrEqual(2, \count($rows));
        $this->assertLessThanOrEqual(8, \count($rows));

        foreach ($rows as $row => $modules) {
            $this->assertSame(self::ROW_MODULES, \strlen($modules), 'row ' . $row);
            $this->assertSame('10', \substr($modules, 0, 2), 'row ' . $row);
            $this->assertSame('1111', \substr($modules, -4), 'row ' . $row);

            $parity = Data::ROW_PARITY[$row < (\count($rows) - 1) ? $row : \count(Data::ROW_PARITY) - 1] ?? [];
            foreach ($this->getSymbolBars(\substr($modules, 2, -4)) as $col => $bars) {
                $this->assertSame($parity[$col] ?? 0, $bars % 2, 'row ' . $row . ' column ' . $col);
            }
        }
    }

    /**
     * Get the number of bar modules of each of the four symbol characters of a row.
     *
     * @return array<int, int>
     */
    private function getSymbolBars(string $modules): array
    {
        $bars = [];
        foreach (\str_split($modules, 16) as $symbol) {
            $bars[] = \substr_count($symbol, '1');
        }

        return $bars;
    }
}
