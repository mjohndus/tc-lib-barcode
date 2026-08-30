<?php

/**
 * InternalDatamatrix.php
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

use Com\Tecnick\Barcode\Type\Square\Datamatrix\Data;
use Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode;

/**
 * Exposes the Data Matrix high level encoder and data codeword region.
 */
class InternalDatamatrix extends \Com\Tecnick\Barcode\Type\Square\Datamatrix
{
    /**
     * Expose the high level encoder so tests can assert on the codeword stream.
     *
     * @return array<int, int>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeHighLevelEncoding(string $data): array
    {
        return $this->getHighLevelEncoding($data);
    }

    /**
     * Expose the data codeword region: data codewords and padding, without error correction.
     *
     * @return array<int, int>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeDataCodewords(string $data): array
    {
        $this->dmx = new Encode($this->shape, $this->gsonemode);
        $this->cdw = $this->getHighLevelEncoding($data);
        $ncw = \count($this->cdw);
        $params = Data::getPaddingSize($this->shape, $ncw);
        $this->addPadding($params[11], $ncw);
        return $this->cdw;
    }
}
