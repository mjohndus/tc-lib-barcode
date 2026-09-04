<?php

/**
 * EanUpcAddonTest.php
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

namespace Test\Linear;

use PHPUnit\Framework\Attributes\DataProvider;
use Test\TestUtil;

/**
 * EAN and UPC add-on symbol test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class EanUpcAddonTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Symbol length in modules of the combined symbol, and the minimum quiet
     * zones that bring it to the total length of the GS1 General Specifications.
     *
     * @return array<int, array{string, string, int, int}>
     */
    public static function symbolLengthProvider(): array
    {
        return [
            // type, code, modules of the combined symbol, total length with the minimum quiet zones
            ['EAN13', '9781234567897+12',    122, 138],
            ['EAN13', '9781234567897+12345', 149, 165],
            ['UPCA',  '72527273070+12',      124, 138],
            ['UPCA',  '72527273070+12345',   151, 165],
            ['UPCE',  '725277+12',           78,  92],
            ['UPCE',  '725277+12345',        105, 119],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('symbolLengthProvider')]
    public function testSymbolLength(string $type, string $code, int $ncols, int $total): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj($type, $code)->getArray();
        $this->assertSame($ncols, $data['ncols']);
        $this->assertSame(1, $data['nrows']);
        // the left quiet zone of the main symbol plus the 5 modules of the right one
        $left = $type === 'EAN13' ? 11 : 9;
        $this->assertSame($total, $left + $ncols + 5);
    }

    /**
     * The combined symbol is the main symbol, the separation and the standalone
     * add-on symbol, drawn side by side.
     *
     * @return array<int, array{string, string, string, string, int}>
     */
    public static function compositionProvider(): array
    {
        return [
            ['EAN13', '9781234567897', 'EAN2', '12',    7],
            ['EAN13', '9781234567897', 'EAN5', '12345', 7],
            ['UPCA',  '72527273070',   'EAN2', '12',    9],
            ['UPCA',  '72527273070',   'EAN5', '12345', 9],
            ['UPCE',  '725277',        'EAN2', '12',    7],
            ['UPCE',  '725277',        'EAN5', '12345', 7],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('compositionProvider')]
    public function testComposition(
        string $type,
        string $code,
        string $addon_type,
        string $addon,
        int $separation,
    ): void {
        $barcode = $this->getTestObject();
        $main = \rtrim($barcode->getBarcodeObj($type, $code)->getGrid(), "\n");
        $extension = \rtrim($barcode->getBarcodeObj($addon_type, $addon)->getGrid(), "\n");
        $combined = \rtrim($barcode->getBarcodeObj($type, $code . '+' . $addon)->getGrid(), "\n");
        $this->assertSame($main . \str_repeat('0', \max(0, $separation)) . $extension, $combined);
    }

    /**
     * The add-on digits are part of the human readable interpretation.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetExtendedCode(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            '9781234567897+12345',
            $barcode->getBarcodeObj('EAN13', '9781234567897+12345')->getExtendedCode(),
        );
        $this->assertSame('0725272730706+12', $barcode->getBarcodeObj('UPCA', '72527273070+12')->getExtendedCode());
        $this->assertSame('0072527000078+12', $barcode->getBarcodeObj('UPCE', '725277+12')->getExtendedCode());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function invalidAddonProvider(): array
    {
        return [
            // the add-on carries 2 or 5 digits only
            ['EAN13', '9781234567897+1'],
            ['EAN13', '9781234567897+123'],
            ['EAN13', '9781234567897+1234'],
            ['EAN13', '9781234567897+123456'],
            ['EAN13', '9781234567897+'],
            ['EAN13', '9781234567897+1a'],
            ['EAN13', '9781234567897+12+34'],
            // EAN-8 admits no add-on
            ['EAN8',  '1234567+12'],
            // the main code is still validated
            ['EAN13', '97812345678971234+12'],
            ['UPCE',  '123456789012+12'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidAddonProvider')]
    public function testInvalidAddon(string $type, string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj($type, $code);
    }
}
