<?php

/**
 * PdfFourOneSevenCompactTest.php
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

use PHPUnit\Framework\Attributes\DataProvider;
use Test\TestUtil;

/**
 * Compact PDF417 Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class PdfFourOneSevenCompactTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * @return array<int, array{string}>
     */
    public static function codeProvider(): array
    {
        return [
            ['0123456789'],
            ['ABCDefgh 1234567890'],
            [\str_repeat('tc-lib-barcode ', 20)],
        ];
    }

    /**
     * The right row indicator and the 18 modules of the stop pattern are
     * replaced by a single bar, so each row is 34 modules narrower.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('codeProvider')]
    public function testSymbolWidth(string $code): void
    {
        $barcode = $this->getTestObject();
        $full = $barcode->getBarcodeObj('PDF417', $code)->getArray();
        $compact = $barcode->getBarcodeObj('PDF417C', $code)->getArray();
        $this->assertSame($full['ncols'] - 34, $compact['ncols']);
        $this->assertSame($full['nrows'], $compact['nrows']);
    }

    /**
     * Everything up to and including the last data column is unchanged, and
     * each row is terminated by a single bar followed by the quiet zone.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('codeProvider')]
    public function testRowsMatchThePdfFourOneSevenRows(string $code): void
    {
        $barcode = $this->getTestObject();
        $full = \explode("\n", \rtrim($barcode->getBarcodeObj('PDF417', $code)->getGrid(), "\n"));
        $compact = \explode("\n", \rtrim($barcode->getBarcodeObj('PDF417C', $code)->getGrid(), "\n"));
        $this->assertCount(\count($full), $compact);

        foreach ($full as $index => $row) {
            // drop the right row indicator, the stop pattern and the right quiet
            // zone, then add the terminating bar and the right quiet zone back
            $expected = \substr($row, 0, \strlen($row) - 37) . '100';
            if (!\str_contains($row, '1')) {
                // the rows of the vertical quiet zone carry no bar
                $expected = \str_repeat('0', \max(0, \strlen($row) - 34));
            }

            $this->assertSame($expected, $compact[$index] ?? '');
        }
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['0123456789',          '6b08d8d7efaf49841b4fdd63b506ad5c'],
            ['ABCDefgh 1234567890', '9c2e4178646214eb9f5fba2a15b5879c'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getGridDataProvider')]
    public function testGetGrid(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('PDF417C', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEmptyInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('PDF417C', '');
    }
}
