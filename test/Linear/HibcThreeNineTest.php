<?php

/**
 * HibcThreeNineTest.php
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
 * HIBC in CODE 39 Barcode class test
 *
 * The data structure layer is shared by every HIBC carrier, so it is covered
 * here and the other carriers only check that they encode what it produces.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class HibcThreeNineTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * The worked examples of ANSI/HIBC 2.6 and ANSI/HIBC 1.3.
     *
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            // ANSI/HIBC 2.6 Appendix B.2.0, primary data structure
            ['+A123BJC5D6E71',                                '*+A123BJC5D6E71G*'],
            // ANSI/HIBC 2.6 Appendix C.3, primary data structure
            ['+H123ABC01234567890',                           '*+H123ABC01234567890D*'],
            // ANSI/HIBC 2.6 Appendix E1.1, lot number without a date field
            ['+$A1234G',                                      '*+$A1234GU*'],
            // ANSI/HIBC 2.6 section 4.3.2, secondary data structure
            ['+$$52001510X3G',                                '*+$$52001510X3GD*'],
            // ANSI/HIBC 2.6 section 2.2.1.1, concatenated primary and secondary
            ['+A99912345/$$52001510X3',                       '*+A99912345/$$52001510X33*'],
            // ANSI/HIBC 2.6 section 4.3.3, concatenated primary and secondary
            ['+A123BJC5D6E71/$$52001510X3',                   '*+A123BJC5D6E71/$$52001510X3C*'],
            // ANSI/HIBC 2.6 section 2.3.2, production date and serial number
            ['+A99912345/$$52001510X3/16D20111212/S77DEFG45', '*+A99912345/$$52001510X3/16D20111212/S77DEFG457*'],
            // ANSI/HIBC 2.6 section 2.3.2.3, production date and expiry date
            ['+A99912345/$10X3/16D20111231/14D20200131',      '*+A99912345/$10X3/16D20111231/14D202001313*'],
            // ANSI/HIBC 2.6 section 2.3.2.4, quantity as the last supplemental field
            ['+A99912349/$10X3/16D20111231/14D20200131/Q500', '*+A99912349/$10X3/16D20111231/14D20200131/Q500Z*'],
            // ANSI/HIBC 1.3 section 2.2, single Provider Applications data structure
            ['+/EO523201',                                    '*+/EO5232013*'],
            // ANSI/HIBC 1.3 Appendix B, single Provider Applications data structure
            ['+/EAH783',                                      '*+/EAH783B*'],
            // ANSI/HIBC 1.3 section 2.6, single Provider Applications data structure
            ['+/KN12345',                                     '*+/KN12345A*'],
            // ANSI/HIBC 1.3 section 2.1, concatenated Provider Applications fields
            ['+/ACMRN123456/V200912190833',                   '*+/ACMRN123456/V2009121908334*'],
            // ANSI/HIBC 1.3 section 2.3, concatenated Provider Applications fields
            ['+/EU720060FF0/O523201',                         '*+/EU720060FF0/O523201W*'],
            // ANSI/HIBC 1.3 section 2.4, four concatenated Provider Applications fields
            ['+/EU720060FF0/O523201/Z34H159/M9842431340',     '*+/EU720060FF0/O523201/Z34H159/M9842431340V*'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('HIBC39', $code)->getExtendedCode());
    }

    /**
     * The secondary data formats of ANSI/HIBC 2.6 Appendix F, Table F1, each
     * with the example data of the table and "L" as the Link Character.
     *
     * @return array<int, array{string}>
     */
    public static function secondaryFormatProvider(): array
    {
        return [
            ['+05271L'], // five digit Julian date of the superseded format
            ['+$3C001L'], // lot number, no date field
            ['+$$09053C001L'], // MMYY
            ['+$$20928053C001L'], // MMDDYY
            ['+$$30509283C001L'], // YYMMDD
            ['+$$4050928223C001L'], // YYMMDDHH
            ['+$$5052713C001L'], // YYJJJ
            ['+$$605271223C001L'], // YYJJJHH
            ['+$$73C001L'], // no date field
            ['+$+0001L'], // serial number of the superseded format
            ['+$$+09050001L'], // MMYY
            ['+$$+20928050001L'], // MMDDYY
            ['+$$+30509280001L'], // YYMMDD
            ['+$$+4050928200001L'], // YYMMDDHH
            ['+$$+5052710001L'], // YYJJJ
            ['+$$+605271200001L'], // YYJJJHH
            ['+$$+70001L'], // no date field
        ];
    }

    /**
     * Every secondary data format is accepted and carries its check character.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('secondaryFormatProvider')]
    public function testSecondaryDataFormats(string $code): void
    {
        $barcode = $this->getTestObject();
        $extcode = $barcode->getBarcodeObj('HIBC39', $code)->getExtendedCode();
        $this->assertSame('*' . $code, \substr($extcode, 0, \strlen($code) + 1));
        $this->assertSame(\strlen($code) + 3, \strlen($extcode));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['+A123BJC5D6E71',          'f33b9f04d6213968706e7825febadb9b'],
            ['+A99912345/$$52001510X3', 'e70ccc8a2c02c14503526b1ffcc5ccfd'],
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
        $type = $barcode->getBarcodeObj('HIBC39', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol is the CODE 39 representation of the data structure and its
     * check character, without a CODE 39 check character of its own.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolMatchesCodeThreeNine(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('C39', '+A123BJC5D6E71G')->getGrid(),
            $barcode->getBarcodeObj('HIBC39', '+A123BJC5D6E71')->getGrid(),
        );
    }

    /**
     * The asterisk bounds the human readable interpretation and is the CODE 39
     * start and stop character, so the symbol holds two characters more than
     * the data structure and its check character.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolWidth(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('HIBC39', '+A123BJC5D6E71')->getArray();
        // 17 characters of 15 modules each, separated by 16 intercharacter gaps
        $this->assertSame(271, $data['ncols']);
        $this->assertSame(1, $data['nrows']);
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            [''],
            // the flag character is missing
            ['A123BJC5D6E71'],
            // the flag character is not followed by "/", a letter, a digit or "$"
            ['+%A123BJC5D6E71'],
            ['+'],
            // outside the HIBC character set
            ['+a123bjc5d6e71'],
            ['+A123BJC5D6E7*1'],
            ['+A123BJC5#D6E71'],
            // the primary data structure is too short or too long
            ['+A1231'],
            ['+A123BJC5D6E7123456789012'],
            // the Unit of Measure Identifier is not a digit
            ['+A123BJC5D6E7A'],
            // special characters in the primary data structure
            ['+A123BJ.C5D6E71'],
            ['+A123BJ-C5D6E71'],
            // the date format indicator is out of range
            ['+$$8052713C001L'],
            ['+$$+9052713C001L'],
            // the date field is not a number
            ['+$$5ABCDE3C001L'],
            // the date field is truncated
            ['+$$50527'],
            // the five digit Julian date of the superseded format is truncated
            ['+0527'],
            // the Lot/Batch field is longer than 18 characters
            ['+$$505271ABCDEFGHIJKLMNOPQRST'],
            // a concatenated structure carries no Link Character, so the whole
            // Lot/Batch field counts against the 18 character limit and every
            // character of it belongs to the Lot/Batch character set
            ['+A123BJC5D6E71/$$3231031ABCDEFGHIJKLMNOPQRS'],
            ['+A123BJC5D6E71/$$3231031ABC%'],
            // the secondary data structure is empty
            ['+A99912345/'],
            // unknown supplemental data identifier
            ['+A99912345/$10X3/17D20111231'],
            // the production date is not eight digits
            ['+A99912345/$10X3/16D2011123'],
            // the quantity is not the last supplemental data field
            ['+A99912345/$10X3/Q500/S123'],
            // the Provider Applications data structure has no data field
            ['+/'],
            ['+/A'],
            ['+/AC'],
            // the Provider Applications data field is longer than 15 characters
            ['+/AC1234567890123456'],
            // the Provider Applications flag character is not alphabetic
            ['+/A1MRN123456'],
            // a three character flag character is truncated
            ['+/AY1MRN123456'],
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
        $barcode->getBarcodeObj('HIBC39', $code);
    }
}
