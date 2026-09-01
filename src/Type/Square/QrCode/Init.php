<?php

declare(strict_types=1);

/**
 * Init.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Com\Tecnick\Barcode\Type\Square\QrCode;

use Com\Tecnick\Barcode\Type\ReedSolomon;

/**
 * Com\Tecnick\Barcode\Type\Square\QrCode\Init
 *
 * Reed-Solomon codec initialization methods for QrCode Barcode type class
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2010-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * @phpstan-type RSblock array{
 *          'data': array<int, int>,
 *          'dataLength': int,
 *          'ecc': array<int, int>,
 *          'eccLength': int,
 *      }
 */
abstract class Init extends \Com\Tecnick\Barcode\Type\Square\QrCode\Mask
{
    /**
     * Data code
     *
     * @var array<int, int>
     */
    protected array $datacode = [];

    /**
     * Error correction code
     *
     * @var array<int, int>
     */
    protected array $ecccode = [];

    /**
     * Blocks
     */
    protected int $blocks;

    /**
     * Reed-Solomon blocks
     *
     * @var array<int, RSblock>
     */
    protected array $rsblocks = []; //of RSblock

    /**
     * Counter
     */
    protected int $count;

    /**
     * Data length
     */
    protected int $dataLength;

    /**
     * Error correction length
     */
    protected int $eccLength;

    /**
     * Value bv1
     */
    protected int $bv1;

    /**
     * Width.
     */
    protected int $width;

    /**
     * Frame
     *
     * @var array<int, string>
     */
    protected array $frame = [];

    /**
     * Horizontal bit position
     */
    protected int $xpos;

    /**
     * Vertical bit position
     */
    protected int $ypos;

    /**
     * Direction
     */
    protected int $dir;

    /**
     * Single bit value
     */
    protected int $bit;

    /**
     * Initialize code
     *
     * @param array<int, int> $spec Array of ECC specification
     */
    protected function init(array $spec): void
    {
        $reedSolomon = new ReedSolomon(8, ReedSolomon::GF_QRCODE, 0);
        $blockNo = 0;
        $dataPos = 0;
        $eccPos = 0;
        $endfor = $this->spc->rsBlockNum1($spec);
        $this->initLoop(
            $endfor,
            $this->spc->rsDataCodes1($spec),
            $this->spc->rsEccCodes1($spec),
            $reedSolomon,
            $eccPos,
            $blockNo,
            $dataPos,
        );
        if ($this->spc->rsBlockNum2($spec) === 0) {
            return;
        }

        $endfor = $this->spc->rsBlockNum2($spec);
        $this->initLoop(
            $endfor,
            $this->spc->rsDataCodes2($spec),
            $this->spc->rsEccCodes2($spec),
            $reedSolomon,
            $eccPos,
            $blockNo,
            $dataPos,
        );
    }

    /**
     * Internal loop for init
     *
     * @param int   $endfor  End for
     * @param int   $dlv     Data length value
     * @param int   $elv     Error correction length value
     * @param ReedSolomon $reedSolomon Reed-Solomon codec
     * @param int   $eccPos  Error correction code position
     * @param int   $blockNo Block number
     * @param int   $dataPos Data position
     */
    protected function initLoop(
        int $endfor,
        int $dlv,
        int $elv,
        ReedSolomon $reedSolomon,
        int &$eccPos,
        int &$blockNo,
        int &$dataPos,
    ): void {
        for ($idx = 0; $idx < $endfor; ++$idx) {
            $data = \array_slice($this->datacode, $dataPos);
            $ecc = $reedSolomon->checkwords(\array_slice($data, 0, $dlv), $elv);
            $this->rsblocks[$blockNo] = [
                'data' => $data,
                'dataLength' => $dlv,
                'ecc' => $ecc,
                'eccLength' => $elv,
            ];
            $this->ecccode = \array_merge(\array_slice($this->ecccode, 0, $eccPos), $ecc);
            $dataPos += $dlv;
            $eccPos += $elv;
            ++$blockNo;
        }
    }
}
