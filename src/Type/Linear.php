<?php

/**
 * Linear.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2023 Nicola Asuni - Tecnick.com LTD
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
 * @copyright   2010-2023 Nicola Asuni - Tecnick.com LTD
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
    protected $type = 'linear';

    /**
     * Guard Bar
     *
     * @var int
     */
    protected $mark = 0;

    /**
     * Set extra (optional) parameter:
     *     0: MARKS -> longer guard bars for EAN,UPC... from 1 to 20
     */
    protected function setParameters()
    {
        parent::setParameters();
        // mark
        if (!isset($this->params[0]) or $this->params[0] < 1 or $this->params[0] > 20) {
            $this->params[0] = 0;
        }
        $this->mark = intval($this->params[0]);
    }
}
