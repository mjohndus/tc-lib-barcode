<?php

/**
 * ItfOneFourTest.php
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
 * ITF-14 Barcode class test
 *
 * @since       2026-09-01
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class ItfOneFourTest extends TestUtil
{
    /**
     * Bearer bar thickness in modules
     */
    private const BEARER = 10;

    /**
     * Quiet zone width in modules
     */
    private const QUIET_ZONE = 20;

    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * Get the barcode grid as one string per row
     *
     * @return array<int, string>
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getGridRows(string $code): array
    {
        $grid = $this->getTestObject()->getBarcodeObj('ITF14', $code)->getGridArray();
        return \array_map(static fn(array $row): string => \implode('', $row), $grid);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            ['09312345678907', '06d165efe6aacc63493c7f4393ddbfe9'],
            ['0123456789012',  'df322d929097eeca45eb438437046c34'],
            ['1',              '1728fd7bb89090629a60a85963bb10e3'],
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
        $type = $barcode->getBarcodeObj('ITF14', $code);
        $this->assertEquals($expected, \md5($type->getGrid()));
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function extendedCodeProvider(): array
    {
        return [
            // full GTIN-14 with a valid check digit
            ['09312345678907', '09312345678907'],
            // 13 data digits, check digit appended
            ['0931234567890',  '09312345678907'],
            // shorter codes are left-padded with zeros
            ['1',              '00000000000017'],
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
        $this->assertSame($expected, $barcode->getBarcodeObj('ITF14', $code)->getExtendedCode());
    }

    /**
     * The symbol is 48 narrow and 29 wide elements at a 2.5:1 bar width ratio
     * (241 modules), enclosed by the quiet zones and the bearer bar frame.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testSymbolDimensions(): void
    {
        $barcode = $this->getTestObject();
        $data = $barcode->getBarcodeObj('ITF14', '09312345678907')->getArray();
        $this->assertSame(301, $data['ncols']);
        $this->assertSame(83, $data['nrows']);
        // 39 symbol bars and the 4 sides of the bearer bar frame
        $this->assertCount(43, $data['bars']);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testBearerBarFrame(): void
    {
        $rows = $this->getGridRows('09312345678907');
        $nrows = \count($rows);
        $solid = \str_repeat('1', \strlen($rows[0] ?? ''));
        $side = \str_repeat('1', self::BEARER);

        foreach ($rows as $idx => $row) {
            if ($idx < self::BEARER || $idx >= ($nrows - self::BEARER)) {
                $this->assertSame($solid, $row);
                continue;
            }

            $this->assertSame($side, \substr($row, 0, self::BEARER));
            $this->assertSame($side, \substr($row, -self::BEARER));
        }
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testQuietZones(): void
    {
        $rows = $this->getGridRows('09312345678907');
        $nrows = \count($rows);
        $blank = \str_repeat('0', self::QUIET_ZONE);

        foreach ($rows as $idx => $row) {
            if ($idx < self::BEARER || $idx >= ($nrows - self::BEARER)) {
                continue;
            }

            $this->assertSame($blank, \substr($row, self::BEARER, self::QUIET_ZONE));
            $this->assertSame($blank, \substr($row, -(self::BEARER + self::QUIET_ZONE), self::QUIET_ZONE));
        }
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('ITF14', 'GHI');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEmptyInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('ITF14', '');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCodeTooLong(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('ITF14', '093123456789071');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidCheckDigit(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('ITF14', '09312345678901');
    }
}
