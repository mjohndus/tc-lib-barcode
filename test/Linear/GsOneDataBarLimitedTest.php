<?php

/**
 * GsOneDataBarLimitedTest.php
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

use Com\Tecnick\Barcode\Type\Linear\GsOneDataBar\Data;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalGsOneDataBarLimited;
use Test\TestUtil;

/**
 * GS1 DataBar Limited Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarLimitedTest extends TestUtil
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
        $grid = $this->getTestObject()->getBarcodeObj('DATABARLIMITED', $code)->getGridArray();
        return \implode('', $grid[0] ?? []);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['15012345678907', '9c0cd3deec80308f037eb4489abb179b'],
            ['00098765432105', '0c1029fdded795b9bebf26db120ffba0'],
            ['1',              'fdbe063ab2227ef7586aa058d363e5f2'],
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
        $type = $barcode->getBarcodeObj('DATABARLIMITED', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol of Figure 5-44 of the GS1 General Specifications.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testReferenceSymbol(): void
    {
        $this->assertSame(
            '0100011001100011011010100111010010101101001101001001011000110111001100110100000',
            $this->getFirstRow('15012345678907'),
        );
    }

    /**
     * The symbol of Annex F.2 of ISO/IEC 24724, whose element widths are
     * 11 11112121224251 11121121122111 31311131512121 115.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testWorkedExampleSymbol(): void
    {
        $this->assertSame(
            '0101010010010011000011000001010110100101100101000100010100010000010010010100000',
            $this->getFirstRow('00098765432105'),
        );
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            ['15012345678907', '(01)15012345678907'],
            ['1501234567890',  '(01)15012345678907'],
            ['1',              '(01)00000000000017'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('extendedCodeProvider')]
    public function testGetExtendedCode(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame($expected, $barcode->getBarcodeObj('DATABARLIMITED', $code)->getExtendedCode());
    }

    /**
     * The symbol starts with a one module space and ends with a five module
     * space, and has 47 elements over 79 modules.
     *
     * @return array<int, array{string}>
     */
    public static function codeProvider(): array
    {
        return [['15012345678907'], ['00098765432105'], ['1'], ['1999999999999']];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('codeProvider')]
    public function testSymbolStructure(string $code): void
    {
        $row = $this->getFirstRow($code);
        $this->assertSame(79, \strlen($row));
        $this->assertSame('01', \substr($row, 0, 2));
        $this->assertSame('100000', \substr($row, -6));
        $this->assertSame(47, \preg_match_all('/0+|1+/', $row));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('DATABARLIMITED', '15012345678907')->getArray();
        $this->assertSame(79, $data['ncols']);
        $this->assertSame(10, $data['nrows']);
    }

    /**
     * The 89 check characters are distinct (18,7) combinations of 7 spaces of
     * 9 modules and 7 bars of 9 modules.
     */
    public function testCheckCharacterTable(): void
    {
        $this->assertCount(89, Data::LIMITED_CHECK);
        $seen = [];
        foreach (Data::LIMITED_CHECK as $widths) {
            $this->assertCount(14, $widths);
            $this->assertSame(
                9,
                \array_sum(\array_values(\array_filter(
                    $widths,
                    static fn(int $pos): bool => ($pos % 2) === 0,
                    ARRAY_FILTER_USE_KEY,
                ))),
            );
            $this->assertSame(
                9,
                \array_sum(\array_values(\array_filter(
                    $widths,
                    static fn(int $pos): bool => ($pos % 2) === 1,
                    ARRAY_FILTER_USE_KEY,
                ))),
            );
            $seen[] = \implode(',', $widths);
        }

        $this->assertSame(89, \count(\array_unique($seen)));
    }

    /**
     * Only the Indicator digits 0 and 1 are encodable.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testIndicatorDigitTooHigh(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABARLIMITED', '25012345678904');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABARLIMITED', 'GHI');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCodeTooLong(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABARLIMITED', '150123456789071');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidCheckDigit(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATABARLIMITED', '15012345678901');
    }

    /**
     * The data character values run from 0 to 2,014,977, the sum of the values
     * of the last group of the (26,7) characters.
     *
     * @return array<int, array{int}>
     */
    public static function outOfRangeCharacterProvider(): array
    {
        return [[-1], [-2_014_978]];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('outOfRangeCharacterProvider')]
    public function testOutOfRangeDataCharacter(int $value): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $type = new InternalGsOneDataBarLimited('15012345678907');
        $type->exposeDataCharacter($value);
    }

    /**
     * A subset of E elements over M modules, none of them wider than W, holds
     * as many width sequences as the combinations of the modules left over the
     * elements, so a value above them all has no sequence.
     *
     * @return array<int, array{int, int, int, int, bool}>
     */
    public static function outOfRangeSubsetProvider(): array
    {
        return [
            // value, modules, elements, widest, narrow
            [1,          4,  4, 1, false],
            [1_000_000,  12, 7, 3, false],
            [1_000_000,  26, 7, 9, true],
            [10_000_000, 26, 7, 9, false],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('outOfRangeSubsetProvider')]
    public function testOutOfRangeSubsetValue(int $value, int $modules, int $elements, int $widest, bool $narrow): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $type = new InternalGsOneDataBarLimited('15012345678907');
        $type->exposeSubsetWidths($value, $modules, $elements, $widest, $narrow);
    }

    /**
     * The subset value 0 is the width sequence with the narrowest leading
     * elements, and the elements add up to the modules of the subset.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSubsetWidths(): void
    {
        $type = new InternalGsOneDataBarLimited('15012345678907');

        $this->assertSame([1, 1, 1, 1, 1, 1, 20], $type->exposeSubsetWidths(0, 26, 7, 20, true));
        $this->assertSame([1, 1, 1, 1], $type->exposeSubsetWidths(0, 4, 4, 1, true));
    }
}
