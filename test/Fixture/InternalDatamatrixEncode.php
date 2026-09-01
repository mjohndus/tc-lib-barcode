<?php

/**
 * InternalDatamatrixEncode.php
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

/**
 * Exposes the codeword buffer of the Data Matrix high level encoder.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalDatamatrixEncode extends \Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode
{
    /**
     * Take the first value out of the codeword buffer.
     *
     * @param array<int, int> $temp_cw
     */
    public function exposeShiftTempCw(array &$temp_cw): int
    {
        return $this->shiftTempCw($temp_cw);
    }
}
