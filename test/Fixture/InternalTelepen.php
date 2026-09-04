<?php

/**
 * InternalTelepen.php
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
 * Exposes the bit stream reader of the Telepen encoder.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalTelepen extends \Com\Tecnick\Barcode\Type\Linear\Telepen
{
    /**
     * Get the bar and space pair of the block of one bits that starts at the
     * given position.
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeBlockSequence(string $bits, int &$pos): string
    {
        return $this->getBlockSequence($bits, $pos);
    }
}
