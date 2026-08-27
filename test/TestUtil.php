<?php

/**
 * TestUtil.php
 *
 * @since       2020-12-19
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-color software library.
 */

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * Base test case with the assertion and output capture helpers used by the test suite.
 *
 * @since       2020-12-19
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * @SuppressWarnings("PHPMD.NumberOfChildren")
 */
class TestUtil extends TestCase
{
    public function bcAssertEqualsWithDelta(
        mixed $expected,
        mixed $actual,
        float $delta = 0.01,
        string $message = '',
    ): void {
        parent::assertEqualsWithDelta($expected, $actual, $delta, $message);
    }

    /**
     * @param class-string<\Throwable> $exception
     */
    public function bcExpectException(string $exception): void
    {
        if (!\is_a($exception, \Throwable::class, true)) {
            self::fail('Expected a throwable class name.');
        }

        parent::expectException($exception);
    }

    /**
     * Returns the response headers sent so far, or null when the SAPI does not expose them.
     * The CLI SAPI discards the headers, so they are only readable through Xdebug.
     *
     * @return array<int, string>|null
     */
    protected function getResponseHeaders(): ?array
    {
        if (\function_exists('xdebug_get_headers')) {
            /** @var list<string> $rawHeaders */
            $rawHeaders = xdebug_get_headers();
            $headers = [];
            foreach ($rawHeaders as $header) {
                $headers[] = $header;
            }

            return $headers;
        }

        $headers = \headers_list();

        return $headers === [] ? null : $headers;
    }

    /**
     * Asserts the file name in the last Content-Disposition header.
     * The assertion is skipped when the response headers are not readable.
     */
    protected function assertContentDisposition(string $filename, string $fileext): void
    {
        $headers = $this->getResponseHeaders();
        if ($headers === null) {
            return;
        }

        $disposition = '';
        foreach ($headers as $header) {
            if (!\str_starts_with($header, 'Content-Disposition:')) {
                continue;
            }

            $disposition = $header;
        }

        $this->assertSame('Content-Disposition: inline; filename="' . $filename . '.' . $fileext . '";', $disposition);
    }
}
