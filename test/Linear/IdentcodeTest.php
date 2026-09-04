<?php

/**
 * IdentcodeTest.php
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
 * Deutsche Post Identcode Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class IdentcodeTest extends TestUtil
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
            ['563102430313', 'a41a0d95bc15101e68eb6fa45c399c56'],
            ['56310243031',  'a41a0d95bc15101e68eb6fa45c399c56'],
            ['000000000000', '2423c385e33e35e43f0533813fee80eb'],
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
        $type = $barcode->getBarcodeObj('IDENTCODE', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The check digit weights the eleven data digits with 4 and 9, as in the
     * worked example of the specification.
     *
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            // eleven data digits, the check digit is appended
            ['56310243031',  '563102430313'],
            // twelve digits carrying the correct check digit
            ['563102430313', '563102430313'],
            ['00000000000',  '000000000000'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('IDENTCODE', $code)->getExtendedCode());
    }

    /**
     * The symbol is an Interleaved 2 of 5 symbol of the same twelve digits.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolMatchesInterleavedTwoOfFive(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('I25', '563102430313')->getGrid(),
            $barcode->getBarcodeObj('IDENTCODE', '563102430313')->getGrid(),
        );
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('IDENTCODE', '563102430313')->getArray();
        $this->assertSame(92, $data['ncols']);
        $this->assertSame(1, $data['nrows']);
        $this->assertCount(34, $data['bars']);
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            // not a number
            ['5631024303AB'],
            // empty
            [''],
            // too short
            ['5631024303'],
            // too long
            ['5631024303131'],
            // wrong check digit
            ['563102430311'],
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
        $barcode->getBarcodeObj('IDENTCODE', $code);
    }
}
