<?php

/**
 * CodeThreeTwoTest.php
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
 * CODE 32 Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class CodeThreeTwoTest extends TestUtil
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
            // 8 data digits, check digit appended
            ['01234567',  '*0CSSBD*'],
            // the same code with its check digit
            ['012345676', '*0CSSBD*'],
            ['39012345',  '*CN1MY1*'],
            ['390123457', '*CN1MY1*'],
            // shorter codes are left-padded with zeros
            ['1',         '*00000D*'],
            ['000000000', '*000000*'],
            // highest encodable value
            ['999999992', '*XTPLHS*'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('C32', $code)->getExtendedCode());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['012345676', '2bf4c0f912b3c136dabb6f94a2550283'],
            ['390123457', 'c224ef772d23853dc29553348ab0de75'],
            ['1',         '377f5d8fdff2018440e6efeea686601e'],
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
        $type = $barcode->getBarcodeObj('C32', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol is the CODE 39 representation of the 6 base 32 characters,
     * without the CODE 39 check character.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolMatchesCodeThreeNine(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('C39', '0CSSBD')->getGrid(),
            $barcode->getBarcodeObj('C32', '012345676')->getGrid(),
        );
    }

    /**
     * The encoded value is always 6 characters, so every symbol has the same width:
     * 8 characters of 15 modules each, separated by 7 intercharacter gaps.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testFixedSymbolWidth(): void
    {
        $barcode = $this->getTestObject();
        foreach (['1', '012345676', '999999992'] as $code) {
            $data = $barcode->getBarcodeObj('C32', $code)->getArray();
            $this->assertSame(127, $data['ncols']);
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
            ['0123456789'],
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
        $barcode->getBarcodeObj('C32', $code);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidCheckDigit(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('C32', '999999999');
    }
}
