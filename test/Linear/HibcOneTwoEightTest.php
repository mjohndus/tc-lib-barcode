<?php

/**
 * HibcOneTwoEightTest.php
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
use Test\Fixture\InternalHibc;
use Test\TestUtil;

/**
 * HIBC in CODE 128 Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class HibcOneTwoEightTest extends TestUtil
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
            // ANSI/HIBC 2.6 Appendix B.2.0 and section 4.3.1, Figure 1
            ['+A123BJC5D6E71',          '*+A123BJC5D6E71G*'],
            // ANSI/HIBC 2.6 section 4.3.2, Figure 5
            ['+$$52001510X3G',          '*+$$52001510X3GD*'],
            // ANSI/HIBC 2.6 section 2.2.1.1, concatenated primary and secondary
            ['+A99912345/$$52001510X3', '*+A99912345/$$52001510X33*'],
            // ANSI/HIBC 1.3 section 2.2, Provider Applications data structure
            ['+/EO523201',              '*+/EO5232013*'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('HIBC128', $code)->getExtendedCode());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['+A123BJC5D6E71',          '468d52efd271d4121d5aa06046cae602'],
            ['+A99912345/$$52001510X3', '3c0a282132e1827c74a4e4dce9b9bf6f'],
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
        $type = $barcode->getBarcodeObj('HIBC128', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol is the CODE 128 representation of the data structure and its
     * check character. The asterisk bounds the human readable interpretation
     * only and is never encoded.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('extendedCodeProvider')]
    public function testSymbolMatchesCodeOneTwoEight(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('C128', \trim($expected, '*'))->getGrid(),
            $barcode->getBarcodeObj('HIBC128', $code)->getGrid(),
        );
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            [''],
            ['A123BJC5D6E71'],
            ['+a123bjc5d6e71'],
            ['+A123BJC5D6E7A'],
            ['+/A1MRN123456'],
            // a Provider Applications data field is alphanumeric
            ['+/AC%ME'],
            ['+/EACME 123'],
            ['+/ACME/12-34'],
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
        $barcode->getBarcodeObj('HIBC128', $code);
    }

    /**
     * The Labeler Identification Code of a primary data structure starts with a
     * letter and ends with the Unit of Measure Identifier, which is a digit.
     *
     * @return array<int, array{string}>
     */
    public static function invalidPrimaryDataProvider(): array
    {
        return [
            // the first character is not a letter
            ['1123BJC5D6E71'],
            ['9999999999999'],
            // the last character is not a digit
            ['A123BJC5D6E7A'],
            // the data structure is not alphanumeric
            ['A123BJC5D6E7$'],
            // the data structure is too short
            ['A123'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidPrimaryDataProvider')]
    public function testInvalidPrimaryData(string $data): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        (new InternalHibc())->exposeValidatePrimaryData($data);
    }
}
