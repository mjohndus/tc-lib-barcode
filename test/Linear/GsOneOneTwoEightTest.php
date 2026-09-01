<?php

/**
 * GsOneOneTwoEightTest.php
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
 * GS1-128 Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneOneTwoEightTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * The extended code is the bracketed human readable interpretation.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetExtendedCode(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            '(01)09501101020917(10)AB-123',
            $barcode->getBarcodeObj('GS1128', '(01)09501101020917(10)AB-123')->getExtendedCode(),
        );
    }

    /**
     * The CODE 128 payload, with FNC1 as the first symbol character and as the
     * separator that follows a variable length element string.
     *
     * @return array<int, array{string, string}>
     */
    public static function payloadProvider(): array
    {
        $fnc1 = "\xF1";
        return [
            // a single element string of predefined length
            ['(00)395011010209171719', $fnc1 . '00395011010209171719'],
            // a predefined length element string is not followed by a separator
            ['(01)09501101020917(10)AB-123', $fnc1 . '010950110102091710AB-123'],
            // two variable length element strings are separated by FNC1
            ['(10)ABC(21)XYZ', $fnc1 . '10ABC' . $fnc1 . '21XYZ'],
            // the last element string is never followed by a separator
            ['(10)ABC(17)260901', $fnc1 . '10ABC' . $fnc1 . '17260901'],
            // a four digit Application Identifier of predefined length
            ['(3103)000189(01)09501101020917', $fnc1 . '31030001890109501101020917'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('payloadProvider')]
    public function testPayloadMatchesCodeOneTwoEight(string $code, string $payload): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('C128', $payload)->getGrid(),
            $barcode->getBarcodeObj('GS1128', $code)->getGrid(),
        );
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['(00)395011010209171719',       '0bffc51c1a3db4ebdae9d16c46c4ec26'],
            ['(01)09501101020917(10)AB-123', '3bfcdbdd1a1dde2114b5447935f9c36e'],
            ['(10)ABC(21)XYZ',               '5890b4e269ce85dbacb772e92258e3c0'],
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
        $type = $barcode->getBarcodeObj('GS1128', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            // empty payload
            [''],
            // the code must start with a bracketed Application Identifier
            ['0100'],
            ['01)0950110102091712'],
            // the closing bracket is missing
            ['(01'],
            ['(10)AB(CD'],
            // the Application Identifier is not 2 to 4 digits
            ['(1)12'],
            ['(12345)12'],
            ['(1A)12'],
            // an empty data field
            ['(10)'],
            ['(01)09501101020917(10)'],
            // a predefined length element string of the wrong length
            ['(01)0950110102091'],
            ['(01)095011010209171'],
            ['(00)39501101020917171'],
            ['(20)1'],
            // a predefined length data field must be numeric
            ['(01)0950110102091A'],
            // characters outside the GS1 encodable character set 82
            ['(10)AB CD'],
            ['(10)AB#CD'],
            ['(10)AB$CD'],
            // parentheses are reserved as delimiters
            ['(10)AB)CD'],
            // more than 48 data characters
            ['(10)0123456789012345678901234567890123456789012345678'],
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
        $barcode->getBarcodeObj('GS1128', $code);
    }

    /**
     * A symbol of exactly 48 data characters is accepted.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testMaximumDataLength(): void
    {
        $barcode = $this->getTestObject();
        $code = '(10)' . \str_repeat('A', 46);
        $this->assertSame($code, $barcode->getBarcodeObj('GS1128', $code)->getExtendedCode());
    }
}
