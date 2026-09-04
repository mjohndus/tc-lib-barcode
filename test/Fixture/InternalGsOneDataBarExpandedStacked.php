<?php

/**
 * InternalGsOneDataBarExpandedStacked.php
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
use Com\Tecnick\Barcode\Type\Linear\GsOneDataBarExpandedStacked;

/**
 * Exposes the internals of the GS1 DataBar Expanded Stacked encoder.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalGsOneDataBarExpandedStacked extends GsOneDataBarExpandedStacked
{
    /**
     * Get the binary string of the symbol, the length of the part that precedes
     * the general purpose data compaction field, and the characters that the
     * field carries.
     *
     * @return array{string, int, string}
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeBits(): array
    {
        [$prefix, $data] = $this->getPrefix();
        $bits = '';
        foreach ($this->getDataValues() as $value) {
            $bits .= \str_pad(\decbin($value), Compaction::CHARACTER_BITS, '0', STR_PAD_LEFT);
        }

        return [$bits, \strlen($prefix), $data];
    }
}
