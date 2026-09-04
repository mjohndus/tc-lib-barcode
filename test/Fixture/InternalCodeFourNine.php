<?php

/**
 * InternalCodeFourNine.php
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

use Com\Tecnick\Barcode\Type\Linear\CodeFourNine;

/**
 * Exposes the internals of the CODE 49 encoder.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalCodeFourNine extends CodeFourNine
{
    /**
     * Get the code characters of the input and the starting mode.
     *
     * @return array{array<int, int>, int}
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeCodeData(): array
    {
        $data = $this->getCodeData();
        return [$data, $this->mode];
    }

    /**
     * Get the symbol characters of the whole symbol, four per row.
     *
     * @return array<int, array<int, int>>
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeSymbolChars(): array
    {
        $data = $this->getCodeData();
        return $this->getSymbolChars($this->getRowCount(\count($data)), $data);
    }

    /**
     * Get the code characters of a run of digits in the numeric encodation.
     *
     * @return array<int, int>
     */
    public function exposeNumeric(string $digits): array
    {
        return $this->encodeNumeric($digits);
    }
}
