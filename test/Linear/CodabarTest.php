<?php

/**
 * CodabarTest.php
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
class CodabarTest extends TestUtil
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
        $type = $barcode->getBarcodeObj('CODABAR', '0123456789');
        $grid = $type->getGrid();
        $expected =
            '10110010010101010011010101100101010010110110010101010110'
            . "10010110101001010010101101001011010100110101011010010101011001001\n";
        $this->assertEquals($expected, $grid);
    }

    /**
     * Like ':', '/' and '.', '+' is three wide bars and no wide space, here bars 2, 3 and 4.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testPlusPattern(): void
    {
        $barcode = $this->getTestObject();
        $grid = $barcode->getBarcodeObj('CODABAR', '+')->getGrid();
        $this->assertEquals("10110010010101101101101011001001\n", $grid);
    }

    /**
     * Every Codabar character is seven elements with two or three wide ones, so it is
     * 9 or 10 modules wide. '0' is the 9-module reference.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEveryCharacterKeepsTheStandardWidth(): void
    {
        $barcode = $this->getTestObject();
        $reference = \strlen(\trim($barcode->getBarcodeObj('CODABAR', '0')->getGrid()));
        foreach (\str_split('0123456789-$:/.+') as $char) {
            $grid = \trim($barcode->getBarcodeObj('CODABAR', $char)->getGrid());
            $width = 9 + \strlen($grid) - $reference;
            $this->assertGreaterThanOrEqual(9, $width, 'character ' . $char);
            $this->assertLessThanOrEqual(10, $width, 'character ' . $char);
        }
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('CODABAR', \chr(218));
    }
}
