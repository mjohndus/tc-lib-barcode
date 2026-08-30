<?php

declare(strict_types=1);

/**
 * InternalAztec.php
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
 * Exposes the Aztec symbol layout and high level bitstream.
 */
class InternalAztec extends \Com\Tecnick\Barcode\Type\Square\Aztec\Encode
{
    /**
     * Number of data codewords reported by the first addCheckWords() call.
     */
    private int $datacdw = 0;

    /**
     * Record the number of data codewords of the symbol.
     *
     * @param array<int> $bitstream Array of bits.
     */
    protected function addCheckWords(array &$bitstream, int &$totbits, int $nbits, int $wsize): int
    {
        $numcdw = parent::addCheckWords($bitstream, $totbits, $nbits, $wsize);
        if ($this->datacdw === 0) {
            $this->datacdw = $numcdw;
        }

        return $numcdw;
    }

    /**
     * Expose the selected symbol layout and the number of data codewords.
     *
     * @return array{'compact': bool, 'layers': int, 'datacdw': int}
     */
    public function exposeLayout(): array
    {
        return [
            'compact' => $this->compact,
            'layers' => $this->numlayers,
            'datacdw' => $this->datacdw,
        ];
    }

    /**
     * Expose the high level encoding bitstream of the given code.
     *
     * @return array{'bits': array<int, int>, 'totbits': int}
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeHighLevelBitstream(string $code, int $eci = -1, string $hint = 'A'): array
    {
        $this->bitstream = [];
        $this->totbits = 0;
        $this->encmode = \Com\Tecnick\Barcode\Type\Square\Aztec\Data::MODE_UPPER;
        $this->highLevelEncoding($code, $eci, $hint);
        return [
            'bits' => $this->bitstream,
            'totbits' => $this->totbits,
        ];
    }
}
