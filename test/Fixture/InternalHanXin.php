<?php

/**
 * InternalHanXin.php
 *
 * @since       2026-09-02
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
 * Exposes the data analysis of the Han Xin Code encoder.
 *
 * @since       2026-09-02
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalHanXin extends \Com\Tecnick\Barcode\Type\Square\HanXin\Compaction
{
    /**
     * Get the information bit stream of the code.
     */
    public function exposeBitStream(string $code): string
    {
        return $this->getBitStream($code);
    }

    /**
     * Get the mode of each segment of the code and the bytes it carries.
     *
     * @return list<array{int, string}>
     */
    public function exposeSegments(string $code): array
    {
        $segments = [];
        foreach ($this->getSegments($this->getUnits($code)) as $segment) {
            $bytes = '';
            foreach ($segment[1] as $unit) {
                $bytes .= $unit[1];
            }

            $segments[] = [$segment[0], $bytes];
        }

        return $segments;
    }

    /**
     * Get the six bit code of a Text mode character, or a negative value when
     * neither sub mode represents the byte.
     */
    public function exposeTextCode(int $byte): int
    {
        return $this->getTextCode($byte);
    }

    /**
     * Get whether the byte is a character of the Text1 sub mode.
     */
    public function exposeTextOne(int $byte): bool
    {
        return $this->isTextOne($byte);
    }
}
