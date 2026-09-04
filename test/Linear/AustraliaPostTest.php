<?php

/**
 * AustraliaPostTest.php
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
 * Australia Post 4-State Customer Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class AustraliaPostTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Read the bar values back off the symbol, one character per bar
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getBarValues(string $type, string $code): string
    {
        $grid = $this->getTestObject()->getBarcodeObj($type, $code)->getGridArray();
        $values = '';
        $ncols = \count($grid[1] ?? []);
        for ($col = 0; $col < $ncols; $col += 2) {
            $ascender = ($grid[0][$col] ?? '0') === '1';
            $descender = ($grid[2][$col] ?? '0') === '1';
            $values .= match (true) {
                $ascender && $descender => '0',
                $ascender => '1',
                $descender => '2',
                default => '3',
            };
        }

        return $values;
    }

    /**
     * The four symbols printed in the specification, reproduced bar for bar.
     *
     * @return array<int, array{string, string, string}>
     */
    public static function referenceSymbolProvider(): array
    {
        return [
            // Standard Customer Barcode, sorting code 54516251
            ['AUSPOST', '1154516251',       '1301011211120120021201303030220222213'],
            // Standard Customer Barcode, sorting code 39549554
            ['AUSPOST', '1139549554',       '1301011030121130121211331210131132213'],
            // Customer Barcode 2, the Customer Information field left to fillers
            ['AUSPOST', '5954516251',       '1312301211120120021201333333333333333310031000312313'],
            // Customer Barcode 3, the Customer Information field holding ABC123
            ['AUSPOST', '6254516251ABC123', '1320021211120120021201000001002300301302333333333333313133002021313'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('referenceSymbolProvider')]
    public function testReferenceSymbols(string $type, string $code, string $expected): void
    {
        $this->assertSame($expected, $this->getBarValues($type, $code));
    }

    /**
     * @return array<int, array{string, string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['AUSPOST',   '1139549554',           'ba0da2c1d60de0e2845dc8d585f4d7a1'],
            ['AUSPOST',   '5954516251ABC12',      '9d366dc60deb347f82ef36b7fba3ea9b'],
            ['AUSPOST,N', '595451625112345678',   '73ee5757395b70c9af95c0762745693b'],
            ['AUSPOST',   '6254516251Australia1', 'cd98dd01571540636f3370afaed890cc'],
            ['AUSPOST',   '0000000000',           '412d139939bdca0a26269afb9d9b7607'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getGridDataProvider')]
    public function testGetGrid(string $type, string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj($type, $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * @return array<int, array{string, int}>
     */
    public static function symbolLengthProvider(): array
    {
        return [
            ['1139549554', 37],
            ['5954516251', 52],
            ['6254516251', 67],
            ['0000000000', 37],
        ];
    }

    /**
     * Each format has a fixed number of bars, each two modules apart.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('symbolLengthProvider')]
    public function testSymbolLength(string $code, int $bars): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('AUSPOST', $code)->getArray();
        $this->assertCount($bars, $data['bars']);
        $this->assertSame((2 * $bars) - 1, $data['ncols']);
        $this->assertSame(3, $data['nrows']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testGetExtendedCode(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame(
            '6254516251ABC123',
            $barcode->getBarcodeObj('AUSPOST', '6254516251ABC123')->getExtendedCode(),
        );
    }

    /**
     * The start and stop bars are an ascender and a tracker, so the symbol
     * cannot be read the wrong way round.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testStartAndStopBars(): void
    {
        $values = $this->getBarValues('AUSPOST', '1139549554');
        $this->assertSame('13', \substr($values, 0, 2));
        $this->assertSame('13', \substr($values, -2));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            // too short
            ['AUSPOST',   '113954955'],
            // unknown Format Control Code
            ['AUSPOST',   '1239549554'],
            // the sorting code is not a number
            ['AUSPOST',   '11ABCDEFGH'],
            // the null format needs a zero sorting code
            ['AUSPOST',   '0039549554'],
            // the customer information does not fit
            ['AUSPOST',   '5954516251ABC123'],
            // the standard format has no customer information field
            ['AUSPOST',   '1139549554A'],
            // not in the C encoding table
            ['AUSPOST',   '5954516251@'],
            // not in the N encoding table
            ['AUSPOST,N', '5954516251A'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidCodeProvider')]
    public function testInvalidInput(string $type, string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj($type, $code);
    }
}
