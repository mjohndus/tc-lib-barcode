<?php

/**
 * DmreSizeTest.php
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Test;

use Com\Tecnick\Barcode\Type\Square\Dmre\DmreSize;

/**
 * DmreSize enum test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class DmreSizeTest extends TestUtil
{
    public function testCaseBackingValues(): void
    {
        $this->assertSame('', DmreSize::Auto->value);
        $this->assertSame('8x48', DmreSize::Size8x48->value);
        $this->assertSame('26x64', DmreSize::Size26x64->value);
    }

    /**
     * The eighteen DMRE symbol sizes, and the automatic one.
     */
    public function testCaseCount(): void
    {
        $this->assertCount(19, DmreSize::cases());
    }

    public function testFromLooseRoundTrip(): void
    {
        foreach (DmreSize::cases() as $case) {
            $this->assertSame($case, DmreSize::fromLoose($case->value));
        }
    }

    public function testFromLoosePassesThroughEnumInstance(): void
    {
        $this->assertSame(DmreSize::Size8x48, DmreSize::fromLoose(DmreSize::Size8x48));
        $this->assertSame(DmreSize::Auto, DmreSize::fromLoose(DmreSize::Auto));
    }

    public function testFromLooseNormalizesTheValue(): void
    {
        $this->assertSame(DmreSize::Size8x48, DmreSize::fromLoose('8X48'));
        $this->assertSame(DmreSize::Size8x48, DmreSize::fromLoose(' 8x48 '));
        $this->assertSame(DmreSize::Size12x64, DmreSize::fromLoose("\t12X64\n"));
    }

    public function testFromLooseUnknownFallsBackToAuto(): void
    {
        $this->assertSame(DmreSize::Auto, DmreSize::fromLoose(''));
        $this->assertSame(DmreSize::Auto, DmreSize::fromLoose('8x50'));
        $this->assertSame(DmreSize::Auto, DmreSize::fromLoose('S'));
    }
}
