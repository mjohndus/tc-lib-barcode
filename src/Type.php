<?php

declare(strict_types=1);

/**
 * Type.php
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

namespace Com\Tecnick\Barcode;

use Com\Tecnick\Barcode\Exception as BarcodeException;
use Com\Tecnick\Color\Exception as ColorException;
use Com\Tecnick\Color\Model\Rgb;
use Com\Tecnick\Color\Pdf;
use Com\Tecnick\Color\Web;

/**
 * Com\Tecnick\Barcode\Type
 *
 * Barcode Type class
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-barcode
 *
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 */
abstract class Type extends \Com\Tecnick\Barcode\Type\Convert implements Model
{
    /**
     * Initialize a new barcode object
     *
     * @param string                    $code    Barcode content
     * @param int                       $width   Barcode width in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each column.
     * @param int                       $height  Barcode height in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each row.
     * @param string                    $color   Foreground color in Web notation
     *                                           (color name, or hexadecimal code, or CSS syntax)
     *                                           or PDF spot color name
     * @param array<int|float|string>   $params  Array containing extra parameters for the specified barcode type
     * @param array{int, int, int, int} $padding Additional padding to add around the barcode
     *                                           (top, right, bottom, left) in user units. A
     *                                           negative value indicates the number of rows
     *                                           or columns.
     *
     * @throws BarcodeException in case of error
     * @throws ColorException in case of color error
     */
    public function __construct(
        string $code,
        int $width = -1,
        int $height = -1,
        string $color = 'black',
        array $params = [],
        array $padding = [0, 0, 0, 0],
    ) {
        $this->code = $code;
        $this->extcode = $code;
        $this->params = $params;
        $this->setParameters();
        $this->setBars();
        $this->setSize($width, $height, $padding);
        $this->setColor($color);
    }

    /**
     * Set extra (optional) parameters
     */
    protected function setParameters(): void {}

    /**
     * Set the bars array
     */
    protected function setBars(): void {}

    /**
     * Set the size of the barcode to be exported
     *
     * @param int                       $width   Barcode width in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each column.
     * @param int                       $height  Barcode height in user units (excluding padding).
     *                                           A negative value indicates the multiplication
     *                                           factor for each row.
     * @param array{int, int, int, int} $padding Additional padding to add around the barcode
     *                                           (top, right, bottom, left) in user units. A
     *                                           negative value indicates the number of rows
     *                                           or columns.
     *
     * @throws BarcodeException in case of an empty barcode or invalid padding
     */
    public function setSize(int $width, int $height, array $padding = [0, 0, 0, 0]): static
    {
        if ($this->ncols <= 0 || $this->nrows <= 0) {
            throw new BarcodeException('Empty barcode: the number of rows and columns must be greater than zero');
        }

        $this->width = $width;
        if ($this->width <= 0) {
            $this->width = \abs(\min(-1, $this->width)) * $this->ncols;
        }

        $this->height = $height;
        if ($this->height <= 0) {
            $this->height = \abs(\min(-1, $this->height)) * $this->nrows;
        }

        $this->width_ratio = $this->width / $this->ncols;
        $this->height_ratio = $this->height / $this->nrows;

        $this->setPadding($padding);

        return $this;
    }

    /**
     * Set the barcode padding
     *
     * @param array{int, int, int, int} $padding Additional padding to add around the barcode
     *                                           (top, right, bottom, left) in user units.
     *                                           A negative value indicates the number of rows or columns.
     *
     * @throws BarcodeException in case of error
     */
    protected function setPadding(array $padding): static
    {
        if (\array_keys($padding) !== [0, 1, 2, 3]) {
            throw new BarcodeException('Invalid padding, expecting an array of 4 numbers (top, right, bottom, left)');
        }

        foreach ($padding as $key => $val) {
            $side = match ($key) {
                0 => 'T',
                1 => 'R',
                2 => 'B',
                3 => 'L',
            };
            $ratio = match ($key) {
                0, 2 => $this->height_ratio,
                1, 3 => $this->width_ratio,
            };
            if ($val < 0) {
                $val = \abs(\min(-1, $val)) * $ratio;
            }

            $this->padding[$side] = (int) $val;
        }

        return $this;
    }

    /**
     * Set the color of the bars.
     * An empty or transparent foreground color is rejected with a BarcodeException.
     *
     * @param string $color Foreground color in Web notation (color name, or hexadecimal code, or CSS syntax)
     *                      or PDF spot color name
     *
     * @throws ColorException in case of color error
     * @throws BarcodeException in case of empty or transparent color
     */
    public function setColor(string $color): static
    {
        $colobj = $this->getRgbColorObject($color);
        if ($colobj === null) {
            throw new BarcodeException('The foreground color cannot be empty or transparent');
        }

        $this->color_obj = $colobj;
        return $this;
    }

    /**
     * Set the Space bars color.
     *
     * @param string $color Space bars color in Web notation (color name, or hexadecimal code, or CSS syntax)
     *
     * @throws ColorException in case of color error
     */
    public function setSpaceColor(string $color): static
    {
        $this->fs_color_obj = $this->getRgbColorObject($color);
        return $this;
    }

    /**
     * Set the background color and radius.
     *
     * @param string $color Background color in Web notation (color name, or hexadecimal code, or CSS syntax)
     *                      or PDF spot color name
     *
     * @param int $radius from 4 to 22
     *
     * @throws ColorException in case of color error
     */
    public function setBackgroundColor(string $color, int $radius = 0): static
    {
        $this->bg_color_obj = $this->getRgbColorObject($color);
        $this->radius = (($radius > 4 and $radius <= 22) ? $radius : 0);
        return $this;
    }

    /**
     * Set the border color and line-width
     *
     * @param string $color Border color in Web notation (color name, or hexadecimal code, or CSS syntax)
     *
     * @param float $bordw from 0.4 to 4
     *
     * @throws ColorException in case of color error
     */
    public function setBorder(string $color, float $bordw): static
    {
        $this->bd_color_obj = $this->getRgbColorObject($color);
        $this->bordw = (($bordw > 0.4 and $bordw <= 4.0) ? $bordw : 0);
        return $this;
    }

    /**
     * Get the RGB Color object for the given color representation.
     * Web and CSS notations are resolved first, then PDF spot color names.
     *
     * @param string $color Color in Web notation (color name, or hexadecimal code, or CSS syntax)
     *                      or PDF spot color name
     *
     * @return ?Rgb Null if the color is empty or transparent
     *
     * @throws ColorException if the color cannot be parsed
     */
    protected function getRgbColorObject(string $color): ?Rgb
    {
        $web = new Web();
        try {
            $cobj = $web->getColorObj($color);
        } catch (ColorException $colorException) {
            $pdf = new Pdf();
            $cobj = $pdf->getColorObject($color);
            if ($cobj === null) {
                throw $colorException;
            }
        }

        if ($cobj === null) {
            return null;
        }

        return $cobj instanceof Rgb ? $cobj : new Rgb($cobj->toRgbArray());
    }

    /**
     * Get the barcode raw array
     *
     * @return array{
     *             'type': string,
     *             'format': string,
     *             'params': array<int|float|string>,
     *             'marks': array<int, string>,
     *             'code': string,
     *             'extcode': string,
     *             'ncols': int,
     *             'nrows': int,
     *             'width': int,
     *             'height': int,
     *             'width_ratio': float,
     *             'height_ratio': float,
     *             'padding': array{'T': int, 'R': int, 'B': int, 'L': int},
     *             'full_width': int,
     *             'full_height': int,
     *             'color_obj': Rgb,
     *             'fs_color_obj': ?Rgb,
     *             'bg_color_obj': ?Rgb,
     *             'bd_color_obj': ?Rgb,
     *             'bordw':float,
     *             'radius':int,
     *             'bars': array<array{int, int, int, int}>,
     *             'sbars': array<array{int, int, int, int}>,
     *         }
     */
    public function getArray(): array
    {
        return [
            'type' => $this::TYPE,
            'format' => $this::FORMAT,
            'params' => $this->params,
            'marks' => $this->marks,
            'code' => $this->code,
            'extcode' => $this->extcode,
            'ncols' => $this->ncols,
            'nrows' => $this->nrows,
            'width' => $this->width,
            'height' => $this->height,
            'width_ratio' => $this->width_ratio,
            'height_ratio' => $this->height_ratio,
            'padding' => $this->padding,
            'full_width' => $this->width + $this->padding['L'] + $this->padding['R'],
            'full_height' => $this->height + $this->padding['T'] + $this->padding['B'],
            'color_obj' => $this->color_obj,
            'fs_color_obj' => $this->fs_color_obj,
            'bg_color_obj' => $this->bg_color_obj,
            'bd_color_obj' => $this->bd_color_obj,
            'bordw' => $this->bordw,
            'radius' => $this->radius,
            'bars' => $this->bars,
            'sbars' => $this->sbars,
        ];
    }

    /**
     * Get the extended code (code + checksum)
     */
    public function getExtendedCode(): string
    {
        return $this->extcode;
    }

    /**
     * Sends the data as file to the browser.
     *
     * @param string $data The file data.
     * @param string $mime The file MIME type (i.e. 'application/svg+xml' or 'image/png').
     * @param string $fileext The file extension (i.e. 'svg' or 'png').
     * @param string|null $filename The file name without extension (optional).
     *                              Only allows alphanumeric characters, underscores and hyphens.
     *                              Defaults to a md5 hash of the data.
     *
     * @return void
     */
    protected function getHTTPFile(string $data, string $mime, string $fileext, ?string $filename = null): void
    {
        if (\is_null($filename) || \preg_match('/^[a-zA-Z0-9_\-]{1,250}\z/', $filename) !== 1) {
            $filename = \md5($data);
        }

        \header('Content-Type: ' . $mime);
        \header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=1');
        \header('Pragma: public');
        \header('Expires: Thu, 04 jan 1973 00:00:00 GMT'); // Date in the past
        \header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        \header('Content-Disposition: inline; filename="' . $filename . '.' . $fileext . '";');
        if (($_SERVER['HTTP_ACCEPT_ENCODING'] ?? null) === null) {
            // the content length may vary if the server is using compression
            \header('Content-Length: ' . \strlen($data));
        }

        echo $data;
    }

    /**
     * Get the barcode as SVG image object.
     *
     * @param string|null $filename The file name without extension (optional).
     *                              Only allows alphanumeric characters, underscores and hyphens.
     *                              Defaults to a md5 hash of the data.
     *                              The file extension is always '.svg'.
     */
    public function getSvg(?string $filename = null): void
    {
        $this->getHTTPFile($this->getSvgCode(), 'application/svg+xml', 'svg', $filename);
    }

    /**
     * Get the barcode as inline SVG code.
     *
     * @return string Inline SVG code.
     */
    public function getInlineSvgCode(): string
    {
        if (\array_sum($this->padding) / 4 < 12) {
            $br = 0;
            $bw = $this->bordw;
        } else {
            $br = $this->radius;
            $bw = $this->bordw;
        }
        // ENT_SUBSTITUTE replaces the invalid UTF-8 sequences of a binary payload
        $hflag = ENT_XML1 | ENT_DISALLOWED | ENT_SUBSTITUTE;

        $width = \sprintf('%F', $this->width + $this->padding['L'] + $this->padding['R']);
        $height = \sprintf('%F', $this->height + $this->padding['T'] + $this->padding['B']);

        $svg =
            '<svg'
            . ' version="1.2"'
            . ' baseProfile="full"'
            . ' xmlns="http://www.w3.org/2000/svg"'
            . ' xmlns:xlink="http://www.w3.org/1999/xlink"'
            . ' xmlns:ev="http://www.w3.org/2001/xml-events"'
            . ' width="'
            . $width
            . '"'
            . ' height="'
            . $height
            . '"'
            . ' viewBox="0 0 '
            . $width
            . ' '
            . $height
            . '"'
            . '>'
            . "\n"
            . "\t"
            . '<desc>'
            . \htmlspecialchars($this->code, $hflag, 'UTF-8')
            . '</desc>'
            . "\n";
        if ($this->bg_color_obj !== null) {
            $svg .=
                '	<rect x="' . ($bw / 2) . '" y="' . ($bw / 2) . '"'
                . ' rx="' . $br . '"'
                . ' ry="' . $br . '"'
                . ' width="' . ((int)$width - $bw) . '"'
                . ' height="' . ((int)$height - $bw) . '"'
                . ' fill="'
                . $this->bg_color_obj->getRgbHexColor()
                . '"';
            if ($bw != 0) {
                if ($this->bd_color_obj instanceof \Com\Tecnick\Color\Model\Rgb) {
                    $svg .= ' stroke="' . $this->bd_color_obj->getRgbHexColor() . '"'
                        . ' stroke-width="' . $bw . '"'
                        . ' stroke-linecap="square"';
                }
            }
        $svg .= ' />'
              . "\n";
        }

        $svg .=
            '	<g id="bars" fill="'
            . $this->color_obj->getRgbHexColor()
            . '"'
            . ' stroke="none"'
            . ' stroke-width="0"'
            . ' stroke-linecap="square"'
            . '>'
            . "\n";
        list($bars, $sbars) = $this->getBarsArrayXYWH();
        foreach ($bars as $bar) {
            $svg .= \sprintf(
                '		<rect x="%F" y="%F" width="%F" height="%F" />' . "\n",
                $bar[0],
                $bar[1],
                $bar[2],
                $bar[3],
            );
        }

        if ($this->fs_color_obj instanceof \Com\Tecnick\Color\Model\Rgb) {
        $svg .=
            '   <g id="sbars" fill="'
            . $this->fs_color_obj->getRgbHexColor()
            . '"'
            . ' stroke="none"'
            . ' stroke-width="0"'
            . ' stroke-linecap="square"'
            . '>'
            . "\n";
            foreach ($sbars as $bar1) {
                $svg .= \sprintf(
                    '               <rect x="%F" y="%F" width="%F" height="%F" />' . "\n",
                    $bar1[0],
                    $bar1[1],
                    $bar1[2],
                    $bar1[3],
                );
            }
        }

        return $svg . ('	</g>' . "\n" . '</svg>' . "\n");
    }

    /**
     * Get the barcode as SVG code, including the XML declaration.
     *
     * @return string SVG code
     */
    public function getSvgCode(): string
    {
        return '<?xml version="1.0" standalone="no" ?>' . "\n" . $this->getInlineSvgCode();
    }

    /**
     * Get an HTML representation of the barcode.
     *
     * @return string HTML code (DIV block)
     */
    public function getHtmlDiv(): string
    {
        if (\array_sum($this->padding) / 4 < 12) {
            $br = 0;
            $bw = $this->bordw;
        } else {
            $br = $this->radius;
            $bw = $this->bordw;
        }
        $html = \sprintf(
            '<div style="width:%Fpx;height:%Fpx;border-radius:' . $br . 'px;position:relative;font-size:0;border:none;padding:0;margin:0;',
            $this->width + $this->padding['L'] + $this->padding['R'],
            $this->height + $this->padding['T'] + $this->padding['B'],
        );
        if ($this->bg_color_obj !== null) {
            $html .= 'background-color:' . $this->bg_color_obj->getCssColor() . ';';
        }
        if ($bw != 0) {
            $html .= 'border:solid;'
                . 'border-width:' . $bw . 'px;';
            if ($this->bd_color_obj instanceof \Com\Tecnick\Color\Model\Rgb) {
                $html .= 'border-color:' . $this->bd_color_obj->getCssColor() . ';';
            }
        }

        $html .= '">' . "\n";
        list($bars, $sbars) = $this->getBarsArrayXYWH();
        foreach ($bars as $bar) {
            $html .= \sprintf(
                '    <div style="background-color:%s;left:%Fpx;top:%Fpx;width:%Fpx;height:%Fpx;position:absolute;border:none;padding:0;margin:0;">&nbsp;</div>'
                . "\n",
                $this->color_obj->getCssColor(),
                $bar[0],
                $bar[1],
                $bar[2],
                $bar[3],
            );
        }
        if ($this->fs_color_obj instanceof \Com\Tecnick\Color\Model\Rgb) {
            foreach ($sbars as $bar1) {
                $html .= \sprintf(
                    '       <div style="background-color:%s;left:%Fpx;top:%Fpx;width:%Fpx;height:%Fpx;position:absolute;border:none;padding:0;margin:0;">&nbsp;</div>'
                    . "\n",
                    $this->fs_color_obj->getCssColor(),
                    $bar1[0],
                    $bar1[1],
                    $bar1[2],
                    $bar1[3],
                );
            }
        }

        return $html . ('</div>' . "\n");
    }

    /**
     * Get Barcode as PNG Image (requires GD or Imagick library)
     *
     * @param string|null $filename The file name without extension (optional).
     *                              Only allows alphanumeric characters, underscores and hyphens.
     *                              Defaults to a md5 hash of the data.
     *                              The file extension is always '.png'.
     *
     * @throws BarcodeException in case image generation fails
     */
    public function getPng(?string $filename = null): void
    {
        $this->getHTTPFile($this->getPngData(), 'image/png', 'png', $filename);
    }

    /**
     * Get the barcode as PNG image (requires GD or Imagick library)
     *
     * @param bool $imagick If true try to use the Imagick extension
     *
     * @return string PNG image data
     *
     * @throws BarcodeException in case image generation fails
     */
    public function getPngData(bool $imagick = true): string
    {
        if ($imagick && \extension_loaded('imagick')) {
            return $this->getPngDataImagick();
        }

        $gdImage = $this->getGd();
        \ob_start();
        \imagepng($gdImage);
        $data = \ob_get_clean();
        if ($data === false) {
            throw new BarcodeException('Unable to get PNG data');
        }
        return $data;
    }

    /**
     * Maximum width or height, in pixels, of a rendered barcode image.
     */
    protected const MAX_IMAGE_SIDE = 30_000;

    /**
     * Compute and validate the rendered image dimensions, in pixels.
     *
     * @return array{int, int} [width, height], each at least 1 pixel
     *
     * @throws BarcodeException if the requested image size is too large
     */
    protected function getImageSize(): array
    {
        $width = \max(1, (int) \ceil($this->width + $this->padding['L'] + $this->padding['R']));
        $height = \max(1, (int) \ceil($this->height + $this->padding['T'] + $this->padding['B']));
        if ($width > self::MAX_IMAGE_SIDE || $height > self::MAX_IMAGE_SIDE) {
            throw new BarcodeException(
                'The requested image size ('
                . $width
                . 'x'
                . $height
                . ' px) exceeds the maximum of '
                . self::MAX_IMAGE_SIDE
                . ' px per side',
            );
        }

        return [$width, $height];
    }

    /**
     * Get the barcode as PNG image (requires Imagick library)
     *
     * @throws BarcodeException if the Imagick library is not installed or the image is too large
     */
    public function getPngDataImagick(): string
    {
        $imagick = new \Imagick();
        [$width, $height] = $this->getImageSize();

        if (array_sum($this->padding) / 4 < 12) {
            $br = 0;
            $bw = $this->bordw;
        } else {
            $br = $this->radius;
            $bw = $this->bordw;
        }

        $imagick->newImage($width, $height, 'none', 'png');
        $imagickdraw = new \ImagickDraw();
        if ($this->bg_color_obj !== null) {
            $imagickdraw->setfillcolor($this->bg_color_obj->getRgbHexColor());
            } else {
                $imagickdraw->setfillcolor('#00000000');
        }

        if ($this->bd_color_obj !== null) {
            $imagickdraw->setstrokecolor($this->bd_color_obj->getRgbHexColor());
//            } else {
//                $imagickdraw->setfillcolor('#00000000');
        }

        $imagickdraw->setstrokewidth($bw);
        if ($br !== 0) {
            $imagickdraw->roundrectangle(
                \ceil($bw / 2),
                \ceil($bw / 2),
                $width - $bw + 0,
                $height - $bw + 0,
                $br - $bw,
                $br - $bw
            );
           } else {
                $imagickdraw->rectangle(\ceil($bw / 2), \ceil($bw / 2), $width - $bw + 0, $height - $bw + 0);
        }

        $imagickdraw->setfillcolor($this->color_obj->getRgbHexColor());
        $imagickdraw->setstrokecolor($this->color_obj->getRgbHexColor());
        $imagickdraw->setStrokeWidth(0);
        list($bars, $sbars) = $this->getBarsArrayXYXY();
        foreach ($bars as $bar) {
            $imagickdraw->rectangle($bar[0], $bar[1], $bar[2], $bar[3]);
        }

        if ($this->fs_color_obj !== null) {
            $imagickdraw->setfillcolor($this->fs_color_obj->getRgbHexColor());
            $imagickdraw->setstrokecolor($this->fs_color_obj->getRgbHexColor());
//            } else {
//                $imagickdraw->setfillcolor('#00000000');
        }

        $imagickdraw->setStrokeWidth(0);
        foreach ($sbars as $bar1) {
            $imagickdraw->rectangle($bar1[0], $bar1[1], $bar1[2], $bar1[3]);
        }

        $imagick->drawimage($imagickdraw);
        return $imagick->getImageBlob();
    }

    /**
     * Allocate a color in the palette of a GD image.
     *
     * @param string $role Color role to report in the error message
     *
     * @throws BarcodeException if the allocation fails
     */
    protected function allocateGdColor(\GdImage $img, Rgb $color, string $role): int
    {
        $rgbcolor = $color->getNormalizedArray(255);
        $index = \imagecolorallocate(
            $img,
            (int) ($rgbcolor['R'] ?? 0.0),
            (int) ($rgbcolor['G'] ?? 0.0),
            (int) ($rgbcolor['B'] ?? 0.0),
        );
        if ($index === false) {
            throw new BarcodeException('Unable to allocate ' . $role . ' color');
        }

        return $index;
    }

    /**
     * Apply GD background color/alpha strategy.
     *
     * @throws BarcodeException if background allocation fails
     */
    protected function applyGdBackground(\GdImage $img, int $width, int $height): void
    {
        $bgColorObj = $this->bg_color_obj;
        if ($bgColorObj !== null) {
            $bg_color = $this->allocateGdColor($img, $bgColorObj, 'GD background');
            \imagefilledrectangle($img, 0, 0, $width, $height, $bg_color);
            return;
        }

        $background_color = $this->allocateGdColor(
            $img,
            $this->color_obj->withInvertedColor(),
            'default GD background',
        );
        \imagecolortransparent($img, $background_color);
    }

    /**
     * Get the barcode as GD image object (requires GD library)
     *
     * @throws BarcodeException if the GD library is not installed or the image is too large
     */
    public function getGd(): \GdImage
    {
        [$width, $height] = $this->getImageSize();
        $img = \imagecreate($width, $height);
        if ($img === false) {
            throw new BarcodeException('Unable to create GD image');
        }

        $this->applyGdBackground($img, $width, $height);

        $bar_color = $this->allocateGdColor($img, $this->color_obj, 'GD foreground');
        list($bars, $sbars)  = $this->getBarsArrayXYXY();
        //$bars = $this->getBarsArrayXYXY();
        foreach ($bars as $bar) {
            \imagefilledrectangle(
                $img,
                (int) \floor($bar[0]),
                (int) \floor($bar[1]),
                (int) \floor($bar[2]),
                (int) \floor($bar[3]),
                $bar_color,
            );
        }

        return $img;
    }

    /**
     * Get a raw barcode string representation using characters
     *
     * @param string $space_char Character or string to use for filling empty spaces
     * @param string $bar_char   Character or string to use for filling bars
     */
    public function getGrid(string $space_char = '0', string $bar_char = '1'): string
    {
        $raw = $this->getGridArray($space_char, $bar_char);
        $grid = '';
        foreach ($raw as $row) {
            $grid .= \implode('', $row) . "\n";
        }

        return $grid;
    }

    /**
     * Get the array containing all the formatted bars coordinates (x1, y1, x2, y2)
     *
     * @return list{list<array{0: float, 1: float, 2: float, 3: float}>, list<array{0: float, 1: float, 2: float, 3: float}>}
     */
    public function getBarsArrayXYXY(): array
    {
        list($mark, $smark) = $this->guard();
        $rect = [];
        $abc = 0;
        foreach ($this->bars as $bar) {
            if ($bar[2] <= 0) {
                continue;
            }

            if ($bar[3] <= 0) {
                continue;
            }

            $rect[] = $this->getBarRectXYXY($bar);


            if (! empty($mark)) {
                $rect[$abc][3] = ($rect[$abc][3] ?? 0.0) - ($mark[$abc] ?? 0.0);
                $abc++;
            }
        }
        $rect1 = [];
        $abc = 0;
        foreach ($this->sbars as $bar) {
            if ($bar[2] <= 0) {
                continue;
            }

            if ($bar[3] <= 0) {
                continue;
            }

            $rect1[] = $this->getBarRectXYXY($bar);

            if (! empty($smark)) {
                $rect1[$abc][3] = ($rect1[$abc][3] ?? 0.0) - ($smark[$abc] ?? 0.0);
                $abc++;
            }
        }

        if ($this->nrows > 1) {
            // reprint rotated to cancel row gaps
            list($rot, $rot1) = $this->getRotatedBarArray();
            foreach ($rot as $bar) {
                if ($bar[2] <= 0) {
                    continue;
                }

                if ($bar[3] <= 0) {
                    continue;
                }

                $rect[] = $this->getBarRectXYXY($bar);
            }
            foreach ($rot1 as $bar) {
                if ($bar[2] <= 0) {
                    continue;
                }

                if ($bar[3] <= 0) {
                    continue;
                }

                $rect1[] = $this->getBarRectXYXY($bar);
            }
        }

        return [$rect, $rect1];
    }

    /**
     * Get the array containing all the formatted bars coordinates (x, y, width, height)
     *
     * @return list{list<array{float, float, float, float}>, list<array{float, float, float, float}>}
     */
    public function getBarsArrayXYWH(): array
    {
        list($mark, $smark) = $this->guard();
        $rect = [];
        $abc = 0;
        foreach ($this->bars as $bar) {
            if ($bar[2] <= 0) {
                continue;
            }

            if ($bar[3] <= 0) {
                continue;
            }

            $rect[] = $this->getBarRectXYWH($bar);

            if (! empty($mark)) {
                $rect[$abc][3] = ($rect[$abc][3] ?? 0.0) - ($mark[$abc] ?? 0.0);
                $abc++;
            }
        }
        $rect1 = [];
        $abc = 0;
        foreach ($this->sbars as $bar) {
            if ($bar[2] <= 0) {
                continue;
            }

            if ($bar[3] <= 0) {
                continue;
            }

            $rect1[] = $this->getBarRectXYWH($bar);

            if (! empty($smark)) {
                $rect1[$abc][3] = ($rect1[$abc][3] ?? 0.0) - ($smark[$abc] ?? 0.0);
                $abc++;
            }
        }

        if ($this->nrows > 1) {
            // reprint rotated to cancel row gaps
            list($rot, $rot1) = $this->getRotatedBarArray();
            foreach ($rot as $bar) {
                if ($bar[2] <= 0) {
                    continue;
                }

                if ($bar[3] <= 0) {
                    continue;
                }

                $rect[] = $this->getBarRectXYWH($bar);
            }
            foreach ($rot1 as $bar1) {
                if ($bar1[2] <= 0) {
                    continue;
                }

                if ($bar1[3] <= 0) {
                    continue;
                }

                $rect1[] = $this->getBarRectXYWH($bar1);
            }
        }

        return [$rect, $rect1];
    }

    /**
     * Get the array containing all the formatted R,G,B colors
     *
     * @return array<int, array<string, float>|null>
     */
    public function getcolor(): array
    {
            $fg_col = $this->color_obj->getNormalizedArray(255);
            $bg_col = $this->bg_color_obj !== null ? $this->bg_color_obj->getNormalizedArray(255) : null;
            $bd_col = $this->bd_color_obj !== null ? $this->bd_color_obj->getNormalizedArray(255) : null;
            $fs_col = $this->fs_color_obj !== null ? $this->fs_color_obj->getNormalizedArray(255) : null;

        return [$fg_col, $bg_col, $bd_col, $fs_col];
    }
}
