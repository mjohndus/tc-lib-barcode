<?php

/**
 * InternalQrEncode.php
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

use Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use Com\Tecnick\Barcode\Type\Square\QrCode\Encode;

/**
 * Exposes the internals of the QR Code symbol builder that no public entry
 * point reaches on its own.
 *
 * @since       2026-09-02
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InternalQrEncode extends Encode
{
    /**
     * Build an instance for the given version and error correction level without
     * encoding anything.
     */
    public static function forSymbol(int $version = 1, int $level = 0): self
    {
        $encode = (new \ReflectionClass(self::class))->newInstanceWithoutConstructor();
        $encode->version = $version;
        $encode->level = $level;
        $encode->size = (4 * $version) + 17;

        return $encode;
    }

    /**
     * @return array<int, int>
     */
    public function exposeBlockSizes(): array
    {
        return $this->getBlockSizes();
    }

    /**
     * @param array<int, array<int, int>> $blocks
     *
     * @return array<int, int>
     */
    public function exposeInterleave(array $blocks): array
    {
        return $this->interleave($blocks);
    }

    public function exposeDataBits(int $mode, int $count): int
    {
        return $this->getDataBits($mode, $count);
    }

    public function exposeHeaderBits(int $mode, int $group): int
    {
        return $this->getHeaderBits($mode, $group);
    }

    /**
     * @return array<int, array{int, int, int}>
     */
    public function exposeSegments(string $code, int $group, bool $kanji): array
    {
        return $this->getSegments($code, $group, $kanji);
    }

    /**
     * @param array<int, array{int, int, int}> $segments
     */
    public function exposeStreamBits(array $segments, int $group): int
    {
        return $this->getStreamBits($segments, $group);
    }

    public function exposeVersionGroup(int $version): int
    {
        return $this->getVersionGroup($version);
    }

    public function exposeBchCode(int $data, int $generator, int $dataBits, int $checkBits): int
    {
        return $this->getBchCode($data, $generator, $dataBits, $checkBits);
    }

    public function exposeIsMasked(int $pattern, int $row, int $col): bool
    {
        return $this->isMasked($pattern, $row, $col);
    }

    public function exposeKanjiValueAt(string $code, int $pos): int
    {
        return $this->kanjiValueAt($code, $pos);
    }

    public function exposeAlphanumericValue(int $ord): int
    {
        return $this->alphanumericValue($ord);
    }

    /**
     * Set the module matrix and return the penalty points of Table 11.
     *
     * @param array<int, string> $grid Rows of binary digits.
     *
     * @return array{int, int, int, int}
     */
    public function exposePenalties(array $grid): array
    {
        $this->size = \count($grid);
        $this->matrix = [];
        foreach ($grid as $row => $line) {
            $this->matrix[$row] = \array_map(\intval(...), \str_split($line));
        }

        return [
            $this->getRunPenalty(),
            $this->getBlockPenalty(),
            $this->getFinderPenalty(),
            $this->getBalancePenalty(),
        ];
    }

    /**
     * Returns the format information of the error correction level and the mask.
     */
    public function exposeFormatInfo(int $level, int $mask): int
    {
        $data = ((Data::ECC_INDICATOR[$level] ?? 0) << 3) | $mask;

        return $this->getBchCode($data, Data::FORMAT_GENERATOR, 5, 10) ^ Data::FORMAT_MASK;
    }
}
