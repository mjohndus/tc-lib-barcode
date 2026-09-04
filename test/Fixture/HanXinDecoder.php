<?php

/**
 * HanXinDecoder.php
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
use Com\Tecnick\Barcode\Type\Square\HanXin\Data;
use Com\Tecnick\Barcode\Type\Square\HanXin\Geometry;

/**
 * Reads a Han Xin Code symbol back into its version, error correction level,
 * mask and message.
 *
 * @since       2026-09-02
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class HanXinDecoder
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
     * Symbol version, from 1 to 84.
     */
    private int $version = 1;

    /**
     * Error correction level, from 1 to 4.
     */
    private int $level = 1;

    /**
     * Data mask pattern reference, from 0 to 3.
     */
    private int $mask = 0;

    /**
     * Information bit stream read back from the symbol.
     */
    private string $bits = '';

    /**
     * @param array<int, string> $grid Rows of binary digits.
     */
    public function __construct(array $grid)
    {
        $this->size = \count($grid);
        foreach ($grid as $row => $line) {
            $this->matrix[$row] = \array_map(\intval(...), \str_split($line));
        }

        $this->readFunctionInformation();
        $this->readInformationBits();
    }

    /**
     * Returns the symbol version, from 1 to 84.
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Returns the error correction level, from 1 to 4.
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Returns the data mask pattern reference, from 0 to 3.
     */
    public function getMask(): int
    {
        return $this->mask;
    }

    /**
     * Returns the message the symbol carries.
     */
    public function getMessage(): string
    {
        $out = '';
        $pos = 0;
        $len = \strlen($this->bits);
        while (($pos + 4) <= $len) {
            $mode = \substr($this->bits, $pos, 4);
            $pos += 4;
            $before = $pos;
            $out .= $this->readSegment($mode, $pos);
            if ($pos === $before) {
                break;
            }
        }

        return $out;
    }

    /**
     * Returns whether every error correction block of the symbol has a zero
     * syndrome, that is the codewords are a valid Reed-Solomon codeword.
     */
    public function hasValidCheckwords(): bool
    {
        $reed = new ReedSolomon(8, Data::GF_DATA);
        $pos = 0;
        $stream = $this->readCodewords();
        foreach (Data::BLOCKS[$this->version][$this->level] ?? [] as $block) {
            for ($idx = 0; $idx < $block[0]; ++$idx) {
                $words = \array_slice($stream, $pos, $block[1]);
                $pos += $block[1];
                $necc = $block[1] - $block[2];
                $data = \array_slice($words, 0, $block[2]);
                if ($reed->checkwords($data, $necc) !== \array_slice($words, $block[2])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Reads the version, the error correction level and the mask out of the
     * two function information areas of the top of the symbol.
     */
    private function readFunctionInformation(): void
    {
        $areas = $this->getInfoCells();
        $bits = '';
        foreach ([$areas[0], $areas[1]] as $cells) {
            foreach ($cells as $cell) {
                $bits .= (string) ($this->matrix[$cell[0]][$cell[1]] ?? 0);
            }
        }

        $value = (int) \bindec(\substr($bits, 0, Data::INFO_DATA_BITS));
        $this->version = (($value >> 4) & 0xFF) - Data::VERSION_OFFSET;
        $this->level = (($value >> 2) & 0x03) + 1;
        $this->mask = $value & 0x03;
    }

    /**
     * Returns the modules of the four function information areas, read from a
     * symbol of the size of this one.
     *
     * @return array{list<array{int, int}>, list<array{int, int}>, list<array{int, int}>, list<array{int, int}>}
     */
    private function getInfoCells(): array
    {
        $version = 1 + (int) (($this->size - Data::SIZE_BASE) / Data::SIZE_STEP);

        return (new Geometry($version))->getInfoCells();
    }

    /**
     * Reads the data codewords out of the encoding region.
     *
     * @return array<int, int> Data codewords in error correction block order.
     */
    private function readCodewords(): array
    {
        $geometry = new Geometry($this->version);
        $bits = '';
        foreach ($geometry->getEncodingCells() as $cell) {
            $module = ($this->matrix[$cell[0]][$cell[1]] ?? 0) ^ $this->getMaskModule($cell[0], $cell[1]);
            $bits .= (string) $module;
        }

        $total = 0;
        foreach (Data::BLOCKS[$this->version][$this->level] ?? [] as $block) {
            $total += $block[0] * $block[1];
        }

        $order = [];
        for ($pos = 0; $pos < Data::GROUP_SIZE; ++$pos) {
            for ($start = 0; $start < $total; $start += Data::GROUP_SIZE) {
                if (($start + $pos) >= $total) {
                    continue;
                }

                $order[] = $start + $pos;
            }
        }

        $stream = [];
        foreach ($order as $idx => $target) {
            $stream[$target] = (int) \bindec(\substr($bits, $idx * 8, 8));
        }

        \ksort($stream);

        return \array_values($stream);
    }

    /**
     * Reads the information bit stream out of the data codewords.
     */
    private function readInformationBits(): void
    {
        $stream = $this->readCodewords();
        $pos = 0;
        $data = [];
        foreach (Data::BLOCKS[$this->version][$this->level] ?? [] as $block) {
            for ($idx = 0; $idx < $block[0]; ++$idx) {
                $data = \array_merge($data, \array_slice($stream, $pos, $block[2]));
                $pos += $block[1];
            }
        }

        $this->bits = '';
        foreach ($data as $word) {
            $this->bits .= \str_pad(\decbin($word), 8, '0', \STR_PAD_LEFT);
        }
    }

    /**
     * Returns the module of the mask pattern at the given position.
     *
     * @param int $row Row of the module.
     * @param int $col Column of the module.
     */
    private function getMaskModule(int $row, int $col): int
    {
        $vpos = $row + 1;
        $hpos = $col + 1;

        return match ($this->mask) {
            1 => (($vpos + $hpos) % 2) === 0 ? 1 : 0,
            2 => (((($vpos + $hpos) % 3) + ($hpos % 3)) % 2) === 0 ? 1 : 0,
            3 => ((($vpos % $hpos) + ($hpos % $vpos) + ($vpos % 3) + ($hpos % 3)) % 2) === 0 ? 1 : 0,
            default => 0,
        };
    }

    /**
     * Reads one mode segment out of the information bit stream.
     *
     * @param string $mode Mode indicator.
     * @param int    $pos  Offset of the segment data, advanced past the segment.
     */
    private function readSegment(string $mode, int &$pos): string
    {
        return match ($mode) {
            Data::MODE_NUMERIC => $this->readNumeric($pos),
            Data::MODE_TEXT => $this->readText($pos),
            Data::MODE_BINARY => $this->readBinary($pos),
            Data::MODE_HANZI_ONE, Data::MODE_HANZI_TWO => $this->readHanzi($mode, $pos),
            Data::MODE_GB_TWO => $this->readGbTwo($pos),
            Data::MODE_GB_FOUR => $this->readGbFour($pos),
            default => '',
        };
    }

    /**
     * Reads a numeric mode segment.
     *
     * @param int $pos Offset of the segment data, advanced past the segment.
     */
    private function readNumeric(int &$pos): string
    {
        $out = '';
        $len = \strlen($this->bits);
        while (($pos + 10) <= $len) {
            $chunk = \substr($this->bits, $pos, 10);
            $pos += 10;
            $digits = \array_search($chunk, Data::NUMERIC_TERMINATOR, true);
            if ($digits !== false) {
                return \substr($out, 0, \max(0, \strlen($out) - 3)) . \substr($out, \strlen($out) - (int) $digits);
            }

            $out .= \str_pad((string) \bindec($chunk), 3, '0', \STR_PAD_LEFT);
        }

        return $out;
    }

    /**
     * Reads a Text mode segment.
     *
     * @param int $pos Offset of the segment data, advanced past the segment.
     */
    private function readText(int &$pos): string
    {
        $out = '';
        $sub = 0;
        $len = \strlen($this->bits);
        while (($pos + 6) <= $len) {
            $chunk = \substr($this->bits, $pos, 6);
            $pos += 6;
            if ($chunk === Data::TEXT_TERMINATOR) {
                return $out;
            }

            if ($chunk === Data::TEXT_SWITCH) {
                $sub = 1 - $sub;
                continue;
            }

            $out .= \chr($this->getTextByte($sub, (int) \bindec($chunk)));
        }

        return $out;
    }

    /**
     * Returns the byte value of a Text mode code.
     *
     * @param int  $sub  Sub mode, zero for Text1 and one for Text2.
     * @param int  $code Six bit code.
     */
    private function getTextByte(int $sub, int $code): int
    {
        if ($sub === 0) {
            if ($code < 10) {
                return 0x30 + $code;
            }

            return $code < 36 ? 0x41 + $code - 10 : 0x61 + $code - 36;
        }

        foreach (Data::TEXT_TWO_RANGES as $range) {
            $low = $range[0] - $range[2];
            $high = $range[1] - $range[2];
            if ($code >= $low && $code <= $high) {
                return $code + $range[2];
            }
        }

        return 0;
    }

    /**
     * Reads a binary byte mode segment.
     *
     * @param int $pos Offset of the segment data, advanced past the segment.
     */
    private function readBinary(int &$pos): string
    {
        $count = (int) \bindec(\substr($this->bits, $pos, Data::BINARY_COUNT_BITS));
        $pos += Data::BINARY_COUNT_BITS;
        $out = '';
        for ($idx = 0; $idx < $count; ++$idx) {
            $out .= \chr((int) \bindec(\substr($this->bits, $pos, 8)));
            $pos += 8;
        }

        return $out;
    }

    /**
     * Reads a common Chinese character mode segment.
     *
     * @param string $mode Mode indicator of the first region.
     * @param int    $pos  Offset of the segment data, advanced past the segment.
     */
    private function readHanzi(string $mode, int &$pos): string
    {
        $region = $mode === Data::MODE_HANZI_ONE ? 1 : 2;
        $out = '';
        $len = \strlen($this->bits);
        while (($pos + 12) <= $len) {
            $chunk = \substr($this->bits, $pos, 12);
            $pos += 12;
            if ($chunk === Data::HANZI_TERMINATOR) {
                return $out;
            }

            if ($chunk === Data::HANZI_SWITCH) {
                $region = 3 - $region;
                continue;
            }

            $out .= $region === 1
                ? $this->getHanziOneBytes((int) \bindec($chunk))
                : $this->getHanziTwoBytes((int) \bindec($chunk));
        }

        return $out;
    }

    /**
     * Returns the byte pair of a common Chinese character of region one.
     *
     * @param int $value Twelve bit value.
     */
    private function getHanziOneBytes(int $value): string
    {
        if ($value >= 0xFCA) {
            return \chr(0xA8) . \chr($value - 0xFCA + 0xA1);
        }

        if ($value >= 0xEB0) {
            $value -= 0xEB0;

            return \chr(0xA1 + \intdiv($value, 0x5E)) . \chr(0xA1 + ($value % 0x5E));
        }

        return \chr(0xB0 + \intdiv($value, 0x5E)) . \chr(0xA1 + ($value % 0x5E));
    }

    /**
     * Returns the byte pair of a common Chinese character of region two.
     *
     * @param int $value Twelve bit value.
     */
    private function getHanziTwoBytes(int $value): string
    {
        return \chr(0xD8 + \intdiv($value, 0x5E)) . \chr(0xA1 + ($value % 0x5E));
    }

    /**
     * Reads a GB 18030 two byte region mode segment.
     *
     * @param int $pos Offset of the segment data, advanced past the segment.
     */
    private function readGbTwo(int &$pos): string
    {
        $out = '';
        $len = \strlen($this->bits);
        while (($pos + 15) <= $len) {
            $chunk = \substr($this->bits, $pos, 15);
            $pos += 15;
            if ($chunk === Data::GB_TWO_TERMINATOR) {
                return $out;
            }

            $value = (int) \bindec($chunk);
            $low = $value % 0xBE;
            $out .= \chr(0x81 + \intdiv($value, 0xBE)) . \chr($low <= 0x3E ? $low + 0x40 : $low + 0x41);
        }

        return $out;
    }

    /**
     * Reads a GB 18030 four byte region mode segment, which carries one
     * character.
     *
     * @param int $pos Offset of the segment data, advanced past the segment.
     */
    private function readGbFour(int &$pos): string
    {
        $value = (int) \bindec(\substr($this->bits, $pos, 21));
        $pos += 21;

        return (
            \chr(0x81 + \intdiv($value, 0x3138))
            . \chr(0x30 + \intdiv($value % 0x3138, 0x04EC))
            . \chr(0x81 + \intdiv($value % 0x04EC, 0x0A))
            . \chr(0x30 + ($value % 0x0A))
        );
    }
}
