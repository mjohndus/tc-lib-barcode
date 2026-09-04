<?php

/**
 * GsOneOneFourTest.php
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
 * GS1-14 Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneOneFourTest extends TestUtil
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
            // 13 data digits, check digit appended
            ['9501101020917',  '(01)95011010209176'],
            // the same code with its check digit
            ['95011010209176', '(01)95011010209176'],
            // shorter codes are left-padded with zeros
            ['1',              '(01)00000000000017'],
            ['0931234567890',  '(01)09312345678907'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('GS114', $code)->getExtendedCode());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['9501101020917', 'cdb5dbb340eb410a256a4bb190cb4a1c'],
            ['1',             '791d37daba3058a935765fd2fbcdbc21'],
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
        $type = $barcode->getBarcodeObj('GS114', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The GTIN-14 check digit is the same as the one of the equivalent ITF-14 symbol.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCheckDigitMatchesItfOneFour(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            '(01)' . $barcode->getBarcodeObj('ITF14', '0931234567890')->getExtendedCode(),
            $barcode->getBarcodeObj('GS114', '0931234567890')->getExtendedCode(),
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
        foreach (['1', '9501101020917', '0000000000000'] as $code) {
            $data = $barcode->getBarcodeObj('GS114', $code)->getArray();
            $this->assertSame(134, $data['ncols']);
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
            ['123456789012345'],
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
        $barcode->getBarcodeObj('GS114', $code);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidCheckDigit(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('GS114', '95011010209177');
    }
}
