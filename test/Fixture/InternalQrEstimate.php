<?php

/**
 * InternalQrEstimate.php
 *
 * @since       2026-08-27
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
 * Exposes the QR Code bit stream size estimators.
 */
class InternalQrEstimate extends \Com\Tecnick\Barcode\Type\Square\QrCode\Estimate
{
    /**
     * Expose the bit stream size estimator.
     *
     * @param array<int, array{
     *            'mode': int,
     *            'size': int,
     *            'data': array<int, string>,
     *            'bstream': array<int, int>,
     *        }> $items Items
     */
    public function exposeEstimateBitStreamSize(array $items, int $version): int
    {
        return $this->estimateBitStreamSize($items, $version);
    }

    /**
     * Expose the alphanumeric mode bit estimator.
     */
    public function exposeEstimateBitsModeAn(int $size): int
    {
        return $this->estimateBitsModeAn($size);
    }
}
