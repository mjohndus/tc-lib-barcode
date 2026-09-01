<?php

/**
 * PznTest.php
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
 * PZN Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class PznTest extends TestUtil
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
            // 7 data digits, check digit appended
            ['2758089',  '*-27580899*'],
            // the same code with its check digit
            ['27580899', '*-27580899*'],
            ['0375286',  '*-03752864*'],
            // shorter codes are left-padded with zeros
            ['1',        '*-00000017*'],
            ['0000000',  '*-00000000*'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('PZN', $code)->getExtendedCode());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['2758089', 'e1b7c2d37c6003912bd006decbbb53d4'],
            ['1',       '9b6adf9dddd8bc7b8d3199aaf9042af6'],
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
        $type = $barcode->getBarcodeObj('PZN', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol is the CODE 39 representation of the minus sign and the eight
     * digits, without the CODE 39 check character.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolMatchesCodeThreeNine(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('C39', '-27580899')->getGrid(),
            $barcode->getBarcodeObj('PZN', '2758089')->getGrid(),
        );
    }

    /**
     * The code is always 8 digits, so every symbol has the same width:
     * 11 characters of 15 modules each, separated by 10 intercharacter gaps.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testFixedSymbolWidth(): void
    {
        $barcode = $this->getTestObject();
        foreach (['1', '2758089', '0000000'] as $code) {
            $data = $barcode->getBarcodeObj('PZN', $code)->getArray();
            $this->assertSame(175, $data['ncols']);
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
            ['12A4567'],
            ['12345 6'],
            ['123456789'],
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
        $barcode->getBarcodeObj('PZN', $code);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidCheckDigit(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('PZN', '27580890');
    }

    /**
     * A digit sequence whose modulo 11 remainder is 10 is never allocated as a PZN.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testUnallocatedDigitSequence(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('PZN', '0000003');
    }
}
