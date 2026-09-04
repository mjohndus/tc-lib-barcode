<?php

/**
 * HibcDatamatrixTest.php
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

namespace Test\Square;

use PHPUnit\Framework\Attributes\DataProvider;
use Test\TestUtil;

/**
 * HIBC in Data Matrix Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class HibcDatamatrixTest extends TestUtil
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
            // ANSI/HIBC 2.6 section 4.3.1, Figure 3
            ['+A123BJC5D6E71',              '*+A123BJC5D6E71G*'],
            // ANSI/HIBC 2.6 Appendix C.3, Figure C2
            ['+H123ABC01234567890',         '*+H123ABC01234567890D*'],
            // ANSI/HIBC 2.6 section 4.3.3, concatenated primary and secondary
            ['+A123BJC5D6E71/$$52001510X3', '*+A123BJC5D6E71/$$52001510X3C*'],
            // ANSI/HIBC 1.3 section 2.2, Provider Applications data structure
            ['+/EO523201',                  '*+/EO5232013*'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('HIBCDM', $code)->getExtendedCode());
    }

    /**
     * The symbol is the Data Matrix representation of the data structure and
     * its check character, without the data format envelope of ISO/IEC 15434.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('extendedCodeProvider')]
    public function testSymbolMatchesDatamatrix(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            $barcode->getBarcodeObj('DATAMATRIX', \trim($expected, '*'))->getGrid(),
            $barcode->getBarcodeObj('HIBCDM', $code)->getGrid(),
        );
    }

    /**
     * ANSI/HIBC 2.6 Appendix C.3 gives an 18 by 18 matrix for this data
     * structure.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolSize(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('HIBCDM', '+H123ABC01234567890')->getArray();
        $this->assertSame(18, $data['ncols']);
        $this->assertSame(18, $data['nrows']);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['+A123BJC5D6E71',      '2fce6d2595c1ab7014c02e9c07896810'],
            ['+H123ABC01234567890', 'fd6d364347fe4c6cdb88bb19a0ad6437'],
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
        $type = $barcode->getBarcodeObj('HIBCDM', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
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
        $barcode->getBarcodeObj('HIBCDM', $code);
    }
}
