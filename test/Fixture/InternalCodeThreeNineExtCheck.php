<?php

/**
 * InternalCodeThreeNineExtCheck.php
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
 * Exposes the check character alphabet of the extended CODE 39.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalCodeThreeNineExtCheck extends \Com\Tecnick\Barcode\Type\Linear\CodeThreeNineExtCheck
{
    /**
     * Get the value of a character of the check character alphabet.
     */
    public function exposeChecksumIndex(string $char): int
    {
        return $this->getChecksumIndex($char);
    }

    /**
     * Get the character of the check character alphabet of a value.
     */
    public function exposeChecksumChar(int $index): string
    {
        return $this->getChecksumChar($index);
    }
}
