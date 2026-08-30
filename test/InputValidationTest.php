<?php

/**
 * InputValidationTest.php
 *
 * @since       2026-08-27
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Test;

use Com\Tecnick\Barcode\Barcode;
use Com\Tecnick\Barcode\Exception as BarcodeException;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalConvert;

/**
 * Payload validation tests
 *
 * @since       2026-08-27
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class InputValidationTest extends TestUtil
{
    protected function getTestObject(): Barcode
    {
        return new Barcode();
    }

    /**
     * Strings accepted by is_numeric() but not encodable by any EAN or UPC symbology.
     *
     * @return array<int, array{string, string}>
     */
    public static function nonDigitNumericProvider(): array
    {
        return [
            ['EAN13', '-5'],
            ['EAN13', '1e5'],
            ['EAN13', '1.5'],
            ['EAN13', ' 123'],
            ['EAN13', '+12'],
            ['EAN8', '-5'],
            ['EAN8', '1.5'],
            ['EAN2', 'ab'],
            ['EAN5', 'xy'],
            ['UPCA', '1.5'],
            ['UPCE', '1e5'],
        ];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('nonDigitNumericProvider')]
    public function testNonDigitNumericPayloadIsRejected(string $type, string $code): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj($type, $code);
    }

    /**
     * Payloads longer than the fixed length of the symbology.
     *
     * @return array<int, array{string, string}>
     */
    public static function overLongCodeProvider(): array
    {
        return [
            ['EAN13', '01234567890123456782'],
            ['EAN8',  '123456789'],
            ['EAN2',  '12345'],
            ['EAN5',  '1234567'],
            ['UPCA',  '12345678901234'],
            ['UPCE',  '1234567890123'],
        ];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('overLongCodeProvider')]
    public function testOverLongPayloadIsRejected(string $type, string $code): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj($type, $code);
    }

    /**
     * A code that already carries its check digit must not get a second one appended.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testUpcaDoesNotAppendASpuriousCheckDigit(): void
    {
        $bobj = $this->getTestObject()->getBarcodeObj('UPCA', '012345678905');
        $this->assertSame('0012345678905', $bobj->getExtendedCode());

        $bobj = $this->getTestObject()->getBarcodeObj('UPCA', '01234567890');
        $this->assertSame('0012345678905', $bobj->getExtendedCode());
    }

    /**
     * Payloads whose modulo 11 check digit is 10 cannot be encoded as a single MSI character.
     *
     * @return array<int, array{string}>
     */
    public static function msiUnrepresentableCheckDigitProvider(): array
    {
        return [['6'], ['23'], ['37'], ['40'], ['99']];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('msiUnrepresentableCheckDigitProvider')]
    public function testMsiRejectsUnrepresentableCheckDigit(string $code): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj('MSI+', $code);
    }

    /**
     * MSI without the checksum suffix is unaffected by the check digit restriction.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testMsiWithoutChecksumAcceptsAnyDigits(): void
    {
        $bobj = $this->getTestObject()->getBarcodeObj('MSI', '6');
        $this->assertSame('6', $bobj->getExtendedCode());
    }

    /**
     * Pharmacode rejects anything that is not a positive integer, with a message
     * that names the payload rather than the bar generation stage.
     *
     * @return array<int, array{string}>
     */
    public static function invalidPharmaProvider(): array
    {
        return [[''], ['abc'], ['0'], ['3.7'], ['-5']];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('invalidPharmaProvider')]
    public function testPharmaRejectsNonPositiveIntegers(string $code): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj('PHARMA', $code);
    }

    /**
     * An empty CODE 128 payload emits no PHP warning while auto-selecting the subset.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEmptyCodeOneTwoEightDoesNotWarn(): void
    {
        $bobj = $this->getTestObject()->getBarcodeObj('C128', '');
        $this->assertSame(35, $bobj->getArray()['ncols']);
    }

    /**
     * A padding array must be a plain list; associative keys are a BarcodeException,
     * not an UnhandledMatchError.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testAssociativePaddingIsRejected(): void
    {
        $this->bcExpectException(BarcodeException::class);
        /** @var array{0: int, 1: int, 2: int, 3: int} $invalidPadding */
        $invalidPadding = [
            'T' => 1,
            'R' => 1,
            'B' => 1,
            'L' => 1,
        ];
        $this->getTestObject()->getBarcodeObj('C39', 'TEST', -1, -1, 'black', $invalidPadding);
    }

    /**
     * The rotated bar array of a barcode with a single row is empty.
     */
    public function testRotatedBarArrayOnASingleRow(): void
    {
        $conv = new InternalConvert();
        $conv->setColsRows(4, 1);
        $conv->setBars([[0, 0, 2, 1]]);

        $this->assertSame([], $conv->exposeGetRotatedBarArray());
    }

    /**
     * The asterisk is the CODE 39 start/stop character and is rejected in the payload.
     *
     * @return array<int, array{string, string}>
     */
    public static function codeThreeNineStartStopProvider(): array
    {
        return [
            ['C39', 'A*B'],
            ['C39', '*'],
            ['C39', 'AB*'],
            ['C39', '*AB'],
            ['C39+', 'A*B'],
            ['C39+', '*'],
        ];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('codeThreeNineStartStopProvider')]
    public function testCodeThreeNineRejectsTheStartStopCharacter(string $type, string $code): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj($type, $code);
    }

    /**
     * The extended variants escape the asterisk, so they accept it.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testExtendedCodeThreeNineEscapesTheAsterisk(): void
    {
        $barcode = $this->getTestObject();
        $this->assertSame('*A/JB*', $barcode->getBarcodeObj('C39E', 'A*B')->getExtendedCode());
    }

    /**
     * A, B, C and D are the Codabar start/stop characters and may not appear in the payload.
     *
     * @return array<int, array{string}>
     */
    public static function codabarStartStopProvider(): array
    {
        return [['A123456A'], ['B1234C'], ['abcd'], ['1A2'], ['D']];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('codabarStartStopProvider')]
    public function testCodabarRejectsStartStopCharacters(string $code): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj('CODABAR', $code);
    }

    /**
     * Extended CODE 39 and CODE 93 encode the full ASCII set, lowercase included.
     *
     * @return array<int, array{string, string, string}>
     */
    public static function extendedLowercaseProvider(): array
    {
        return [
            ['C39E',  'abc', '*+A+B+C*'],
            ['C39E+', 'abc', '*+A+B+CR*'],
            ['C93',   'abc', "*\x82A\x82B\x82C-8*"],
        ];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('extendedLowercaseProvider')]
    public function testExtendedTypesPreserveLowercase(string $type, string $code, string $expected): void
    {
        $bobj = $this->getTestObject()->getBarcodeObj($type, $code);
        $this->assertSame($expected, $bobj->getExtendedCode());
        $this->assertNotSame(
            $bobj->getGrid(),
            $this
                ->getTestObject()
                ->getBarcodeObj($type, \strtoupper($code))
                ->getGrid(),
        );
    }

    /**
     * Plain CODE 39 has no lowercase in its character set and keeps folding the payload.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testPlainCodeThreeNineStillFoldsToUppercase(): void
    {
        $this->assertSame('*ABC*', $this->getTestObject()->getBarcodeObj('C39', 'abc')->getExtendedCode());
    }

    /**
     * Standard 2 of 5 encodes each digit on its own: an odd-length payload must not gain
     * the leading zero that the interleaved variant needs.
     *
     * @return array<int, array{string, string, string}>
     */
    public static function standardTwoOfFiveProvider(): array
    {
        return [
            ['S25',  '12345',      '12345'],
            ['S25',  '1234',       '1234'],
            ['S25',  '0123456789', '0123456789'],
            ['S25+', '12345',      '123457'],
            ['S25+', '1234',       '12342'],
            ['S25+', '0123456789', '01234567895'],
        ];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('standardTwoOfFiveProvider')]
    public function testStandardTwoOfFiveIsNotPadded(string $type, string $code, string $expected): void
    {
        $this->assertSame($expected, $this->getTestObject()->getBarcodeObj($type, $code)->getExtendedCode());
    }

    /**
     * The interleaved variant keeps its own even-length padding.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInterleavedTwoOfFiveIsStillPadded(): void
    {
        $extcode = $this->getTestObject()->getBarcodeObj('I25', '12345')->getExtendedCode();
        $this->assertSame('AA012345ZA', $extcode);
    }

    /**
     * Raw payloads whose rows do not all have the same length.
     *
     * @return array<int, array{string, string}>
     */
    public static function raggedRawProvider(): array
    {
        return [
            ['LRAW', '1,111'],
            ['LRAW', '111,1'],
            ['SRAW', '101,1101,10'],
            ['SRAW', '0101,1010,110,0011'],
        ];
    }

    /**
     * A raw payload whose rows do not all have the same length is rejected.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('raggedRawProvider')]
    public function testRawRejectsRowsOfDifferentLength(string $type, string $code): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj($type, $code);
    }

    /**
     * Six-digit UPC-E payloads that are not the canonical compression of their own UPC-A.
     * The encoder expands them and re-compresses, so the symbol carries the same UPC-A.
     *
     * @return array<int, array{string, string}>
     */
    public static function nonCanonicalUpceProvider(): array
    {
        return [
            ['123607', '0012360000071'],
            ['280453', '0028000000455'],
            ['042293', '0004200000297'],
        ];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('nonCanonicalUpceProvider')]
    public function testUpceNormalisesANonCanonicalPayload(string $code, string $expected): void
    {
        $this->assertSame($expected, $this->getTestObject()->getBarcodeObj('UPCE', $code)->getExtendedCode());
    }

    /**
     * Raw payloads containing a character other than 0 or 1.
     *
     * @return array<int, array{string, string}>
     */
    public static function nonBinaryRawProvider(): array
    {
        return [
            ['LRAW', '1a1'],
            ['LRAW', '0121'],
            ['LRAW', 'abc'],
            ['LRAW', '1011,10-1'],
            ['SRAW', '0101,10x0'],
            ['SRAW', '2222'],
        ];
    }

    /**
     * A raw payload character other than 0 or 1 is rejected.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('nonBinaryRawProvider')]
    public function testRawRejectsNonBinaryCharacters(string $type, string $code): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj($type, $code);
    }

    /**
     * Macro control block fields that Text or Numeric Compaction cannot represent.
     *
     * @return array<int, array{string}>
     */
    public static function pdfMacroFieldProvider(): array
    {
        return [
            // the sub-mode sentinel values of the text table
            ['PDF417,2,0,3,1,AB' . "\xfb" . 'CD'],
            ['PDF417,2,0,3,1,AB' . "\xfd" . 'CD'],
            ['PDF417,2,0,3,1,AB' . "\xfe" . 'CD'],
            // characters outside the text compaction set
            ['PDF417,2,0,3,1,AB' . "\x00" . 'CD'],
            ['PDF417,2,0,3,1,AB' . "\x80" . 'CD'],
            // the segment index is a fixed two codeword field
            ['PDF417,2,0,200000,100001,ABC'],
            ['PDF417,2,0,3,100000,ABC'],
            ['PDF417,2,0,x,1,ABC'],
            // the numeric optional fields must be digits
            ['PDF417,2,0,3,1,ABC,,ZZZ'],
            ['PDF417,2,0,3,1,ABC,,,notatimestamp'],
            ['PDF417,2,0,3,1,ABC,,,,,,notasize'],
            // the checksum is a fixed two codeword field
            ['PDF417,2,0,3,1,ABC,,,,,,,100000'],
            ['PDF417,2,0,3,1,ABC,,,,,,,notachecksum'],
        ];
    }

    /**
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('pdfMacroFieldProvider')]
    public function testPdfFourOneSevenRejectsInvalidMacroFields(string $type): void
    {
        $this->bcExpectException(BarcodeException::class);
        $this->getTestObject()->getBarcodeObj($type, 'HELLO');
    }

    /**
     * A valid macro control block is still accepted.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testPdfFourOneSevenAcceptsValidMacroFields(): void
    {
        $type = $this->getTestObject()->getBarcodeObj(
            'PDF417,2,0,3,1,ABC,file.txt,00042,1700000000,me,you,12345,99999',
            'HELLO',
        );

        $this->assertSame('PDF417', $type->getArray()['format']);
    }

    /**
     * A payload that is not valid UTF-8 must not empty the SVG description.
     *
     * @throws BarcodeException
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInlineSvgKeepsTheDescriptionOfABinaryPayload(): void
    {
        $svg = $this->getTestObject()->getBarcodeObj('QRCODE,H', "\xff\xfeAB")->getInlineSvgCode();
        $match = [];

        $this->assertSame(1, \preg_match('#<desc>(.*)</desc>#', $svg, $match));
        $this->assertNotSame('', $match[1] ?? '');
        $this->assertNotFalse(\simplexml_load_string($svg));
    }
}
