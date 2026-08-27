<?php

declare(strict_types=1);

/**
 * DatamatrixDecoder.php
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

use RuntimeException;

/**
 * Decodes a Data Matrix ECC 200 data codeword region (data codewords plus padding, without
 * error correction) back to the encoded byte string, following ISO/IEC 16022 clause 5.2.
 *
 * FNC1 (codeword 232) is emitted back as the byte 0xE8 the encoder accepts for it.
 * The other control codewords (233, 234, 236, 237, 241) are not decoded.
 */
final class DatamatrixDecoder
{
    /**
     * ASCII encodation.
     */
    private const ENC_ASCII = 0;

    /**
     * C40 encodation.
     */
    private const ENC_C40 = 1;

    /**
     * Text encodation.
     */
    private const ENC_TXT = 2;

    /**
     * ANSI X12 encodation.
     */
    private const ENC_X12 = 3;

    /**
     * EDIFACT encodation.
     */
    private const ENC_EDF = 4;

    /**
     * Base 256 encodation.
     */
    private const ENC_B256 = 5;

    /**
     * Codeword stream being decoded.
     *
     * @var array<int, int>
     */
    private array $cdw = [];

    /**
     * Number of codewords in the stream.
     */
    private int $len = 0;

    /**
     * Index of the next codeword to read.
     */
    private int $pos = 0;

    /**
     * Current encodation.
     */
    private int $mode = self::ENC_ASCII;

    /**
     * Decoded byte string.
     */
    private string $out = '';

    /**
     * Decode a data codeword region.
     *
     * @param array<int, int> $cdw Data codewords, including padding.
     *
     * @throws RuntimeException when the stream is not a valid ECC 200 codeword sequence
     */
    public function decode(array $cdw): string
    {
        $this->cdw = \array_values($cdw);
        $this->len = \count($this->cdw);
        $this->pos = 0;
        $this->mode = self::ENC_ASCII;
        $this->out = '';

        while ($this->pos < $this->len) {
            $end = match ($this->mode) {
                self::ENC_C40, self::ENC_TXT, self::ENC_X12 => $this->decodeC40(),
                self::ENC_EDF => $this->decodeEdf(),
                self::ENC_B256 => $this->decodeBaseTwoFiveSix(),
                default => $this->decodeAscii(),
            };
            if ($end) {
                break;
            }
        }

        return $this->out;
    }

    /**
     * Read the next codeword.
     *
     * @throws RuntimeException when the stream ends too early
     */
    private function next(): int
    {
        if ($this->pos >= $this->len) {
            throw new RuntimeException('unexpected end of the codeword stream');
        }

        $cwd = $this->cdw[$this->pos] ?? 0;
        ++$this->pos;
        return $cwd;
    }

    /**
     * Decode one step of ASCII encodation.
     *
     * @return bool true when the first pad codeword has been reached
     *
     * @throws RuntimeException when the codeword is not valid in ASCII encodation
     */
    private function decodeAscii(): bool
    {
        $cwd = $this->next();
        if ($cwd === 129) {
            return true; // pad
        }

        if ($cwd === 254 && $this->pos === $this->len) {
            return true; // unlatch as the last codeword of a full symbol
        }

        if ($cwd >= 1 && $cwd <= 128) {
            $this->out .= \chr($cwd - 1);
            return false;
        }

        if ($cwd >= 130 && $cwd <= 229) {
            $this->out .= \sprintf('%02d', $cwd - 130);
            return false;
        }

        if ($cwd === 232) {
            // FNC1
            $this->out .= "\xE8";
            return false;
        }

        if ($cwd === 235) {
            // upper shift: the next codeword carries a value 128 higher
            $nxt = $this->next();
            if ($nxt < 1 || $nxt > 128) {
                throw new RuntimeException('invalid upper shift value ' . $nxt);
            }

            $this->out .= \chr($nxt + 127);
            return false;
        }

        $this->mode = match ($cwd) {
            230 => self::ENC_C40,
            231 => self::ENC_B256,
            238 => self::ENC_X12,
            239 => self::ENC_TXT,
            240 => self::ENC_EDF,
            default => throw new RuntimeException('unsupported ASCII codeword ' . $cwd),
        };

        return false;
    }

    /**
     * Decode a C40, Text or X12 field up to the unlatch or the end of the data region.
     *
     * @throws RuntimeException when the field is not a valid C40, Text or X12 sequence
     */
    private function decodeC40(): bool
    {
        $shift = 0;
        $upper = false;
        while (true) {
            if (($this->len - $this->pos) < 2) {
                // a single remaining codeword is interpreted in ASCII encodation
                $this->mode = self::ENC_ASCII;
                return false;
            }

            $cw1 = $this->next();
            if ($cw1 === 254) {
                $this->mode = self::ENC_ASCII;
                return false;
            }

            $val = ($cw1 << 8) + $this->next() - 1;
            if ($val < 0) {
                throw new RuntimeException('invalid C40 codeword pair');
            }

            foreach ([\intdiv($val, 1600), \intdiv($val % 1600, 40), $val % 40] as $cvl) {
                $this->decodeC40Value($cvl, $shift, $upper);
            }
        }
    }

    /**
     * Decode a single C40, Text or X12 value.
     *
     * @param int  $val   Value to decode.
     * @param int  $shift Active shift set (0 = basic set).
     * @param bool $upper Pending upper shift.
     *
     * @throws RuntimeException when the value is not defined in the active set
     */
    private function decodeC40Value(int $val, int &$shift, bool &$upper): void
    {
        if ($shift === 0) {
            if ($val < 3 && $this->mode !== self::ENC_X12) {
                $shift = $val + 1;
                return;
            }

            $this->append($this->basicChar($val), $upper);
            return;
        }

        $set = $shift;
        $shift = 0;

        if ($set === 1) {
            if ($val > 31) {
                throw new RuntimeException('invalid shift 1 value ' . $val);
            }

            $this->append($val, $upper);
            return;
        }

        if ($set === 2) {
            if ($val === 30) {
                $upper = true;
                return;
            }

            $this->append($this->shiftTwoChar($val), $upper);
            return;
        }

        $this->append($this->shiftThreeChar($val), $upper);
    }

    /**
     * Append a decoded character, applying any pending upper shift.
     *
     * @param int  $chr   Character code.
     * @param bool $upper Pending upper shift.
     */
    private function append(int $chr, bool &$upper): void
    {
        $this->out .= \chr($upper ? $chr + 128 : $chr);
        $upper = false;
    }

    /**
     * Map a basic set value to a character code.
     *
     * @throws RuntimeException when the value is not defined in the basic set
     */
    private function basicChar(int $val): int
    {
        if ($this->mode === self::ENC_X12 && $val < 3) {
            return match ($val) {
                0 => 13,
                1 => 42,
                default => 62,
            };
        }

        if ($val === 3) {
            return 32;
        }

        if ($val >= 4 && $val <= 13) {
            return $val + 44; // 0-9
        }

        if ($val >= 14 && $val <= 39) {
            return $val + ($this->mode === self::ENC_TXT ? 83 : 51); // a-z or A-Z
        }

        throw new RuntimeException('invalid basic set value ' . $val);
    }

    /**
     * Map a shift 2 set value to a character code.
     *
     * @throws RuntimeException when the value is not defined in the shift 2 set
     */
    private function shiftTwoChar(int $val): int
    {
        if ($val <= 14) {
            return $val + 33; // !-/
        }

        if ($val <= 21) {
            return $val + 43; // :-@
        }

        if ($val <= 26) {
            return $val + 69; // [-_
        }

        throw new RuntimeException('invalid shift 2 value ' . $val);
    }

    /**
     * Map a shift 3 set value to a character code.
     */
    private function shiftThreeChar(int $val): int
    {
        if ($this->mode === self::ENC_TXT && $val >= 1 && $val <= 26) {
            return $val + 64; // A-Z
        }

        return $val + 96;
    }

    /**
     * Decode an EDIFACT field up to the unlatch or the end of the data region.
     *
     * @throws RuntimeException when the stream ends inside a triplet
     */
    private function decodeEdf(): bool
    {
        while (true) {
            if (($this->len - $this->pos) <= 2) {
                // one or two remaining codewords are interpreted in ASCII encodation
                $this->mode = self::ENC_ASCII;
                return false;
            }

            $start = $this->pos;
            $bits = ($this->next() << 16) + ($this->next() << 8) + $this->next();
            for ($idx = 0; $idx < 4; ++$idx) {
                $val = ($bits >> ((3 - $idx) * 6)) & 0x3F;
                if ($val === 0x1F) {
                    // the unlatch is followed by the bit padding up to the codeword boundary
                    $this->pos = $start + \intdiv(($idx * 6) + 13, 8);
                    $this->mode = self::ENC_ASCII;
                    return false;
                }

                $this->out .= \chr($val < 32 ? $val + 64 : $val);
            }
        }
    }

    /**
     * Decode a Base 256 field.
     *
     * @throws RuntimeException when the field length exceeds the data region
     */
    private function decodeBaseTwoFiveSix(): bool
    {
        $dsz = $this->nextBaseTwoFiveSix();
        $count = match (true) {
            $dsz === 0 => $this->len - $this->pos, // up to the end of the data region
            $dsz > 249 => (($dsz - 249) * 250) + $this->nextBaseTwoFiveSix(),
            default => $dsz,
        };

        if ($count > ($this->len - $this->pos)) {
            throw new RuntimeException('Base 256 field length ' . $count . ' exceeds the data region');
        }

        for ($idx = 0; $idx < $count; ++$idx) {
            $this->out .= \chr($this->nextBaseTwoFiveSix());
        }

        $this->mode = self::ENC_ASCII;
        return false;
    }

    /**
     * Read the next Base 256 codeword and remove the 255-state randomising.
     *
     * @throws RuntimeException when the stream ends too early
     */
    private function nextBaseTwoFiveSix(): int
    {
        $idx = $this->pos;
        $val = $this->next() - (((149 * ($idx + 1)) % 255) + 1);
        return $val < 0 ? $val + 256 : $val;
    }
}
