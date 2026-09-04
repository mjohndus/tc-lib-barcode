<?php

/**
 * InternalMailmarkPostCode.php
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
 * Exposes the post code pattern blocks of Royal Mail Mailmark.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalMailmarkPostCode extends \Com\Tecnick\Barcode\Type\Linear\Mailmark\PostCode
{
    /**
     * Get the offset of the value block of a pattern.
     */
    public function exposePatternOffset(string $pattern): int
    {
        return $this->getPatternOffset($pattern);
    }

    /**
     * Get the pattern that the given field matches.
     */
    public function exposePattern(string $code): string
    {
        return $this->getPattern($code);
    }
}
