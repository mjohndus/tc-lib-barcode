<?php

/**
 * InternalCodeOneTwoEight.php
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

use Com\Tecnick\Barcode\Type\Linear\CodeOneTwoEight;

/**
 * Exposes the internals of the CODE 128 encoder.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalCodeOneTwoEight extends CodeOneTwoEight
{
    /**
     * Get the symbol characters, from the start character to the last data
     * character, without the check character and the stop character.
     *
     * @return array<int, int>
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeCodeData(): array
    {
        return $this->getCodeData();
    }
}
