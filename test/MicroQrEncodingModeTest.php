<?php

/**
 * MicroQrEncodingModeTest.php
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

namespace Test;

use Com\Tecnick\Barcode\Type\Square\MicroQrCode\Data;
use Com\Tecnick\Barcode\Type\Square\MicroQrCode\MicroQrEncodingMode;

/**
 * Micro QR Code encodation mode enum test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class MicroQrEncodingModeTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('', MicroQrEncodingMode::Auto->value);
        $this->assertSame('NM', MicroQrEncodingMode::NM->value);
        $this->assertSame('AN', MicroQrEncodingMode::AN->value);
        $this->assertSame('8B', MicroQrEncodingMode::Byte->value);
    }

    public function testGetMode(): void
    {
        $this->assertSame(Data::MODE_NUMERIC, MicroQrEncodingMode::NM->getMode());
        $this->assertSame(Data::MODE_ALPHANUM, MicroQrEncodingMode::AN->getMode());
        $this->assertSame(Data::MODE_BYTE, MicroQrEncodingMode::Byte->getMode());
        $this->assertSame(-1, MicroQrEncodingMode::Auto->getMode());
    }

    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(MicroQrEncodingMode::AN, MicroQrEncodingMode::fromLoose(MicroQrEncodingMode::AN));
    }

    public function testFromLooseRoundTrip(): void
    {
        foreach (MicroQrEncodingMode::cases() as $case) {
            $this->assertSame($case, MicroQrEncodingMode::fromLoose($case->value));
        }
    }

    public function testFromLooseUnknownFallsBackToAuto(): void
    {
        $this->assertSame(MicroQrEncodingMode::Auto, MicroQrEncodingMode::fromLoose('KJ'));
        $this->assertSame(MicroQrEncodingMode::Auto, MicroQrEncodingMode::fromLoose('ST'));
        $this->assertSame(MicroQrEncodingMode::Auto, MicroQrEncodingMode::fromLoose('nm'));
    }
}
