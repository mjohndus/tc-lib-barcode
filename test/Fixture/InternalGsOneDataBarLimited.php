<?php

/**
 * InternalGsOneDataBarLimited.php
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

use Com\Tecnick\Barcode\Type\Linear\GsOneDataBarLimited;

/**
 * Exposes the internals of the GS1 DataBar Limited encoder.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalGsOneDataBarLimited extends GsOneDataBarLimited
{
    /**
     * Get the element widths of the (26,7) data character of the given value.
     *
     * @return array<int, int>
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeDataCharacter(int $value): array
    {
        return $this->getDataCharacter($value);
    }

    /**
     * Get the element widths of a subset.
     *
     * @return array<int, int>
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeSubsetWidths(int $value, int $modules, int $elements, int $widest, bool $narrow): array
    {
        return $this->getSubsetWidths($value, $modules, $elements, $widest, $narrow);
    }
}
