<?php

/**
 * StandardTwoOfFiveCheckTest.php
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
class StandardTwoOfFiveCheckTest extends TestUtil
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
        $type = $barcode->getBarcodeObj('S25+', '0123456789');
        $grid = $type->getGrid();
        $expected =
            '111011101010101110111010111010101011101011101010111011101110101010101011101011101110101110'
            . '10101011101110101010101011101110111010101110101011101011101011101011101010111010111'
            . "\n";
        $this->assertEquals($expected, $grid);
        $this->assertSame('01234567895', $type->getExtendedCode());
    }

    /**
     * Standard 2 of 5 encodes each digit on its own, so an odd-length code is not padded
     * and its check digit is computed on the payload as given.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('checkDigitDataProvider')]
    public function testCheckDigitMatchesEncodedData(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $extcode = $barcode->getBarcodeObj('S25+', $code)->getExtendedCode();
        $this->assertSame($expected, $extcode);

        $data = \substr($extcode, 0, -1);
        $sum = 0;
        foreach (\str_split($data) as $pos => $digit) {
            $sum += (int) $digit * (($pos % 2) === 0 ? 3 : 1);
        }

        $this->assertSame((10 - ($sum % 10)) % 10, (int) \substr($extcode, -1));
    }

    /**
     * @return array<array{string, string}>
     */
    public static function checkDigitDataProvider(): array
    {
        return [
            ['0123456789', '01234567895'],
            ['12345',      '123457'],
            ['1234',       '12342'],
            ['123',        '1236'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('S25+', '}{');
    }
}
