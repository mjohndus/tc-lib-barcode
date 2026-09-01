<?php

/**
 * PlesseyTest.php
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
 * Plessey Code Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class PlesseyTest extends TestUtil
{
    /**
     * One bit is one pitch of twelve modules
     */
    private const PITCH = 12;

    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Read the bar widths off the symbol, in module counts
     *
     * @return list<int>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getBarWidths(string $code): array
    {
        $data = $this->getTestObject()->getBarcodeObj('PLESSEY', $code)->getArray();
        return \array_values(\array_map(static fn(array $bar): int => $bar[2], $data['bars']));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['1234567890ADCDEF', '5b12385f4bad1aa0cbacc06ac50cdf4a'],
            ['ABCDEF',           '3ec62a97ce7dfb2e53a332f81c12b3eb'],
            ['0',                'ed7f388ceae7f8fcccfec30abd847ed4'],
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
        $type = $barcode->getBarcodeObj('PLESSEY', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * The two check characters are the eight bits the generator polynomial
     * leaves after the data bits.
     *
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            // the published symbol, whose check code is 09
            ['1234567890ADCDEF', '1234567890ADCDEF09'],
            ['abcdef',           'ABCDEFF4'],
            ['0',                '000'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('PLESSEY', $code)->getExtendedCode());
    }

    /**
     * The symbol is the four bit forward start code, four bits per character,
     * eight check bits, the termination bar and the four bit reverse start
     * code, each bit one pitch wide.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('PLESSEY', '1234567890ADCDEF')->getArray();
        $bits = 4 + (4 * 18) + 4;
        $this->assertSame(($bits * self::PITCH) + self::PITCH, $data['ncols']);
        $this->assertSame(1, $data['nrows']);
        // one bar per bit and the termination bar
        $this->assertCount($bits + 1, $data['bars']);
    }

    /**
     * Every element is either narrow or wide, and the termination bar is one
     * full pitch.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testElementWidths(): void
    {
        $widths = $this->getBarWidths('1234567890ADCDEF');
        $terminator = 4 + (4 * 18);

        foreach ($widths as $idx => $width) {
            if ($idx === $terminator) {
                $this->assertSame(self::PITCH, $width);
                continue;
            }

            $this->assertContains($width, [2, 7]);
        }
    }

    /**
     * @return array<int, array{string}>
     */
    public static function invalidCodeProvider(): array
    {
        return [
            ['GHI'],
            [''],
            ['12 34'],
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
        $barcode->getBarcodeObj('PLESSEY', $code);
    }
}
