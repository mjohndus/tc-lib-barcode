<?php

/**
 * GsOneDataBarExpandedDecoder.php
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

use Com\Tecnick\Barcode\Type\Linear\GsOneDataBar\Compaction;

/**
 * Reads back the general purpose data compaction field of GS1 DataBar Expanded.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class GsOneDataBarExpandedDecoder
{
    /**
     * Punctuation of the alphanumeric scheme, keyed by its six bit value
     *
     * @var array<int, string>
     */
    private const ALPHANUMERIC_PUNCTUATION = [
        58 => '*',
        59 => ',',
        60 => '-',
        61 => '.',
        62 => '/',
    ];

    /**
     * Punctuation of the ISO/IEC 646 scheme, keyed by its eight bit value
     *
     * @var array<int, string>
     */
    private const ISO646_PUNCTUATION = [
        232 => '!',
        233 => '"',
        234 => '%',
        235 => '&',
        236 => "'",
        237 => '(',
        238 => ')',
        239 => '*',
        240 => '+',
        241 => ',',
        242 => '-',
        243 => '.',
        244 => '/',
        245 => ':',
        246 => ';',
        247 => '<',
        248 => '=',
        249 => '>',
        250 => '?',
        251 => '_',
        252 => ' ',
    ];

    /**
     * Decoded characters
     */
    private string $out = '';

    /**
     * Position of the next bit to read
     */
    private int $pos = 0;

    /**
     * Encodation scheme in force
     */
    private int $mode = Compaction::NUMERIC;

    /**
     * Read a group of bits.
     */
    private function read(string $bits, int $length): int
    {
        $value = (int) \bindec(\substr($bits, $this->pos, $length));
        $this->pos += $length;
        return $value;
    }

    /**
     * Decode one character in the numeric scheme.
     *
     * @return bool False when the field is over
     */
    private function decodeNumeric(string $bits, int $left): bool
    {
        if ($left < 4) {
            return false;
        }

        if (\substr($bits, $this->pos, 4) === '0000' && $left >= 7) {
            $this->pos += 4;
            $this->mode = Compaction::ALPHANUMERIC;
            return true;
        }

        if ($left < 7) {
            $value = $this->read($bits, 4);
            if ($value > 0) {
                $this->out .= (string) ($value - 1);
            }

            return false;
        }

        $value = $this->read($bits, 7) - 8;
        foreach ([\intdiv($value, 11), $value % 11] as $digit) {
            $this->out .= $digit === 10 ? Compaction::FNC1 : (string) $digit;
        }

        return true;
    }

    /**
     * Decode one character in the alphanumeric scheme.
     *
     * @return bool False when the field is over
     */
    private function decodeAlphanumeric(string $bits, int $left): bool
    {
        if ($left < 3) {
            return false;
        }

        if (\substr($bits, $this->pos, 3) === '000') {
            $this->pos += 3;
            $this->mode = Compaction::NUMERIC;
            return true;
        }

        if ($bits[$this->pos] === '1') {
            if ($left < 6) {
                return false;
            }

            $value = $this->read($bits, 6);
            $this->out .= $value <= 57 ? \chr($value + 33) : self::ALPHANUMERIC_PUNCTUATION[$value] ?? '';
            return true;
        }

        if ($left < 5) {
            return false;
        }

        $value = $this->read($bits, 5);
        if ($value === 4) {
            $this->mode = Compaction::ISO646;
            return true;
        }

        $this->out .= $value === 15 ? Compaction::FNC1 : \chr($value + 43);
        return true;
    }

    /**
     * Decode one character in the ISO/IEC 646 scheme.
     *
     * @return bool False when the field is over
     */
    private function decodeIso646(string $bits, int $left): bool
    {
        if ($left < 3) {
            return false;
        }

        if (\substr($bits, $this->pos, 3) === '000') {
            $this->pos += 3;
            $this->mode = Compaction::NUMERIC;
            return true;
        }

        if ($left < 5) {
            return false;
        }

        $head = (int) \bindec(\substr($bits, $this->pos, 5));
        if ($head <= 15) {
            $value = $this->read($bits, 5);
            if ($value === 4) {
                $this->mode = Compaction::ALPHANUMERIC;
                return true;
            }

            $this->out .= $value === 15 ? Compaction::FNC1 : \chr($value + 43);
            return true;
        }

        if ($head <= 28) {
            if ($left < 7) {
                return false;
            }

            $value = $this->read($bits, 7);
            $this->out .= \chr($value + ($value <= 89 ? 1 : 7));
            return true;
        }

        if ($left < 8) {
            return false;
        }

        $this->out .= self::ISO646_PUNCTUATION[$this->read($bits, 8)] ?? '';
        return true;
    }

    /**
     * Decode the general purpose data compaction field.
     * The padding decodes to latches only, and the Function 1 Symbol Character
     * that pads a trailing digit is discarded.
     *
     * @param string $bits Binary string of the field
     */
    public function decode(string $bits): string
    {
        $this->out = '';
        $this->pos = 0;
        $this->mode = Compaction::NUMERIC;
        $len = \strlen($bits);
        while ($this->pos < $len) {
            $left = $len - $this->pos;
            $more = match ($this->mode) {
                Compaction::NUMERIC => $this->decodeNumeric($bits, $left),
                Compaction::ALPHANUMERIC => $this->decodeAlphanumeric($bits, $left),
                default => $this->decodeIso646($bits, $left),
            };
            if (!$more) {
                break;
            }
        }

        return \rtrim($this->out, Compaction::FNC1);
    }
}
