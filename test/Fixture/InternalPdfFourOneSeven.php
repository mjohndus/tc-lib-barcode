<?php

/**
 * InternalPdfFourOneSeven.php
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
 * Exposes the PDF417 symbol capacity and codeword sequence.
 */
class InternalPdfFourOneSeven extends \Com\Tecnick\Barcode\Type\Square\PdfFourOneSeven
{
    /**
     * Report the symbol capacity against the number of generated codewords.
     *
     * @return array{'capacity': int, 'codewords': int, 'cols': int, 'rows': int}
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeCapacity(): array
    {
        $rows = 0;
        $cols = 0;
        $ecl = 0;
        $codewords = $this->getCodewords($rows, $cols, $ecl);

        return [
            'capacity' => $cols * $rows,
            'codewords' => \count($codewords),
            'cols' => $cols,
            'rows' => $rows,
        ];
    }

    /**
     * Expose the generated codeword sequence.
     *
     * @return array<int, int>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeCodewords(): array
    {
        $rows = 0;
        $cols = 0;
        $ecl = 0;
        return $this->getCodewords($rows, $cols, $ecl);
    }
}
