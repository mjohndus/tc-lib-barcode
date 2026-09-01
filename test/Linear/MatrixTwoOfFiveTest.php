<?php

/**
 * MatrixTwoOfFiveTest.php
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
use Test\TestUtil;

/**
 * 2 of 5 Matrix Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class MatrixTwoOfFiveTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Start and stop are a wide bar followed by two narrow bars, and the digit
     * is three bars and two spaces of which two elements are wide.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolStructure(): void
    {
        $barcode = $this->getTestObject();
        $grid = \rtrim($barcode->getBarcodeObj('S25MATRIX', '1')->getGrid());
        // start pattern and separator, the digit 1 and its separator, stop pattern
        $this->assertSame('1110101011101011101110101', $grid);
    }

    /**
     * Each character is 9 modules wide plus the separator space, so the symbol
     * width grows by 10 modules per digit.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolWidth(): void
    {
        $barcode = $this->getTestObject();
        foreach ([1 => 25, 5 => 65, 10 => 115] as $digits => $ncols) {
            $data = $barcode->getBarcodeObj('S25MATRIX', \str_repeat('7', $digits))->getArray();
            $this->assertSame($ncols, $data['ncols']);
        }
    }

    /**
     * Every digit uses two wide and three narrow elements, so all the patterns
     * are 9 modules wide and distinct from each other.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEveryDigitHasTwoWideElements(): void
    {
        $barcode = $this->getTestObject();
        $patterns = [];
        for ($digit = 0; $digit < 10; ++$digit) {
            $grid = \rtrim($barcode->getBarcodeObj('S25MATRIX', (string) $digit)->getGrid());
            // the start pattern, its separator space, the character, its separator space and the stop pattern
            $this->assertSame(25, \strlen($grid));
            $patterns[] = \substr($grid, 8, 9);
        }

        $this->assertCount(10, \array_unique($patterns));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['0123456789', '213cbca0e69ab4c9bbf14bbe3a92e614'],
            ['1',          '76fab08a37eba1d1afbf140678fbb6db'],
            ['9876543210', '35157f2167aa08781b16c6f5a689a303'],
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
        $type = $barcode->getBarcodeObj('S25MATRIX', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            ['abc'],
            ['12A45'],
            ['12 45'],
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
        $barcode->getBarcodeObj('S25MATRIX', $code);
    }
}
