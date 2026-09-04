<?php

/**
 * MicroQrCodeDecoder.php
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

use Com\Tecnick\Barcode\Type\ReedSolomon;
use Com\Tecnick\Barcode\Type\Square\MicroQrCode\Data;
use Com\Tecnick\Barcode\Type\Square\QrCode\Data as QrData;

/**
 * Reads a Micro QR Code symbol back into its symbol number, mask and message.
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class MicroQrCodeDecoder
{
    /**
     * Module matrix: 1 is a dark module, 0 a light one.
     *
     * @var array<int, array<int, int>>
     */
    private array $matrix = [];

    /**
     * Symbol size in modules.
     */
    private int $size = 0;

    /**
     * Symbol number, from 0 to 7.
     */
    private int $symbol = 0;

    /**
     * Data mask pattern reference, from 0 to 3.
     */
    private int $mask = 0;

    /**
     * Symbol version, from 1 to 4.
     */
    private int $version = 1;

    /**
     * Data capacity of the symbol in bits.
     */
    private int $capacity = 0;

    /**
     * Number of error correction codewords of the symbol.
     */
    private int $checkwordCount = 0;

    /**
     * @param array<int, string> $grid Rows of binary digits.
     */
    public function __construct(array $grid)
    {
        $this->size = \count($grid);
        foreach ($grid as $row => $line) {
            $this->matrix[$row] = \array_map(\intval(...), \str_split($line));
        }

        $this->readFormatInformation();
        $symbol = Data::SYMBOLS[$this->symbol] ?? [];
        $this->version = $symbol[0] ?? 1;
        $this->capacity = $symbol[3] ?? 20;
        $this->checkwordCount = $symbol[4] ?? 2;
    }

    /**
     * Returns the symbol number read from the format information.
     */
    public function getSymbolNumber(): int
    {
        return $this->symbol;
    }

    /**
     * Returns the data mask pattern reference read from the format information.
     */
    public function getMask(): int
    {
        return $this->mask;
    }

    /**
     * Returns the message carried by the symbol.
     */
    public function getMessage(): string
    {
        $bits = $this->getBitSequence();
        $pos = Data::MODE_BITS[$this->version] ?? 0;
        $mode = $pos === 0 ? Data::MODE_NUMERIC : (int) \bindec(\substr($bits, 0, $pos));
        $countBits = Data::COUNT_BITS[$this->version][$mode] ?? 0;
        $count = (int) \bindec(\substr($bits, $pos, $countBits));
        $pos += $countBits;

        return $this->getData(\substr($bits, $pos, $this->capacity - $pos), $mode, $count);
    }

    /**
     * Returns the error correction codewords read from the symbol.
     *
     * @return array<int, int>
     */
    public function getCheckwords(): array
    {
        $bits = \substr($this->getBitSequence(), $this->capacity);
        $checkwords = [];
        foreach (\str_split($bits, 8) as $codeword) {
            $checkwords[] = (int) \bindec($codeword);
        }

        return $checkwords;
    }

    /**
     * Returns whether the error correction codewords of the symbol match the
     * ones computed from its data codewords.
     */
    public function hasValidCheckwords(): bool
    {
        $bits = \substr($this->getBitSequence(), 0, $this->capacity);
        $full = $this->capacity - ($this->capacity % 8);
        $codewords = [];
        for ($pos = 0; $pos < $full; $pos += 8) {
            $codewords[] = (int) \bindec(\substr($bits, $pos, 8));
        }

        if ($this->capacity > $full) {
            $codewords[] = (int) \bindec(\substr($bits, $full, 4)) << 4;
        }

        $errorCorrection = new ReedSolomon(8, ReedSolomon::GF_QRCODE, 0);

        return $errorCorrection->checkwords($codewords, $this->checkwordCount) === $this->getCheckwords();
    }

    /**
     * Read the fifteen bits of the format information.
     */
    private function readFormatInformation(): void
    {
        $format = 0;
        for ($pos = 0; $pos < 15; ++$pos) {
            $bit = $pos < 8 ? $this->matrix[$pos + 1][8] ?? 0 : $this->matrix[8][15 - $pos] ?? 0;
            $format |= $bit << $pos;
        }

        $format ^= Data::FORMAT_MASK;
        $this->symbol = ($format >> 12) & 7;
        $this->mask = ($format >> 10) & 3;
    }

    /**
     * Returns the bit sequence of the encoding region, with the mask removed.
     */
    private function getBitSequence(): string
    {
        $bits = '';
        $upward = true;
        for ($col = $this->size - 1; $col > 0; $col -= 2) {
            for ($idx = 0; $idx < $this->size; ++$idx) {
                $row = $upward ? $this->size - 1 - $idx : $idx;
                foreach ([$col, $col - 1] as $current) {
                    if ($this->isFunctionModule($row, $current)) {
                        continue;
                    }

                    $module = $this->matrix[$row][$current] ?? 0;
                    $bits .= (string) ($module ^ ($this->isMasked($row, $current) ? 1 : 0));
                }
            }

            $upward = !$upward;
        }

        return $bits;
    }

    /**
     * Returns whether the module belongs to a function pattern or to the format
     * information.
     */
    private function isFunctionModule(int $row, int $col): bool
    {
        return $row <= 8 && $col <= 8 || $row === 0 || $col === 0;
    }

    /**
     * Returns whether the mask condition holds for the given module.
     */
    private function isMasked(int $row, int $col): bool
    {
        return match ($this->mask) {
            0 => ($row % 2) === 0,
            1 => ((\intdiv($row, 2) + \intdiv($col, 3)) % 2) === 0,
            2 => (((($row * $col) % 2) + (($row * $col) % 3)) % 2) === 0,
            default => (((($row + $col) % 2) + (($row * $col) % 3)) % 2) === 0,
        };
    }

    /**
     * Returns the decoded data of a mode segment.
     */
    private function getData(string $bits, int $mode, int $count): string
    {
        return match ($mode) {
            Data::MODE_NUMERIC => $this->getNumericData($bits, $count),
            Data::MODE_ALPHANUM => $this->getAlphanumData($bits, $count),
            default => $this->getByteData($bits, $count),
        };
    }

    /**
     * Returns the decoded data of a numeric mode segment.
     */
    private function getNumericData(string $bits, int $count): string
    {
        $data = '';
        $pos = 0;
        while (\strlen($data) < $count) {
            $digits = \min(3, $count - \strlen($data));
            $size = $digits === 3 ? 10 : Data::NUMERIC_REMAINDER_BITS[$digits] ?? 0;
            $data .= \str_pad((string) \bindec(\substr($bits, $pos, $size)), $digits, '0', \STR_PAD_LEFT);
            $pos += $size;
        }

        return $data;
    }

    /**
     * Returns the decoded data of an alphanumeric mode segment.
     */
    private function getAlphanumData(string $bits, int $count): string
    {
        $charset = $this->getAlphanumCharset();
        $data = '';
        $pos = 0;
        while (\strlen($data) < $count) {
            if (($count - \strlen($data)) === 1) {
                $data .= $charset[(int) \bindec(\substr($bits, $pos, 6))] ?? '';
                break;
            }

            $value = (int) \bindec(\substr($bits, $pos, 11));
            $pos += 11;
            $data .= ($charset[\intdiv($value, 45)] ?? '') . ($charset[$value % 45] ?? '');
        }

        return $data;
    }

    /**
     * Returns the decoded data of a byte mode segment.
     */
    private function getByteData(string $bits, int $count): string
    {
        $data = '';
        for ($idx = 0; $idx < $count; ++$idx) {
            $data .= \chr((int) \bindec(\substr($bits, $idx * 8, 8)));
        }

        return $data;
    }

    /**
     * Returns the alphanumeric character set keyed by its value.
     *
     * @return array<int, string>
     */
    private function getAlphanumCharset(): array
    {
        return \str_split(QrData::AN_CHARS);
    }
}
