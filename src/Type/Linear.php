<?php

/**
 * Linear.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Com\Tecnick\Barcode\Type;

/**
 * Com\Tecnick\Barcode\Type\Linear
 *
 * Barcode type class
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2024 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
abstract class Linear extends \Com\Tecnick\Barcode\Type
{
    /**
     * Barcode type
     *
     * @var string
     */
    protected const TYPE = 'linear';

    /**
     * Guard Bar
     *
     * @var int
     */
    protected int $mark = 0;

    /**
     * Set extra (optional) parameter:
     *     0: MARKS -> longer guard bars for EAN,UPC... from 1 to 20
     */
    protected function setParameters(): void
    {
        parent::setParameters();
        // mark
        if (
            !isset($this->params[0])
            || ! is_numeric($this->params[0])
            || ! in_array($this->params[0], range(1, 20))
        ) {
            $this->params[0] = 0;
        }

        $this->mark = (int) $this->params[0];
    }
}
