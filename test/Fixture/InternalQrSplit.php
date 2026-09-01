<?php

/**
 * InternalQrSplit.php
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
 * Feeds the input splitter with the run lengths of the mode readers.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalQrSplit extends \Com\Tecnick\Barcode\Type\Square\QrCode\Split
{
    /**
     * Run lengths that the mode readers return, in order.
     *
     * @var array<int, int>
     */
    public array $runs = [];

    protected function eatNum(string $data): int
    {
        return $this->nextRun();
    }

    protected function eatAn(string $data): int
    {
        return $this->nextRun();
    }

    protected function eatKanji(string $data): int
    {
        return $this->nextRun();
    }

    protected function eat8(string $data): int
    {
        return $this->nextRun();
    }

    /**
     * Get the next run length, or one when none is left.
     */
    private function nextRun(): int
    {
        if ($this->runs === []) {
            return 1;
        }

        return \array_shift($this->runs);
    }
}
