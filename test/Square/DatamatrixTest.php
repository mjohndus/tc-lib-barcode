<?php

/**
 * DatamatrixTest.php
 *
 * @since       2015-02-21
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

use Com\Tecnick\Barcode\Type\Square\Datamatrix\Data;
use Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\Fixture\InternalDatamatrixEncode;
use Test\TestUtil;

/**
 * Datamatrix Barcode class test
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 */
class DatamatrixTest extends TestUtil
{
    protected function getTestObject(): \Com\Tecnick\Barcode\Barcode
    {
        return new \Com\Tecnick\Barcode\Barcode();
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testInvalidInput(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $barcode->getBarcodeObj('DATAMATRIX', '');
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testCapacityException(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $barcode = $this->getTestObject();
        $code = \str_pad('', 3000, 'X');
        $barcode->getBarcodeObj('DATAMATRIX', $code);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEncodeTXTC40shiftException(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);
        $encode = new \Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode();
        $chr = -1;
        $enc = -1;
        $temp_cw = [];
        $ptr = 0;
        $encode->encodeTXTC40shift($chr, $enc, $temp_cw, $ptr);
    }

    /**
     * X12 has no upper shift, so an extended character is reported as not representable.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEncodeTXTC40UnsupportedCharacter(): void
    {
        $encode = new \Com\Tecnick\Barcode\Type\Square\Datamatrix\Encode();
        $data = "\x80";
        $enc = \Com\Tecnick\Barcode\Type\Square\Datamatrix\Data::ENC_X12;
        $temp_cw = [];
        $ptr = 0;
        $epos = 0;
        $charset = [];
        $this->assertNull($encode->encodeTXTC40($data, $enc, $temp_cw, $ptr, $epos, $charset));
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getGridDataProvider')]
    public function testGetGrid(string $mode, string $code, mixed $expected): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj($mode, $code);
        $grid = $type->getGrid();
        $this->assertEquals($expected, \md5($grid));
    }

    /**
     * @return array<array{string, string, string}>
     */
    public static function getGridDataProvider(): array
    {
        return [
            [
                'DATAMATRIX',
                '0&0&0&0&0&0&_',
                'fffdfdaec33af0788d24cdfa8cba5ac6',
            ],
            [
                'DATAMATRIX',
                '0&0&0&0&0&0&0',
                '10d0faf5a6e7b71829f268218df7e6af',
            ],
            [
                'DATAMATRIX',
                '-=-1-=-2-=-3',
                '75c6038d90476cec641ad07690989b36',
            ],
            [
                'DATAMATRIX',
                '-=-1-=-2-=-3x',
                'f020e44d0926d17af7eb21febdb38d53',
            ],
            [
                'DATAMATRIX',
                '-=-1-=-2-=-3xyz',
                'e7dc0b6fc4a831870da809aee5faa953',
            ],
            [
                'DATAMATRIX',
                '-=-1-=-2-=-3-',
                'a63372ce839b51294964f0da0ae0f9f9',
            ],
            [
                'DATAMATRIX',
                '-=-1-=-2-=-3-xy',
                '6e1292fa6ba488b399f4f7960c78e24c',
            ],
            [
                'DATAMATRIX',
                '-=-1-=-2-=-3-=x',
                '4986c43654745b099c5984fcbececa2f',
            ],
            [
                'DATAMATRIX',
                '(400)BS2WZ64PA(00)0',
                'c537785b8f7d3a177251a9daf48d0dd7',
            ],
            [
                'DATAMATRIX',
                '(400)BS2WZ64QA(00)0',
                '3d89f4a5a6b62c672b5282768922be02',
            ],
            [
                'DATAMATRIX',
                'LD2B 1 CLNGP',
                '84bb0830247749b44bcca686316c5548',
            ],
            [
                'DATAMATRIX',
                'XXXXXXXXXNGP',
                '8d9734dfbdf5f4ea195b195bfc84d7fc',
            ],
            [
                'DATAMATRIX',
                'XXXXXXXXXXXXNGP',
                'f7679d5a7ab4a8edf12571a6866d92bc',
            ],
            [
                'DATAMATRIX',
                'ABCDABCDAB' . "\x80" . 'DABCD',
                '5cce4229a847305a6d9f7c7de7ca6b88',
            ],
            [
                'DATAMATRIX',
                '123aabcdefghijklmnopqrstuvwxyzc',
                'b2d1e957af10655d7a8c3bae86696314',
            ],
            [
                'DATAMATRIX',
                'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopq',
                'c45bd372694ad7a20fca7d45f3d459ab',
            ],
            [
                'DATAMATRIX',
                'abcdefghijklmnop',
                '4fc7940fe3d19fca12454340c38e3421',
            ],
            [
                'DATAMATRIX',
                'abcdefghijklmnopq',
                '8b57b1beb3235c89331a6eccb8c7ecf6',
            ],
            [
                'DATAMATRIX',
                'abcdefghij',
                '8ec27153e5d173aa2cb907845334e68c',
            ],
            [
                'DATAMATRIX',
                '30Q324343430794<OQQ',
                'e67808f91114fb021851098c4cc65b88',
            ],
            [
                'DATAMATRIX',
                '0123456789',
                'cc1fd942bc919b2d09b3c7cf508c6ae4',
            ],
            [
                'DATAMATRIX',
                'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                '8880d59dc48f0fa1b8e2383b5f9eb0d8',
            ],
            [
                'DATAMATRIX',
                '10f27ce-acb7-4e4e-a7ae-a0b98da6ed4a',
                '28b6d99132f3d722069b4b4a7afb68b9',
            ],
            [
                'DATAMATRIX',
                'Hello World',
                'e72650689027fe75d1f9377ec759c710',
            ],
            [
                'DATAMATRIX',
                'https://github.com/tecnickcom/tc-lib-barcode',
                'efed64acfa2ca29024446fa9816be696',
            ],
            [
                'DATAMATRIX',
                'abcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdab'
                    . 'cdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcd'
                    . 'abcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcdab'
                    . 'cdabcdabcdabcdabcdabcdabcdabcdabcdabcdabcd',
                'f28374760686e6885756003a7b5a4df1',
            ],
            [
                'DATAMATRIX',
                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*(),./\\',
                'd1851da5d9b464e83813f8ea5c5b1b2f',
            ],
            [
                'DATAMATRIX',
                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*(),./\\'
                    . 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*(),./\\'
                    . 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*(),./\\',
                '0b2921466e097ff9cc1ad63719430540',
            ],
            [
                'DATAMATRIX',
                "\x80\x8A\x94\x9E",
                '588825d8728b1d5713895a0a7d7cbdee',
            ],
            [
                'DATAMATRIX',
                '!"£$%^&*()-+_={}[]\'#@~;:/?,.<>|',
                '74f87d70754db2ffad1d5ca6c33467b5',
            ],
            [
                'DATAMATRIX',
                '!"£$',
                '792181edb48c6722217dc7e2e4cd4095',
            ],
            [
                'DATAMATRIX',
                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*(),./\\1234567890',
                'df68782f52deeefa9ce5687aa7ad397a',
            ],
            [
                'DATAMATRIX',
                "\xFE\xFD"
                    . 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*(),./\\'
                    . "\xFC\xFB",
                '61dcd419f072e1bfc8fcd2661c15cf0f',
            ],
            [
                'DATAMATRIX',
                'aABCDEFG',
                'f074dee3f0f386d9b2f30b1ce4ad08a8',
            ],
            [
                'DATAMATRIX',
                '123 45678',
                '09db7564f5f542aa4aadfa6753c81a50',
            ],
            [
                'DATAMATRIX',
                'DATA MATRIX',
                '76483bb617a63892d4d238aafb1e3299',
            ],
            [
                'DATAMATRIX',
                '123ABCD89',
                '7ce2f8433b82c16e80f4a4c59cad5d10',
            ],
            [
                'DATAMATRIX',
                'AB/C123-X',
                '703318e1964c63d5d500d14a821827cd',
            ],
            [
                'DATAMATRIX',
                \str_pad('', 300, "\xFE\xFD\xFC\xFB"),
                '2993e5a7849083e9bf7cc4f5db9966ba',
            ],
            [
                'DATAMATRIX',
                'ec:b47'
                    . "\x7F"
                    . '4#P d*b}gI2#DB|hl{!~[EYH*=cmR{lf'
                    . "\x7F"
                    . '=gcGIa.st286. #*"!eG[.Ryr?Kn,1mIyQqC3 6\'3N>',
                '5a4f396e0665fde2fd60c2e4db713e98',
            ],
            [
                'DATAMATRIX',
                'eA211101A2raJTGL/r9o93CVk4gtpEvWd2A2Qz8jvPc7l8ybD3m'
                    . 'Wel91ih727kldinPeHJCjhr7fIBX1KQQfsN7BFMX00nlS8FlZG+',
                '0d6ca088f1a9a315177f7b07a8362a6a',
            ],
            // Square
            [
                'DATAMATRIX,S',
                "\xFF\xFE\xFD\xFC\xFB\xFA\xF9\xF8\xF7\xF6"
                    . "\xF5\xF4\xF3\xF2\xF1\xF0\xEF\xEE\xED\xEC"
                    . "\xEB\xEA\xE9\xE8\xE7\xE6\xE5\xE4\xE3\xE2"
                    . "\xE1\xE0\xDF\xDE\xDD\xDC\xDB\xDA\xD9\xD8"
                    . "\xD7\xD6\xD5\xD4\xD3\xD2\xD1\xD0\xCF\xCE"
                    . "\xCD\xCC\xCB\xCA\xC9\xC8\xC7\xC6\xC5\xC4"
                    . "\xC3\xC2\xC1\xC0\xBF\xBE\xBD\xBC\xBB\xBA"
                    . "\xB9\xB8\xB7\xB6\xB5\xB4\xB3\xB2\xB1\xB0"
                    . "\xAF\xAE\xAD\xAC\xAB\xAA\xA9\xA8\xA7\xA6"
                    . "\xA5\xA4\xA3\xA2\xA1\xA0\x9F\x9E\x9D\x9C"
                    . "\x9B\x9A\x99\x98\x97\x96\x95\x94\x93\x92"
                    . "\x91\x90\x8F\x8E\x8D\x8C\x8B\x8A\x89\x88"
                    . "\x87\x86\x85\x84\x83\x82\x81\x80\x7F\x7E"
                    . "\x7D\x7C\x7B\x7A\x79\x78\x77\x76\x75\x74"
                    . "\x73\x72\x71\x70\x6F\x6E\x6D\x6C\x6B\x6A"
                    . "\x69\x68\x67\x66\x65\x64\x63\x62\x61\x60"
                    . "\x5F\x5E\x5D\x5C\x5B\x5A\x59\x58\x57\x56"
                    . "\x55\x54\x53\x52\x51\x50\x4F\x4E\x4D\x4C"
                    . "\x4B\x4A\x49\x48\x47\x46\x45\x44\x43\x42"
                    . "\x41\x40\x3F\x3E\x3D\x3C\x3B\x3A\x39\x38"
                    . "\x37\x36\x35\x34\x33\x32\x31\x30\x2F\x2E"
                    . "\x2D\x2C\x2B\x2A\x29\x28\x27\x26\x25\x24"
                    . "\x23\x22\x21\x20\x1F\x1E\x1D\x1C\x1B\x1A"
                    . "\x19\x18\x17\x16\x15\x14\x13\x12\x11\x10"
                    . "\x0F\x0E\x0D\x0C\x0B\x0A\x09\x08\x07\x06"
                    . "\x05\x04\x03\x02\x01",
                'aa6c03b963fce1b10fb2e67921051e4c',
            ],
            // Rectangular shape
            [
                'DATAMATRIX,R',
                '01234567890',
                'f16a61c029231d0aa652157d06a66f2b',
            ],
            [
                'DATAMATRIX,R',
                '01234567890123456789',
                'fe3ecb042dabc4b40c5017e204df105b',
            ],
            [
                'DATAMATRIX,R',
                '012345678901234567890123456789',
                '3f8e9aa4413b90f7e1c2e85b4471fd20',
            ],
            [
                'DATAMATRIX,R',
                '0123456789012345678901234567890123456789',
                '017864b3d4760515ea9699dcc11121c3',
            ],
            // Rectangular GS1
            [
                'DATAMATRIX,R,GS1',
                "\xE8" . '01034531200000111719112510ABCD1234',
                '5de4104b51e900d8534c97f0fa3f5c0d',
            ],
            [
                'DATAMATRIX,R,GS1',
                "\xE8" . '01095011010209171719050810ABCD1234' . "\xE8" . '2110',
                'c1775e04af119c0c07883aa0c9589337',
            ],
            [
                'DATAMATRIX,R,GS1',
                "\xE8" . '01034531200000111712050810ABCD1234' . "\xE8" . '4109501101020917',
                'f5a635e36fe0e2b9cb2837bfff280888',
            ],
            // Square GS1
            [
                'DATAMATRIX,S,GS1',
                "\xE8" . '01034531200000111719112510ABCD1234',
                '429c1cfc77ce2f78e27d51112070a248',
            ],
            [
                'DATAMATRIX,S,GS1',
                "\xE8" . '01095011010209171719050810ABCD1234' . "\xE8" . '2110',
                '143d6e6b7410fb74b1b4d519fbd03036',
            ],
            [
                'DATAMATRIX,S,GS1',
                "\xE8" . '01034531200000111712050810ABCD1234' . "\xE8" . '4109501101020917',
                'a29a330a01cce34a346cf7049e2259ee',
            ],
            // Different encoding datamatrix
            [
                'DATAMATRIX,S,N,ASCII',
                '01234567890',
                'dc57d0b736d97cb22f0d741a0dbfd9a5',
            ],
            [
                'DATAMATRIX,S,N,C40',
                '01234567890',
                '958a7a3bcd036d7135489eb703a25633',
            ],
            [
                'DATAMATRIX,S,N,TXT',
                '01234567890',
                '057981dfbf527b029ae59d65fb55f61d',
            ],
            [
                'DATAMATRIX,S,N,X12',
                '01234567890',
                '8d75b0fcfb2d0977abd95004a6ba98dd',
            ],
            [
                'DATAMATRIX,S,N,EDF',
                '01234567890',
                '1d5cd9b3c0e25d06c529e9e7b579d492',
            ],
            [
                'DATAMATRIX,S,N,BASE256',
                '01234567890',
                '44d252cfc62e5a893c9d37bef5afca53',
            ],
            [
                'DATAMATRIX,S,GS1',
                // \xE8 is the control character FNC1 (ASCII 232)
                // Expected read:
                //     (01)03453120000011
                //     (17)191125
                //     (10)ABCD1234
                //     (21)10
                "\xE8" . '01034531200000111719112510ABCD1234' . "\xE8" . '2110',
                '7df3c201ec672bfe15abaf6b13a827ad',
            ],
            [
                'DATAMATRIX,S,GS1',
                // \xE8 is the control character FNC1 (ASCII 232)
                // \x1D is the control character <GS> (ASCII 29)
                // Expected read:
                //     (01)03453120000011
                //     (17)191125
                //     (10)ABCD1234
                //     (21)10
                "\xE8" . '01034531200000111719112510ABCD1234' . "\x1D" . '2110',
                '7df3c201ec672bfe15abaf6b13a827ad',
            ],
            [
                'DATAMATRIX,S,GS1,C40',
                // \xE8 is the control character FNC1 (ASCII 232)
                "\xE8" . '01095011010209171719050810ABCD1234' . "\xE8" . '2110',
                'ac53a192b50451f00c9452254b7e0201',
            ],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('getStringDataProvider')]
    public function testStrings(string $code): void
    {
        $barcode = $this->getTestObject();
        $type = $barcode->getBarcodeObj('DATAMATRIX', $code);
        $this->assertNotNull($type); // @phpstan-ignore method.alreadyNarrowedType
    }

    /**
     * @return array<array{string}>
     */
    public static function getStringDataProvider(): array
    {
        return \Test\TestStrings::$data;
    }

    /**
     * The look-ahead test of Annex P of ISO/IEC 16022, which picks the
     * encodation that spends the fewest codewords on the data that follows.
     *
     * @return array<string, array{string, int, int, int}>
     */
    public static function lookAheadProvider(): array
    {
        return [
            // the digits stay in ASCII, which packs a pair into one codeword
            'digits from ascii' => ['12345678', 0, Data::ENC_ASCII, Data::ENC_ASCII],
            'digits from c40' => ['12345678', 0, Data::ENC_C40, Data::ENC_ASCII],
            'digits from text' => ['12345678', 0, Data::ENC_TXT, Data::ENC_ASCII],
            'digits from x12' => ['12345678', 0, Data::ENC_X12, Data::ENC_ASCII],
            'digits from edifact' => ['12345678', 0, Data::ENC_EDF, Data::ENC_ASCII],
            'digits from base 256' => ['12345678', 0, Data::ENC_BASE256, Data::ENC_ASCII],
            // the lower case letters belong to the Text set
            'lower case from ascii' => ['abcdefgh', 0, Data::ENC_ASCII, Data::ENC_TXT],
            'lower case from x12' => ['abcdefgh', 0, Data::ENC_X12, Data::ENC_TXT],
            'lower case from edifact' => ['abcdefgh', 0, Data::ENC_EDF, Data::ENC_TXT],
            // the upper case letters, the digits, the space, the asterisk, the
            // greater than sign and the carriage return belong to the X12 set
            'x12 set from ascii' => ['AB*CD>EF', 0, Data::ENC_ASCII, Data::ENC_X12],
            'x12 set from x12' => ['AB*CD>EF', 0, Data::ENC_X12, Data::ENC_X12],
            'x12 set from edifact' => ["ABC\rDEF\r", 0, Data::ENC_EDF, Data::ENC_X12],
            // the extended characters go to Base 256
            'extended from ascii' => ["\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7", 0, Data::ENC_ASCII, Data::ENC_BASE256],
            'extended from edifact' => ["\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7", 0, Data::ENC_EDF, Data::ENC_BASE256],
            // the current encodation is kept when nothing follows
            'end of data from edifact' => ['ABCDEF', 6, Data::ENC_EDF, Data::ENC_EDF],
            'end of data from ascii' => ['ABCDEF', 6, Data::ENC_ASCII, Data::ENC_ASCII],
            // C40 and X12 spend the same number of codewords, and the
            // characters that follow the tie are of the X12 set
            'c40 and x12 tie' => ['F3C 00AEE12A**', 0, Data::ENC_TXT, Data::ENC_X12],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    #[DataProvider('lookAheadProvider')]
    public function testLookAheadTest(string $data, int $pos, int $mode, int $expected): void
    {
        $encode = new Encode();

        $this->assertSame($expected, $encode->lookAheadTest($data, $pos, $mode));
    }

    /**
     * A Base 256 field of zero length runs to the end of the symbol, so no
     * length codeword is written when there is nothing left to encode.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testEncodeBase256AtTheEndOfTheData(): void
    {
        $encode = new Encode();
        $cdw = [];
        $cdw_num = 0;
        $data = 'ABC';
        $pos = 3;
        $data_length = 3;
        $field_length = 0;
        $enc = Data::ENC_BASE256;

        $encode->encodeBase256($cdw, $cdw_num, $pos, $data_length, $field_length, $data, $enc);

        $this->assertSame([], $cdw);
        $this->assertSame(0, $cdw_num);
        $this->assertSame(0, $field_length);
    }

    /**
     * The codeword buffer of a partial triple is taken one value at a time, and
     * an empty buffer yields no value.
     *
     * @throws \Com\Tecnick\Barcode\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    public function testShiftTempCw(): void
    {
        $encode = new InternalDatamatrixEncode();
        $temp_cw = [3, 1, 4];

        $this->assertSame(3, $encode->exposeShiftTempCw($temp_cw));
        $this->assertSame([1, 4], $temp_cw);
        $this->assertSame(1, $encode->exposeShiftTempCw($temp_cw));
        $this->assertSame(4, $encode->exposeShiftTempCw($temp_cw));
        $this->assertSame([], $temp_cw);
        $this->assertSame(0, $encode->exposeShiftTempCw($temp_cw));
        $this->assertSame([], $temp_cw);
    }

    /**
     * The named symbol size must be one of the sizes of the shape.
     *
     * @return array<int, array{string, string}>
     */
    public static function unknownSymbolSizeProvider(): array
    {
        return [
            ['S', '99x99'],
            ['S', '8x48'],
            ['R', '10x10'],
            ['E', '10x10'],
            ['E', '8x50'],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('unknownSymbolSizeProvider')]
    public function testUnknownSymbolSize(string $shape, string $size): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        Data::getPaddingSize($shape, 1, $size);
    }

    /**
     * @return array<int, array{string, int, string, int, int}>
     */
    public static function namedSymbolSizeProvider(): array
    {
        // shape, codewords, size, rows and columns of the symbol
        return [
            ['S', 1,  '10x10', 10, 10],
            ['S', 3,  '12x12', 12, 12],
            ['R', 5,  '8x18',  8,  18],
            ['E', 18, '8x48',  8,  48],
        ];
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    #[DataProvider('namedSymbolSizeProvider')]
    public function testNamedSymbolSize(string $shape, int $ncw, string $size, int $rows, int $cols): void
    {
        $params = Data::getPaddingSize($shape, $ncw, $size);

        $this->assertSame($rows, $params[0]);
        $this->assertSame($cols, $params[1]);
    }

    /**
     * @throws \Com\Tecnick\Barcode\Exception
     */
    public function testNamedSymbolSizeTooSmall(): void
    {
        $this->bcExpectException(\Com\Tecnick\Barcode\Exception::class);

        Data::getPaddingSize('S', 1_000, '10x10');
    }
}
