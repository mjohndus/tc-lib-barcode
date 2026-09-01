<?php

/**
 * QrCodeEncoderTest.php
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

namespace Test\Square;

use Com\Tecnick\Barcode\Type\Square\QrCode\ByteStream;
use Com\Tecnick\Barcode\Type\Square\QrCode\Data;
use Com\Tecnick\Barcode\Type\Square\QrCode\Encoder;
use Com\Tecnick\Barcode\Type\Square\QrCode\Split;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalQrEncoder;
use Test\TestUtil;

/**
 * QR Code encoder test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class QrCodeEncoderTest extends TestUtil
{
    /**
     * Split the input the way the barcode type does, and return the codewords
     * and the version they need.
     *
     * @return array{array<int, int>, int}
     *
     * @throws \Com\Tecnick\Barcode\Exception
     */
    private function getDataCode(string $code, int $level): array
    {
        $bsObj = new ByteStream(Data::MODE_8B, 0, $level);
        $split = new Split($bsObj, Data::MODE_8B, 0);
        $datacode = $bsObj->getByteStream($split->getSplittedString($code));

        return [$datacode, $bsObj->version];
    }

    /**
     * An explicit mask number gives the same symbol as the default mask of the
     * same number.
     *
     * @return array<int, array{int}>
     */
    public static function maskProvider(): array
    {
        return [[0], [1], [2], [3], [4], [5], [6], [7]];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Random\RandomException
     */
    #[DataProvider('maskProvider')]
    public function testExplicitMask(int $maskNo): void
    {
        [$datacode, $version] = $this->getDataCode('0123456789', 0);

        $explicit = (new Encoder($version, 0, -1, false, $maskNo))->encodeMask($maskNo, $datacode);
        $byDefault = (new Encoder($version, 0, -1, false, $maskNo))->encodeMask(-1, $datacode);

        $this->assertSame($byDefault, $explicit);
        $this->assertCount(21, $explicit);
    }

    /**
     * The eight masks of ISO/IEC 18004 give eight different symbols.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Random\RandomException
     */
    public function testMasksDiffer(): void
    {
        [$datacode, $version] = $this->getDataCode('0123456789', 0);

        $frames = [];
        for ($maskNo = 0; $maskNo < 8; ++$maskNo) {
            $frames[] = (new Encoder($version, 0, -1, false, 0))->encodeMask($maskNo, $datacode);
        }

        $this->assertCount(8, \array_unique(\array_map(\serialize(...), $frames)));
    }

    /**
     * A mask number outside the eight of the specification leaves the symbol
     * unmasked.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Random\RandomException
     */
    public function testUnknownMaskNumber(): void
    {
        [$datacode, $version] = $this->getDataCode('0123456789', 0);

        $frame = (new Encoder($version, 0, -1, false, 0))->encodeMask(8, $datacode);

        $this->assertCount(21, $frame);
        for ($maskNo = 0; $maskNo < 8; ++$maskNo) {
            $masked = (new Encoder($version, 0, -1, false, 0))->encodeMask($maskNo, $datacode);
            $this->assertNotSame($masked, $frame);
        }
    }

    /**
     * The code stream is read once, and it runs out with the last error
     * correction codeword.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Random\RandomException
     */
    public function testCodeStreamIsExhausted(): void
    {
        [$datacode, $version] = $this->getDataCode('0123456789', 0);
        $encoder = new InternalQrEncoder($version, 0, -1, false, 2);
        $encoder->encodeMask(0, $datacode);

        $this->assertSame(0, $encoder->exposeGetCode());
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Random\RandomException
     */
    public function testWalkOutOfTheSymbol(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        [$datacode, $version] = $this->getDataCode('0123456789', 0);
        $encoder = new InternalQrEncoder($version, 0, -1, false, 2);
        $encoder->encodeMask(0, $datacode);
        $encoder->moveOutOfFrame();

        $encoder->exposeGetNextPosition();
    }

    /**
     * The walk goes up and down in columns of two modules, and skips the
     * vertical timing pattern of the column 6.
     *
     * @return array<int, array{array{int, int, int, int, int}, array{int, int, int}}>
     */
    public static function walkProvider(): array
    {
        return [
            // position, width, bit and direction, then position and direction
            // one step later
            [[20, 10, 21, 0, -1], [19, 10, -1]],
            [[19, 10, 21, 1, -1], [20, 9, -1]],
            [[19, 0, 21, 1, -1], [18, 0, 1]],
            [[19, 20, 21, 1, 1], [18, 20, -1]],
            // the turn at the bottom of the column 8, which skips the column 6
            [[7, 0, 21, 1, -1], [5, 9, 1]],
            // the same turn at the top of the column 8
            [[7, 20, 21, 1, 1], [5, 12, -1]],
        ];
    }

    /**
     * @param array{int, int, int, int, int} $step
     * @param array{int, int, int}           $expected
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Random\RandomException
     */
    #[DataProvider('walkProvider')]
    public function testWalk(array $step, array $expected): void
    {
        $encoder = new InternalQrEncoder(1, 0, -1, false, 2);

        $this->assertSame($expected, $encoder->exposeNextPositionStep(...$step));
    }
}
