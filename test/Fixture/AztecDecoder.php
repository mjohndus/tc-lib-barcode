<?php

declare(strict_types=1);

/**
 * AztecDecoder.php
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
 * Decodes an Aztec high level bitstream back to the encoded byte string,
 * following ISO/IEC 24778 clause 7.3.
 *
 * The character tables are written out here instead of being read from the encoder,
 * so that the decoder is an independent oracle.
 */
final class AztecDecoder
{
    /**
     * Upper table.
     */
    private const TABLE_UPPER = 0;

    /**
     * Lower table.
     */
    private const TABLE_LOWER = 1;

    /**
     * Mixed table.
     */
    private const TABLE_MIXED = 2;

    /**
     * Punct table.
     */
    private const TABLE_PUNCT = 3;

    /**
     * Digit table.
     */
    private const TABLE_DIGIT = 4;

    /**
     * Binary table.
     */
    private const TABLE_BINARY = 5;

    /**
     * Number of bits of a code word of each table.
     *
     * @var array<int, int>
     */
    private const TABLE_BITS = [
        self::TABLE_UPPER => 5,
        self::TABLE_LOWER => 5,
        self::TABLE_MIXED => 5,
        self::TABLE_PUNCT => 5,
        self::TABLE_DIGIT => 4,
    ];

    /**
     * Meaning of each code word of each table.
     * The control words are the CTRL_* entries and FLG(n).
     *
     * @var array<int, array<int, string>>
     */
    private const TABLES = [
        self::TABLE_UPPER => [
            'CTRL_PS',
            ' ',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'CTRL_LL',
            'CTRL_ML',
            'CTRL_DL',
            'CTRL_BS',
        ],
        self::TABLE_LOWER => [
            'CTRL_PS',
            ' ',
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
            'CTRL_US',
            'CTRL_ML',
            'CTRL_DL',
            'CTRL_BS',
        ],
        self::TABLE_MIXED => [
            'CTRL_PS',
            ' ',
            "\x01",
            "\x02",
            "\x03",
            "\x04",
            "\x05",
            "\x06",
            "\x07",
            "\x08",
            "\x09",
            "\x0a",
            "\x0b",
            "\x0c",
            "\x0d",
            "\x1b",
            "\x1c",
            "\x1d",
            "\x1e",
            "\x1f",
            '@',
            '\\',
            '^',
            '_',
            '`',
            '|',
            '~',
            "\x7f",
            'CTRL_LL',
            'CTRL_UL',
            'CTRL_PL',
            'CTRL_BS',
        ],
        self::TABLE_PUNCT => [
            'FLG(n)',
            "\x0d",
            "\x0d\x0a",
            '. ',
            ', ',
            ': ',
            '!',
            '"',
            '#',
            '$',
            '%',
            '&',
            "'",
            '(',
            ')',
            '*',
            '+',
            ',',
            '-',
            '.',
            '/',
            ':',
            ';',
            '<',
            '=',
            '>',
            '?',
            '[',
            ']',
            '{',
            '}',
            'CTRL_UL',
        ],
        self::TABLE_DIGIT => [
            'CTRL_PS',
            ' ',
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            ',',
            '.',
            'CTRL_UL',
            'CTRL_US',
        ],
    ];

    /**
     * Bits being decoded.
     *
     * @var array<int, int>
     */
    private array $bits = [];

    /**
     * Number of bits.
     */
    private int $len = 0;

    /**
     * Index of the next bit to read.
     */
    private int $pos = 0;

    /**
     * Decoded byte string.
     */
    private string $out = '';

    /**
     * Extended Channel Interpretation values read from the FLG(n) sequences.
     *
     * @var array<int, int>
     */
    private array $eci = [];

    /**
     * Decode a high level bitstream.
     *
     * @param array<int, int> $bits   Bits of the high level encoding.
     * @param int             $totbits Number of significant bits.
     *
     * @throws RuntimeException when the bitstream is not a valid Aztec high level encoding
     */
    public function decode(array $bits, int $totbits): string
    {
        $this->bits = \array_values($bits);
        $this->len = \min($totbits, \count($this->bits));
        $this->pos = 0;
        $this->out = '';
        $this->eci = [];

        $latch = self::TABLE_UPPER;
        $shift = self::TABLE_UPPER;
        while (true) {
            if ($shift === self::TABLE_BINARY) {
                if (!$this->decodeBinary()) {
                    return $this->out;
                }

                $shift = $latch;
                continue;
            }

            $size = self::TABLE_BITS[$shift] ?? 5;
            if (($this->len - $this->pos) < $size) {
                return $this->out;
            }

            $code = $this->read($size);
            $str = self::TABLES[$shift][$code] ?? null;
            if ($str === null) {
                throw new RuntimeException('undefined code word ' . $code);
            }

            $shift = $latch;
            if ($str === 'FLG(n)') {
                $this->decodeFlg();
                continue;
            }

            if (!\str_starts_with($str, 'CTRL_')) {
                $this->out .= $str;
                continue;
            }

            // a shift ends in the table it was invoked from, a latch does not
            $latch = $shift;
            $shift = $this->tableOf($str[5]);
            if ($str[6] === 'L') {
                $latch = $shift;
            }
        }
    }

    /**
     * Return the ECI values read from the FLG(n) sequences of the last decoded bitstream.
     *
     * @return array<int, int>
     */
    public function getEci(): array
    {
        return $this->eci;
    }

    /**
     * Map a table letter to its identifier.
     *
     * @throws RuntimeException when the letter is not a known table
     */
    private function tableOf(string $chr): int
    {
        return match ($chr) {
            'U' => self::TABLE_UPPER,
            'L' => self::TABLE_LOWER,
            'M' => self::TABLE_MIXED,
            'P' => self::TABLE_PUNCT,
            'D' => self::TABLE_DIGIT,
            'B' => self::TABLE_BINARY,
            default => throw new RuntimeException('unknown table ' . $chr),
        };
    }

    /**
     * Read the given number of bits as an unsigned integer.
     *
     * @throws RuntimeException when the bitstream ends too early
     */
    private function read(int $size): int
    {
        if (($this->len - $this->pos) < $size) {
            throw new RuntimeException('unexpected end of the bitstream');
        }

        $val = 0;
        for ($idx = 0; $idx < $size; ++$idx) {
            $val = ($val << 1) | ($this->bits[$this->pos] ?? 0);
            ++$this->pos;
        }

        return $val;
    }

    /**
     * Decode a Binary Shift field.
     *
     * @return bool false when the bitstream ends before the field
     *
     * @throws RuntimeException when the field runs past the end of the bitstream
     */
    private function decodeBinary(): bool
    {
        if (($this->len - $this->pos) < 5) {
            return false;
        }

        $count = $this->read(5);
        if ($count === 0) {
            $count = $this->read(11) + 31;
        }

        for ($idx = 0; $idx < $count; ++$idx) {
            $this->out .= \chr($this->read(8));
        }

        return true;
    }

    /**
     * Decode an FLG(n) sequence: FNC1 for n = 0, otherwise n digits of an ECI value.
     *
     * @throws RuntimeException when the digits are not valid Digit table code words
     */
    private function decodeFlg(): void
    {
        $digits = $this->read(3);
        if ($digits === 0) {
            $this->eci[] = 0; // FNC1
            return;
        }

        $value = '';
        for ($idx = 0; $idx < $digits; ++$idx) {
            $code = $this->read(4);
            if ($code < 2 || $code > 11) {
                throw new RuntimeException('invalid FLG digit code word ' . $code);
            }

            $value .= \chr($code + 46);
        }

        $this->eci[] = (int) $value;
    }
}
