<?php

/**
 * MicroQrEccLevelTest.php
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

use Com\Tecnick\Barcode\Type\Square\MicroQrCode\MicroQrEccLevel;

/**
 * Micro QR Code error correction level enum test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class MicroQrEccLevelTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('', MicroQrEccLevel::Auto->value);
        $this->assertSame('L', MicroQrEccLevel::L->value);
        $this->assertSame('M', MicroQrEccLevel::M->value);
        $this->assertSame('Q', MicroQrEccLevel::Q->value);
    }

    public function testFromLooseCanonical(): void
    {
        $this->assertSame(MicroQrEccLevel::L, MicroQrEccLevel::fromLoose('L'));
        $this->assertSame(MicroQrEccLevel::Q, MicroQrEccLevel::fromLoose('Q'));
    }

    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(MicroQrEccLevel::M, MicroQrEccLevel::fromLoose(MicroQrEccLevel::M));
    }

    public function testFromLooseRoundTrip(): void
    {
        foreach (MicroQrEccLevel::cases() as $case) {
            $this->assertSame($case, MicroQrEccLevel::fromLoose($case->value));
        }
    }

    public function testFromLooseUnknownFallsBackToAuto(): void
    {
        $this->assertSame(MicroQrEccLevel::Auto, MicroQrEccLevel::fromLoose('H'));
        $this->assertSame(MicroQrEccLevel::Auto, MicroQrEccLevel::fromLoose('X'));
        $this->assertSame(MicroQrEccLevel::Auto, MicroQrEccLevel::fromLoose('l'));
    }
}
