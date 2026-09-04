<?php

/**
 * CodeOneSixKTest.php
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
use Test\Fixture\InternalCodeOneSixK;
use Test\TestUtil;

/**
 * CODE 16K Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class CodeOneSixKTest extends TestUtil
{
    /**
     * Height in modules of a row and of a separator bar
     */
    private const ROW_HEIGHT = 8;

    private const SEPARATOR = 1;

    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Get the element widths of a row of the symbol, alternating bars and spaces
     * from the leading bar of the start pattern.
     *
     * @return array<int, int>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getRowElements(string $code, int $row): array
    {
        $grid = $this->getTestObject()->getBarcodeObj('C16K', $code)->getGridArray();
        $line = \implode('', $grid[($row * (self::ROW_HEIGHT + self::SEPARATOR)) + self::SEPARATOR] ?? []);
        $split = \preg_split('/(?<=0)(?=1)|(?<=1)(?=0)/', $line);

        return \array_map(\strlen(...), $split === false ? [] : $split);
    }

    /**
     * The two rows of Figure 1 of the specification, which encodes the data
     * "ab0123456789", reproduced element for element.
     *
     * @return array<int, array{int, array<int, int>}>
     */
    public static function referenceSymbolProvider(): array
    {
        return [
            [
                0,
                [
                    3,
                    2,
                    1,
                    1,
                    1,
                    1,
                    2,
                    2,
                    2,
                    1,
                    3,
                    1,
                    2,
                    1,
                    1,
                    2,
                    4,
                    1,
                    2,
                    1,
                    4,
                    2,
                    1,
                    2,
                    2,
                    2,
                    1,
                    2,
                    2,
                    3,
                    1,
                    2,
                    1,
                    3,
                    1,
                    3,
                    2,
                    1,
                    1,
                ],
            ],
            [
                1,
                [
                    2,
                    2,
                    2,
                    1,
                    1,
                    1,
                    1,
                    3,
                    1,
                    2,
                    3,
                    1,
                    4,
                    1,
                    1,
                    2,
                    2,
                    2,
                    1,
                    2,
                    1,
                    4,
                    1,
                    2,
                    2,
                    1,
                    1,
                    3,
                    2,
                    1,
                    1,
                    2,
                    1,
                    3,
                    3,
                    2,
                    2,
                    2,
                    1,
                ],
            ],
        ];
    }

    /**
     * @param array<int, int> $expected
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('referenceSymbolProvider')]
    public function testReferenceSymbol(int $row, array $expected): void
    {
        $this->assertSame($expected, $this->getRowElements('ab0123456789', $row));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['ab0123456789', '50efc4f3cc5d1d1002485fe609a6a59e'],
            ['0123456789', '0fe144fda5b537d348b34a7a3aaf7ef9'],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', '8f22f10db22ad76ed39025932121859d'],
            ['A', 'a92725cbe2935e5794d9410eab1b3c0c'],
            // a leading FNC1, which the start character implies
            ["\xF1" . 'ab0123456789', 'e839ecce460f65a7ab6a12adf5adf72f'],
            ["\xF1" . '0123456789', '68acf8f87971b34f22e151730e6b1f45'],
            ["\xF1" . 'ABC', '7a2ec4bb451ada23ab5faa2907220dc6'],
        ];
    }

    /**
     * The start character of a symbol whose data opens with FNC1 implies it, so
     * the FNC1 takes no symbol character of its own.
     *
     * @return array<int, array{string}>
     */
    public static function impliedFunctionProvider(): array
    {
        // the Code Set B and Code Set C data of the start characters 3 and 4
        return [['0123456789'], ['123456'], ['ABC'], ['abc']];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('impliedFunctionProvider')]
    public function testImpliedFunctionCharacter(string $code): void
    {
        $barcode = $this->getTestObject();
        $plain = $barcode->getBarcodeObj('C16K', $code)->getGridArray();
        $implied = $barcode->getBarcodeObj('C16K', "\xF1" . $code)->getGridArray();

        $this->assertCount(\count($plain), $implied);
        $this->assertNotSame($plain, $implied);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getGridDataProvider')]
    public function testGetGrid(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('C16K', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * @return array<int, array{string, int}>
     */
    public static function rowCountProvider(): array
    {
        return [
            // the smallest symbol is two rows
            ['A', 2],
            ['ab0123456789', 2],
            ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 6],
            [\str_repeat('A', 77), 16],
        ];
    }

    /**
     * Every symbol is 70 modules wide and its height follows from the number of
     * rows, each of eight modules between one module separator bars.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('rowCountProvider')]
    public function testSymbolDimensions(string $code, int $rows): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('C16K', $code)->getArray();
        $this->assertSame(70, $data['ncols']);
        $this->assertSame(($rows * (self::ROW_HEIGHT + self::SEPARATOR)) + self::SEPARATOR, $data['nrows']);
    }

    /**
     * Every row is 70 modules of 39 elements: the four of the start pattern, the
     * guard bar, six for each of the five symbol characters and the four of the
     * stop pattern.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testRowStructure(): void
    {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($row = 0; $row < 6; ++$row) {
            $elements = $this->getRowElements($code, $row);
            $this->assertCount(4 + 1 + (5 * 6) + 4, $elements);
            $this->assertSame(70, \array_sum($elements));
        }
    }

    /**
     * The separator bars above, between and below the rows are solid.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSeparatorBars(): void
    {
        $barcode = $this->getTestObject();
        $grid = $barcode->getBarcodeObj('C16K', 'ab0123456789')->getGridArray();
        $solid = \str_repeat('1', 70);
        foreach ([0, 9, 18] as $row) {
            $this->assertSame($solid, \implode('', $grid[$row] ?? []));
        }
    }

    /**
     * More than the 77 symbol characters of a sixteen row symbol.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCodeTooLong(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('C16K', \str_repeat('A', 78));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('C16K', "\xC3\xA8");
    }

    /**
     * The triple shift is the only symbol character that CODE 16K does not
     * share with CODE 128, and it has a pattern of its own.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolPattern(): void
    {
        $type = new InternalCodeOneSixK('A');

        $this->assertSame('211133', $type->exposeSymbolPattern(106));
        $this->assertSame('212222', $type->exposeSymbolPattern(0));
        $this->assertSame('222221', $type->exposeSymbolPattern(2));
        $this->assertSame('211232', $type->exposeSymbolPattern(105));
        $this->assertSame('', $type->exposeSymbolPattern(-1));
    }
}
