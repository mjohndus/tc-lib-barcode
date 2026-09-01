<?php

/**
 * SsccOneEightTest.php
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
 * SSCC-18 Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class SsccOneEightTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            // 17 data digits, check digit appended
            ['39501101020917171',  '(00)395011010209171718'],
            // the same code with its check digit
            ['395011010209171718', '(00)395011010209171718'],
            // shorter codes are left-padded with zeros
            ['1',                  '(00)000000000000000017'],
            ['00000000000000000',  '(00)000000000000000000'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('SSCC18', $code)->getExtendedCode());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['39501101020917171', 'da74b8405a0b8802a4babb802ed80e73'],
            ['1',                 'b0be8e8d04b06418fc3587ce05c2bdff'],
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
        $type = $barcode->getBarcodeObj('SSCC18', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol is the GS1-128 representation of the (00) element string.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolMatchesGsOneOneTwoEight(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('GS1128', '(00)395011010209171718')->getGrid(),
            $barcode->getBarcodeObj('SSCC18', '39501101020917171')->getGrid(),
        );
    }

    /**
     * The element string has a predefined length, so every symbol has the same width.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testFixedSymbolWidth(): void
    {
        $barcode = $this->getTestObject();
        foreach (['1', '39501101020917171', '00000000000000000'] as $code) {
            $data = $barcode->getBarcodeObj('SSCC18', $code)->getArray();
            $this->assertSame(156, $data['ncols']);
            $this->assertSame(1, $data['nrows']);
        }
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            [''],
            ['abc'],
            ['12A45678'],
            ['12345 67'],
            ['1234567890123456789'],
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
        $barcode->getBarcodeObj('SSCC18', $code);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidCheckDigit(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('SSCC18', '395011010209171719');
    }
}
