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
     * Version that the search of the smallest one returns, when set.
     */
    public ?int $forcedMinVer = null;

    /**
     * Expose the encoding mode values.
     */
    public function exposeEncModeValue(string $mode): int
    {
        return $this->getEncModeValue($mode);
    }

    /**
     * Expose the number of data codewords of a version.
     */
    public function exposeCapacityWordsValue(int $version): int
    {
        return $this->getCapacityWordsValue($version);
    }

    /**
     * Expose the number of error correction codewords of a version and level.
     */
    public function exposeCapacityEcValue(int $version, int $level): int
    {
        return $this->getCapacityEcValue($version, $level);
    }

    protected function getMinimumVersion(int $size, int $level): int
    {
        return $this->forcedMinVer ?? parent::getMinimumVersion($size, $level);
    }

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
