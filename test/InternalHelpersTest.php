<?php

/**
 * InternalHelpersTest.php
 *
 * @since       2026-04-19
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Test;

use Test\Fixture\InternalBarcodeType;

/**
 * Internal helper methods test
 *
 * @since       2026-04-19
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalHelpersTest extends TestUtil
{
    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testBaseTypeDefaultHooksAreCovered(): void
    {
        $type = new InternalBarcodeType(true);
        $data = $type->getArray();

        $this->assertSame([], $data['params']);
        $this->assertSame([], $data['bars']);
        $this->assertSame(4, $data['width']);
        $this->assertSame(3, $data['height']);
        $this->assertSame(['T' => 6, 'R' => 2, 'B' => 0, 'L' => 1], $data['padding']);

        $type->setBackgroundColor('');
        $this->assertNull($type->getArray()['bg_color_obj']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testDatamatrixPaddingSizeHelper(): void
    {
        $params = \Com\Tecnick\Barcode\Type\Square\Datamatrix\Data::getPaddingSize('S', 1);
        $this->assertCount(16, $params);

        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        \Com\Tecnick\Barcode\Type\Square\Datamatrix\Data::getPaddingSize('S', PHP_INT_MAX);
    }
}
