<?php

/**
 * InternalDmre.php
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

use Com\Tecnick\Barcode\Type\Square\Datamatrix\Data;
use Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode;

/**
 * Exposes the DMRE data codeword region.
 */
class InternalDmre extends \Com\Tecnick\Barcode\Type\Square\Dmre
{
    /**
     * Expose the data codeword region: data codewords and padding, without error correction.
     *
     * @return array<int, int>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeDataCodewords(string $data): array
    {
        $this->dmx = new Encode($this->shape, $this->gsonemode, $this->size);
        $this->cdw = $this->getHighLevelEncoding($data);
        $ncw = \count($this->cdw);
        $params = Data::getPaddingSize($this->shape, $ncw, $this->size);
        $this->addPadding($params[11], $ncw);
        return $this->cdw;
    }
}
