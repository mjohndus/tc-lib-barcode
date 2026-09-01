<?php

/**
 * InternalQrEncoder.php
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
 * Exposes the code stream reader and the module walker of the QR Code encoder.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalQrEncoder extends \Com\Tecnick\Barcode\Type\Square\QrCode\Encoder
{
    /**
     * Get the next codeword of the interleaved data and error correction stream.
     */
    public function exposeGetCode(): int
    {
        return $this->getCode();
    }

    /**
     * Walk to the next module of the symbol.
     *
     * @return array{'x': int, 'y': int}
     *
     * @throws \Com\Tecnick\Barcode\Exception in case of error
     */
    public function exposeGetNextPosition(): array
    {
        return $this->getNextPosition();
    }

    /**
     * Move the walk out of the symbol.
     */
    public function moveOutOfFrame(): void
    {
        $this->xpos = 0;
        $this->ypos = 0;
        $this->dir = -1;
        $this->bit = 0;
    }

    /**
     * Take one step of the walk from the given state.
     *
     * @return array{int, int, int} Horizontal position, vertical position and direction
     */
    public function exposeNextPositionStep(int $xpos, int $ypos, int $width, int $bit, int $dir): array
    {
        $this->bit = $bit;
        $this->dir = $dir;
        $this->getNextPositionB($xpos, $ypos, $width);

        return [$xpos, $ypos, $this->dir];
    }
}
