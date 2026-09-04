<?php

/**
 * LeitcodeTest.php
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
 * Deutsche Post Leitcode Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class LeitcodeTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['21348075016401', 'e0d989153cc3e7f8b2e0060dd0ee4c26'],
            ['2134807501640',  'e0d989153cc3e7f8b2e0060dd0ee4c26'],
            ['00000000000000', 'b370852211106055344e5a9636c318e5'],
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
        $type = $barcode->getBarcodeObj('LEITCODE', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The check digit weights the thirteen data digits with 4 and 9, as in the
     * worked example of the specification.
     *
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            // thirteen data digits, the check digit is appended
            ['2134807501640',  '21348075016401'],
            // fourteen digits carrying the correct check digit
            ['21348075016401', '21348075016401'],
            ['0000000000000',  '00000000000000'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('LEITCODE', $code)->getExtendedCode());
    }

    /**
     * The symbol is an Interleaved 2 of 5 symbol of the same fourteen digits.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolMatchesInterleavedTwoOfFive(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('I25', '21348075016401')->getGrid(),
            $barcode->getBarcodeObj('LEITCODE', '21348075016401')->getGrid(),
        );
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('LEITCODE', '21348075016401')->getArray();
        $this->assertSame(106, $data['ncols']);
        $this->assertSame(1, $data['nrows']);
        $this->assertCount(39, $data['bars']);
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            // not a number
            ['213480750164AB'],
            // empty
            [''],
            // too short
            ['213480750164'],
            // too long
            ['213480750164012'],
            // wrong check digit
            ['21348075016402'],
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
        $barcode->getBarcodeObj('LEITCODE', $code);
    }
}
