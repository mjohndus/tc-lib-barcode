<?php

/**
 * InternalGsOneDataBarOmni.php
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

use Com\Tecnick\Barcode\Type\Linear\GsOneDataBarOmni;

/**
 * Exposes the internals of the GS1 DataBar Omnidirectional encoder.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalGsOneDataBarOmni extends GsOneDataBarOmni
{
    /**
     * Get the group characteristics that contain the given data character value.
     *
     * @param array<int, array{int, int, int, int, int, int, int}> $groups Group table
     *
     * @return array{int, int, int, int, int, int, int}
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeCharacterGroup(int $value, array $groups): array
    {
        return $this->getCharacterGroup($value, $groups);
    }
}
