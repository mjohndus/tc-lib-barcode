<?php

/**
 * JapanPostTest.php
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
 * Japan Post Customer Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class JapanPostTest extends TestUtil
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
    protected function getBarValues(string $code): string
    {
        $grid = $this->getTestObject()->getBarcodeObj('JPPOST', $code)->getGridArray();
        $values = '';
        $ncols = \count($grid[1] ?? []);
        for ($col = 0; $col < $ncols; $col += 2) {
            $upper = ($grid[0][$col] ?? '0') === '1';
            $lower = ($grid[2][$col] ?? '0') === '1';
            $values .= match (true) {
                $upper && $lower => '1',
                $upper => '2',
                $lower => '3',
                default => '4',
            };
        }

        return $values;
    }

    /**
     * The worked examples of the manual, whose customer barcodes are printed
     * with the control codes spelled out.
     *
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            // the address runs past the thirteen positions, so the last letter
            // keeps its control code and loses its digit
            ['910-00673-80-25J1-2B',     '91000673-80-25CC191-2CC19'],
            // the hyphen of the postal code is optional
            ['91000673-80-25J1-2B',      '91000673-80-25CC191-2CC19'],
            // the address is cut after the twentieth position
            ['064-080429-1524-23-2-501', '064080429-1524-23-2-3'],
            // the check digit of the霞が関 example of the manual
            ['10000131-3-2-503',         '10000131-3-2-503CC4CC4CC4CC49'],
            // a large business postal code with no address
            ['100-8792',                 '1008792CC4CC4CC4CC4CC4CC4CC4CC4CC4CC4CC4CC4CC40'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('extendedCodeProvider')]
    public function testGetExtendedCode(string $code, string $expected): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame($expected, $barcode->getBarcodeObj('JPPOST', $code)->getExtendedCode());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['910-00673-80-25J1-2B',     '98985ccc3beef57ea3c5aeab8ad30228'],
            ['3170055 6-7-14-2',         'a8168f651ec8d66cffe222a24e798e59'],
            ['064-080429-1524-23-2-501', 'ea5e009a1ab8cc3701aeeee06b478204'],
            ['100-8792',                 '50ea178f48593cab4d2adaac850d1adb'],
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
        $type = $barcode->getBarcodeObj('JPPOST', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The symbol is the start code, seven postal code characters, thirteen
     * address characters, the check digit and the stop code, three bars each
     * except the start and stop codes.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('JPPOST', '3170055 6-7-14-2')->getArray();
        $this->assertCount(2 + (3 * 21) + 2, $data['bars']);
        $this->assertSame(133, $data['ncols']);
        $this->assertSame(3, $data['nrows']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testStartAndStopCodes(): void
    {
        $values = $this->getBarValues('3170055 6-7-14-2');
        $this->assertSame('13', \substr($values, 0, 2));
        $this->assertSame('31', \substr($values, -2));
    }

    /**
     * The check values of the whole symbol sum to a multiple of 19.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('extendedCodeProvider')]
    public function testChecksumIsAMultipleOfTheModulus(string $code, string $expected): void
    {
        $values = [
            '0' => 0,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '-' => 10,
            'CC1' => 11,
            'CC2' => 12,
            'CC3' => 13,
            'CC4' => 14,
            'CC5' => 15,
            'CC6' => 16,
            'CC7' => 17,
            'CC8' => 18,
        ];

        $barcode = $this->getTestObject();
        $extcode = $barcode->getBarcodeObj('JPPOST', $code)->getExtendedCode();
        $this->assertSame($expected, $extcode);

        $sum = 0;
        $matches = [];
        \preg_match_all('/CC[1-8]|./', $extcode, $matches);
        foreach ($matches[0] ?? [] as $char) {
            $sum += $values[$char] ?? 0;
        }

        $this->assertSame(0, $sum % 19);
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            // the postal code is too short
            ['317005'],
            // the postal code is not a number
            ['31700AB'],
            // empty
            [''],
            // not in the character set
            ['3170055*'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidCodeProvider')]
    public function testInvalidInput(string $code): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('JPPOST', $code);
    }
}
