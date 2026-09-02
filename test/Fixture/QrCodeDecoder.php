<?php

/**
 * QrCodeDecoder.php
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

use Com\Tecnick\Barcode\Type\ReedSolomon;
use Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use Com\Tecnick\Barcode\Type\Square\QrCode\Encode;
use Com\Tecnick\Barcode\Type\Square\QrCode\Geometry;

/**
 * Reads a QR Code symbol back into its version, error correction level, mask
 * and message, following the reference decoding algorithm of section 12 of
 * ISO/IEC 18004 in reverse of the encoding clauses.
 *
 * @since       2026-09-02
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class QrCodeDecoder
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
     * Symbol version, from 1 to 40.
     */
    private int $version = 1;

    /**
     * Error correction level, from 0 to 3.
     */
    private int $level = 0;

    /**
     * Data mask pattern reference, from 0 to 7.
     */
    private int $mask = 0;

    /**
     * Version information read from the symbol, or a negative value when the
     * version does not carry it.
     */
    private int $versionInfo = -1;

    private Geometry $geometry;

    /**
     * @param array<int, string> $grid Rows of binary digits.
     */
    public function __construct(array $grid)
    {
        $this->size = \count($grid);
        foreach ($grid as $row => $line) {
            $this->matrix[$row] = \array_map(\intval(...), \str_split($line));
        }

        $this->version = \intdiv($this->size - 17, 4);
        $this->geometry = new Geometry($this->version);
        $this->readFormatInformation();
        $this->readVersionInformation();
    }

    /**
     * Returns the symbol version derived from the symbol size.
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Returns the error correction level read from the format information.
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Returns the data mask pattern reference read from the format information.
     */
    public function getMask(): int
    {
        return $this->mask;
    }

    /**
     * Returns the version read from the version information, or a negative value
     * for the versions 1 to 6, which do not carry it.
     */
    public function getVersionInfo(): int
    {
        return $this->versionInfo;
    }

    /**
     * Returns the message carried by the symbol, decoding every mode segment
     * until the terminator or the end of the data codewords.
     */
    public function getMessage(): string
    {
        $bits = $this->getDataBits();
        $group = $this->getVersionGroup();
        $message = '';
        $pos = 0;
        while (($pos + Data::MODE_BITS) <= \strlen($bits)) {
            $indicator = (int) \bindec(\substr($bits, $pos, Data::MODE_BITS));
            $mode = \array_search($indicator, Data::MODE_INDICATOR, true);
            if ($mode === false) {
                break;
            }

            $pos += Data::MODE_BITS;
            $countBits = Data::COUNT_BITS[$group][$mode] ?? 0;
            $count = (int) \bindec(\substr($bits, $pos, $countBits));
            $pos += $countBits;
            $message .= $this->getData($bits, $pos, $mode, $count);
        }

        return $message;
    }

    /**
     * Returns whether every block of the symbol carries the error correction
     * codewords its data codewords generate.
     */
    public function hasValidCheckwords(): bool
    {
        $stream = $this->getCodewords();
        $sizes = $this->getBlockSizes();
        $ecc = Data::ECC_BLOCKS[$this->version][$this->level][0] ?? 0;
        $blocks = $this->deinterleave($stream, $sizes, $ecc);
        $errorCorrection = new ReedSolomon(8, ReedSolomon::GF_QRCODE, 0);
        foreach ($blocks as $block) {
            if ($errorCorrection->checkwords($block[0], $ecc) !== $block[1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Read the fifteen bits of the first copy of the format information.
     */
    private function readFormatInformation(): void
    {
        $format = 0;
        for ($pos = 0; $pos < 15; ++$pos) {
            $row = match (true) {
                $pos < 8 => 8,
                $pos === 8 => 7,
                default => 14 - $pos,
            };
            $col = match (true) {
                $pos < 6 => $pos,
                $pos === 6 => 7,
                default => 8,
            };
            $format |= ($this->matrix[$row][$col] ?? 0) << $pos;
        }

        $format ^= Data::FORMAT_MASK;
        $indicator = ($format >> 13) & 3;
        $level = \array_search($indicator, Data::ECC_INDICATOR, true);
        $this->level = $level === false ? 0 : $level;
        $this->mask = ($format >> 10) & 7;
    }

    /**
     * Read the eighteen bits of the first copy of the version information.
     */
    private function readVersionInformation(): void
    {
        if ($this->version < Data::VERSION_INFO_MIN) {
            return;
        }

        $info = 0;
        for ($pos = 0; $pos < 18; ++$pos) {
            $bit = $this->matrix[$this->size - 11 + ($pos % 3)][\intdiv($pos, 3)] ?? 0;
            $info |= $bit << $pos;
        }

        $this->versionInfo = $info >> 12;
    }

    /**
     * Returns the version group of Data::COUNT_BITS the version belongs to.
     */
    private function getVersionGroup(): int
    {
        foreach (Data::COUNT_GROUP_MAX as $group => $max) {
            if ($this->version <= $max) {
                return $group;
            }
        }

        return \count(Data::COUNT_GROUP_MAX) - 1;
    }

    /**
     * Returns the codewords of the encoding region, with the mask removed, in
     * the order they are placed in the symbol.
     *
     * @return array<int, int>
     */
    public function getCodewords(): array
    {
        $bits = '';
        $upward = true;
        for ($col = $this->size - 1; $col > 0; $col -= 2) {
            if ($col === 6) {
                --$col;
            }

            for ($idx = 0; $idx < $this->size; ++$idx) {
                $row = $upward ? $this->size - 1 - $idx : $idx;
                foreach ([$col, $col - 1] as $current) {
                    if ($this->geometry->isFunctionModule($row, $current)) {
                        continue;
                    }

                    $module = $this->matrix[$row][$current] ?? 0;
                    $bits .= (string) ($module ^ ($this->isMasked($row, $current) ? 1 : 0));
                }
            }

            $upward = !$upward;
        }

        $codewords = [];
        foreach (\str_split(\substr($bits, 0, 8 * Encode::getTotalCodewords($this->version)), 8) as $codeword) {
            $codewords[] = (int) \bindec($codeword);
        }

        return $codewords;
    }

    /**
     * Returns the data bit stream of the symbol, that is the data codewords of
     * every block in block order.
     */
    private function getDataBits(): string
    {
        $blocks = $this->deinterleave(
            $this->getCodewords(),
            $this->getBlockSizes(),
            Data::ECC_BLOCKS[$this->version][$this->level][0] ?? 0,
        );
        $bits = '';
        foreach ($blocks as $block) {
            foreach ($block[0] as $codeword) {
                $bits .= \str_pad(\decbin($codeword), 8, '0', \STR_PAD_LEFT);
            }
        }

        return $bits;
    }

    /**
     * Returns the number of data codewords of each block, shortest first.
     *
     * @return array<int, int>
     */
    private function getBlockSizes(): array
    {
        $count = Data::ECC_BLOCKS[$this->version][$this->level][1] ?? 1;
        $total = Encode::getDataCodewords($this->version, $this->level);
        $short = \intdiv($total, $count);
        $long = $total % $count;
        $sizes = \array_fill(0, \max(0, $count - $long), $short);
        for ($idx = 0; $idx < $long; ++$idx) {
            $sizes[] = $short + 1;
        }

        return $sizes;
    }

    /**
     * Returns the data and the error correction codewords of each block, taken
     * back out of the interleaved sequence.
     *
     * @param array<int, int> $stream Codewords of the symbol.
     * @param array<int, int> $sizes  Number of data codewords of each block.
     * @param int             $ecc    Number of error correction codewords per block.
     *
     * @return array<int, array{array<int, int>, array<int, int>}>
     */
    private function deinterleave(array $stream, array $sizes, int $ecc): array
    {
        $blocks = \array_fill(0, \count($sizes), [[], []]);
        $pos = 0;
        $longest = \max($sizes);
        for ($idx = 0; $idx < $longest; ++$idx) {
            foreach ($sizes as $block => $length) {
                if ($idx >= $length) {
                    continue;
                }

                $blocks[$block][0][] = $stream[$pos] ?? 0;
                ++$pos;
            }
        }

        for ($idx = 0; $idx < $ecc; ++$idx) {
            foreach (\array_keys($sizes) as $block) {
                $blocks[$block][1][] = $stream[$pos] ?? 0;
                ++$pos;
            }
        }

        return $blocks;
    }

    /**
     * Returns whether the mask condition holds for the given module.
     */
    private function isMasked(int $row, int $col): bool
    {
        return match ($this->mask) {
            0 => (($row + $col) % 2) === 0,
            1 => ($row % 2) === 0,
            2 => ($col % 3) === 0,
            3 => (($row + $col) % 3) === 0,
            4 => ((\intdiv($row, 2) + \intdiv($col, 3)) % 2) === 0,
            5 => ((($row * $col) % 2) + (($row * $col) % 3)) === 0,
            6 => (((($row * $col) % 2) + (($row * $col) % 3)) % 2) === 0,
            default => (((($row + $col) % 2) + (($row * $col) % 3)) % 2) === 0,
        };
    }

    /**
     * Returns the decoded data of a mode segment and advances the bit position.
     */
    private function getData(string $bits, int &$pos, int $mode, int $count): string
    {
        return match ($mode) {
            Data::MODE_NUMERIC => $this->getNumericData($bits, $pos, $count),
            Data::MODE_ALPHANUM => $this->getAlphanumData($bits, $pos, $count),
            Data::MODE_KANJI => $this->getKanjiData($bits, $pos, $count),
            default => $this->getByteData($bits, $pos, $count),
        };
    }

    /**
     * Returns the decoded data of a numeric mode segment.
     */
    private function getNumericData(string $bits, int &$pos, int $count): string
    {
        $data = '';
        while (\strlen($data) < $count) {
            $digits = \min(3, $count - \strlen($data));
            $size = [1 => 4, 2 => 7, 3 => 10][$digits] ?? 0;
            $data .= \str_pad((string) \bindec(\substr($bits, $pos, $size)), $digits, '0', \STR_PAD_LEFT);
            $pos += $size;
        }

        return $data;
    }

    /**
     * Returns the decoded data of an alphanumeric mode segment.
     */
    private function getAlphanumData(string $bits, int &$pos, int $count): string
    {
        $data = '';
        while (\strlen($data) < $count) {
            if (($count - \strlen($data)) === 1) {
                $data .= Data::AN_CHARS[(int) \bindec(\substr($bits, $pos, 6))] ?? '';
                $pos += 6;
                break;
            }

            $value = (int) \bindec(\substr($bits, $pos, 11));
            $pos += 11;
            $data .= (Data::AN_CHARS[\intdiv($value, 45)] ?? '') . (Data::AN_CHARS[$value % 45] ?? '');
        }

        return $data;
    }

    /**
     * Returns the decoded data of a byte mode segment.
     */
    private function getByteData(string $bits, int &$pos, int $count): string
    {
        $data = '';
        for ($idx = 0; $idx < $count; ++$idx) {
            $data .= \chr((int) \bindec(\substr($bits, $pos, 8)));
            $pos += 8;
        }

        return $data;
    }

    /**
     * Returns the decoded data of a kanji mode segment, as the Shift JIS bytes
     * the mode encodes.
     */
    private function getKanjiData(string $bits, int &$pos, int $count): string
    {
        $data = '';
        for ($idx = 0; $idx < $count; ++$idx) {
            $value = (int) \bindec(\substr($bits, $pos, 13));
            $pos += 13;
            $shifted = (\intdiv($value, 0xC0) << 8) | ($value % 0xC0);
            $sjis = $shifted + (Data::KANJI_RANGES[0][2] ?? 0);
            if ($sjis > (Data::KANJI_RANGES[0][1] ?? 0)) {
                $sjis = $shifted + (Data::KANJI_RANGES[1][2] ?? 0);
            }

            $data .= \chr($sjis >> 8) . \chr($sjis & 0xFF);
        }

        return $data;
    }
}
