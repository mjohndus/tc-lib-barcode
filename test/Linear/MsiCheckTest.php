<?php

/**
 * MsiCheckTest.php
 *
 * @since       2015-02-21
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
use Test\Fixture\InternalMsiCheck;
use Test\TestUtil;

/**
 * Barcode class test
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class MsiCheckTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetGrid(): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('MSI+', '0123456789ABCDEF');
        $grid = $type->getGrid();
        $expected =
            '110100100100100100100100110100100110100100100110110100110100100100110100110100110110100'
            . '1001101101101101001001001101001001101101001101001101001101101101101001001101101001101101101101'
            . "001101101101101001001001101001\n";
        $this->assertEquals($expected, $grid);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('MSI+', 'GHI');
    }

    /**
     * The modulo 11 check digit is appended to the code.
     *
     * @return array<int, array{string, string}>
     */
    public static function checkDigitProvider(): array
    {
        return [
            ['1234',             '12343'],
            ['0',                '00'],
            ['14',               '140'],
            ['28',               '280'],
            ['9',                '94'],
            ['0123456789ABCDEF', '0123456789ABCDEF1'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('checkDigitProvider')]
    public function testCheckDigit(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();

        $this->assertSame($expected, $barcode->getBarcodeObj('MSI+', $code)->getExtendedCode());
    }

    /**
     * A code whose check digit is 10 has no single character to carry it.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCheckDigitTen(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('MSI+', '6');
    }

    /**
     * The check digit is the modulo 11 sum of the hexadecimal digits of the
     * code, weighted from 2 to 7 from the right, so any other character does
     * not contribute to it.
     *
     * @return array<int, array{string, int}>
     */
    public static function checksumProvider(): array
    {
        return [
            ['1234', 3],
            ['12-34', 3],
            ['--', 0],
            ['', 0],
            ["1\n2\n3\n4", 3],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('checksumProvider')]
    public function testChecksum(string $code, int $expected): void
    {
        $type = new InternalMsiCheck('1234');

        $this->assertSame($expected, $type->exposeChecksum($code));
    }
}
