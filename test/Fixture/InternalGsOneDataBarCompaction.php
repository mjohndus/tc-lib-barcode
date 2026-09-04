<?php

/**
 * InternalGsOneDataBarCompaction.php
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

namespace Test\Fixture;

use Com\Tecnick\Barcode\Type\Linear\GsOneDataBar\Compaction;

/**
 * Exposes the character encoders of the general purpose data compaction.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalGsOneDataBarCompaction extends Compaction
{
    /**
     * Get the bits of a character in the alphanumeric scheme.
     *
     * @throws \Com\Tecnick\Barcode\Exception if the character cannot be encoded
     */
    public function exposeAlphanumericBits(string $char): string
    {
        return $this->getAlphanumericBits($char);
    }

    /**
     * Get the bits of a character in the ISO/IEC 646 scheme.
     *
     * @throws \Com\Tecnick\Barcode\Exception if the character cannot be encoded
     */
    public function exposeIso646Bits(string $char): string
    {
        return $this->getIso646Bits($char);
    }
}
