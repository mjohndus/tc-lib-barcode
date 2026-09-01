<?php

/**
 * InternalQrByteStream.php
 *
 * @since       2026-04-19
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
 * Exposes the protected bit stream methods of the QR Code ByteStream class.
 *
 * @phpstan-import-type Item from \Com\Tecnick\Barcode\Type\Square\QrCode\Estimate
 */
class InternalQrByteStream extends \Com\Tecnick\Barcode\Type\Square\QrCode\ByteStream
{
    /**
     * @var array<int, array{0: array<int, Item>, 1: int}>
     */
    public array $queuedCbs = [];

    public ?int $forcedEstVer = null;

    public ?int $forcedMinVer = null;

    public function exposeLookAnTable(int $chr): int
    {
        return $this->lookAnTable($chr);
    }

    /**
     * @param array<int, Item> $items
     * @param array<int, string> $data
     *
     * @return array<int, Item>
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeAppendNewInputItem(array $items, int $mode, int $size, array $data): array
    {
        return $this->appendNewInputItem($items, $mode, $size, $data);
    }

    /**
     * @param array<int, string> $data
     * @param array<int, int> $bstream
     *
     * @return Item
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeNewInputItem(int $mode, int $size, array $data, array $bstream = []): array
    {
        return $this->newInputItem($mode, $size, $data, $bstream);
    }

    /**
     * @param array<int, int> $bitstream
     * @param array<int, int> $append
     *
     * @return array<int, int>
     */
    public function exposeAppendBitstream(array $bitstream, array $append): array
    {
        return $this->appendBitstream($bitstream, $append);
    }

    /**
     * @param array<int, int> $bitstream
     *
     * @return array<int, int>
     */
    public function exposeAppendNum(array $bitstream, int $bits, int $num): array
    {
        return $this->appendNum($bitstream, $bits, $num);
    }

    /**
     * @param array<int, int> $bitstream
     * @param array<int, int> $data
     *
     * @return array<int, int>
     */
    public function exposeAppendBytes(array $bitstream, int $size, array $data): array
    {
        return $this->appendBytes($bitstream, $size, $data);
    }

    /**
     * @param array<int, int> $bstream
     *
     * @return array<int, int>
     */
    public function exposeAppendPaddingBit(array $bstream): array
    {
        return $this->appendPaddingBit($bstream);
    }

    /**
     * @param array<int, int> $bstream
     *
     * @return array<int, int>
     */
    public function exposeBitstreamToByte(array $bstream): array
    {
        return $this->bitstreamToByte($bstream);
    }

    /**
     * @param array<int, Item> $items
     *
     * @return array<int, Item>
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeConvertData(array $items): array
    {
        return $this->convertData($items);
    }

    /**
     * @param array<int, Item> $items
     *
     * @return array{0: array<int, Item>, 1: int}
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function exposeCreateBitStream(array $items): array
    {
        return parent::createBitStream($items);
    }

    /**
     * @param array<int, Item> $items
     */
    public function estimateVersion(array $items, int $level): int
    {
        return $this->forcedEstVer ?? parent::estimateVersion($items, $level);
    }

    /**
     * @param array<int, Item> $items
     *
     * @return array{0: array<int, Item>, 1: int}
     */
    protected function createBitStream(array $items): array
    {
        if ($this->queuedCbs !== []) {
            return \array_shift($this->queuedCbs);
        }

        return parent::createBitStream($items);
    }

    protected function getMinimumVersion(int $size, int $level): int
    {
        return $this->forcedMinVer ?? parent::getMinimumVersion($size, $level);
    }
}
