<?php

/**
 * AztecTest.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Test\Square;

use PHPUnit\Framework\TestCase;
use Test\TestUtil;

/**
 * Barcode class test
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */

class AztecTest extends TestUtil
{
    protected function getTestObject()
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    public function testInvalidInput()
    {
        $this->bcExpectException('\Com\Tecnick\Barcode\Exception');
        $testObj = $this->getTestObject();
        $testObj->getBarcodeObj('AZTEC', '');
    }

    public function testCapacityException()
    {
        $this->bcExpectException('\Com\Tecnick\Barcode\Exception');
        $testObj = $this->getTestObject();
        $code = str_pad('', 3200, 'X');
        $testObj->getBarcodeObj('AZTEC', $code);
    }

    /**
     * @dataProvider getGridDataProvider
     */
    public function testGetGrid($options, $code, $expected)
    {
        $testObj = $this->getTestObject();
        $bobj = $testObj->getBarcodeObj('AZTEC' . $options, $code);
        $grid = $bobj->getGrid();
        $this->assertEquals($expected, md5($grid));
    }

    public static function getGridDataProvider()
    {
        return array(
            array('', '0123456789', '334052ad702761015cd40336874d8408'),
            array(',200', '0123456789', '860d2cd151de3c6ca851975e86586201'),
            array(',200,binary', '0123456789', '6bcaf3679c91802268cd99c972bb054f'),
            array('', '0123456789ABC', '6c4e7e80034758c4cfe20550956a09de'),
            array(',200', '0123456789ABC', 'f54f01d9149e0656fe1bc998b1a01822'),
            array(',200,binary', '0123456789ABC', 'b3cc5389de00d407f7b54f2d570e8824'),
            array('', 'abcdefghijklmnopqrstuvwxyz01234567890123456789', 'ea8d8c08eb80f718fe333ffc5fb2f458'),
            array(',200', 'abcdefghijklmnopqrstuvwxyz01234567890123456789', '86eb21735f78ec8e8f9c28ff7923bee5'),
            array(',200,binary', 'abcdefghijklmnopqrstuvwxyz01234567890123456789', '89024db3a15780454524e1a30ae022ef'),
            array(',200',
                chr(205)
                . chr(146)
                . chr(176)
                . chr(79)
                . chr(226)
                . chr(154)
                . chr(191)
                . chr(118)
                . chr(198)
                . chr(215)
                . chr(126)
                . chr(236)
                . chr(12)
                . chr(29)
                . chr(243)
                . chr(254)
                . chr(4)
                . chr(27)
                . chr(150)
                . chr(168)
                . chr(96)
                . chr(142)
                . chr(160)
                . chr(176)
                . chr(34)
                . chr(42)
                . chr(71)
                . chr(182)
                . chr(48)
                . chr(192)
                . chr(125)
                . chr(252)
                . chr(84)
                . chr(46)
                . chr(77)
                . chr(55)
                . chr(200)
                . chr(13)
                . chr(173)
                . chr(144)
                . chr(227)
                . chr(44)
                . chr(125)
                . chr(238)
                . chr(73)
                . chr(113)
                . chr(238)
                . chr(76)
                . chr(140)
                . chr(133),
                'a884b7e6a182a7dec29e7c1548aa8a37'
            ),
        );
    }
}
