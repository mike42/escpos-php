<?php
/**
 * This file is part of escpos-php: PHP receipt printer library for use with
 * ESC/POS-compatible thermal and impact printers.
 *
 * Copyright (c) 2014-16 Michael Billington < michael.billington@gmail.com >,
 * incorporating modifications by others. See CONTRIBUTORS.md for a full list.
 *
 * This software is distributed under the terms of the MIT license. See LICENSE.md
 * for details.
 */

namespace Mike42\Escpos;

use Exception;

/**
 * This class deals with images in raster formats, and converts them into formats
 * which are suitable for use on thermal receipt printers. Currently, only PNG
 * images (in) and ESC/POS raster format (out) are implemeted.
 *
 * Input formats:
 *  - Currently, only PNG is supported.
 *  - Other easily read raster formats (jpg, gif) will be added at a later date, as this is not complex.
 *  - The BMP format can be directly read by some commands, but this has not yet been implemented.
 *
 * Output formats:
 *  - Currently, only ESC/POS raster format is supported
 *  - ESC/POS 'column format' support is partially implemented, but is not yet used by Escpos.php library.
 *  - Output as multiple rows of column format image is not yet in the works.
 *
 * Libraries:
 *  - Currently, php-gd is used to read the input. Support for imagemagick where gd is not installed is
 *    also not complex to add, and is a likely future feature.
 *  - Support for native use of the BMP format is a goal, for maximum compatibility with target environments.
 */
abstract class EscposImage
{
    /**
     * @var int $imgHeight
     *  height of the image.
     */
    protected $imgHeight = 0;
    
    /**
     * @var int $imgWidth
     *  width of the image
     */
    protected $imgWidth = 0;
    
    /**
     * @var string $imgData
     *  Image data in rows: 1 for black, 0 for white.
     */
    protected $imgData = "";
    
    /**
     * @var string $imgColumnData
     *  Cached raster format data to avoid re-computation
     */
    protected $imgColumnData = null;
    
    /**
     * @var array:string $imgRasterData
     *  Cached raster format data to avoid re-computation
     */
    protected $imgRasterData = null;
    
    /**
     * @param string $filename Path to image filename, or null to create an empty image.
     */
    abstract public function __construct($filename = null);

    /**
     * @return int height of the image in pixels
     */
    public function getHeight()
    {
        return $this -> imgHeight;
    }
    
    /**
     * @return int Number of bytes to represent a row of this image
     */
    public function getHeightBytes()
    {
        return (int)(($this -> imgHeight + 7) / 8);
    }
    
    /**
     * @return int Width of the image
     */
    public function getWidth()
    {
        return $this -> imgWidth;
    }
    
    /**
     * @return int Number of bytes to represent a row of this image
     */
    public function getWidthBytes()
    {
        return (int)(($this -> imgWidth + 7) / 8);
    }

    /**
     * Output the image in raster (row) format. This can result in padding on the right of the image, if its width is not divisible by 8.
     *
     * @throws Exception Where the generated data is unsuitable for the printer (indicates a bug or oversized image).
     * @return string The image in raster format.
     */
    public function toRasterFormat()
    {
        if ($this -> imgRasterData != null) {
            /* Use previous calculation */
            return $this -> imgRasterData;
        }
        /* Loop through and convert format */
        $widthPixels = $this -> getWidth();
        $heightPixels = $this -> getHeight();
        $widthBytes = $this -> getWidthBytes();
        $heightBytes = $this -> getHeightBytes();
        $x = $y = $bit = $byte = $byteVal = 0;
        $data = str_repeat("\0", $widthBytes * $heightPixels);
        if (strlen($data) == 0) {
            return $data;
        }
        do {
            $byteVal |= (int)$this -> imgData[$y * $widthPixels + $x] << (7 - $bit);
            $x++;
            $bit++;
            if ($x >= $widthPixels) {
                $x = 0;
                $y++;
                $bit = 8;
                if ($y >= $heightPixels) {
                    $data[$byte] = chr($byteVal);
                    break;
                }
            }
            if ($bit >= 8) {
                $data[$byte] = chr($byteVal);
                $byteVal = 0;
                $bit = 0;
                $byte++;
            }
        } while (true);
        if (strlen($data) != ($this -> getWidthBytes() * $this -> getHeight())) {
            throw new Exception("Bug in " . __FUNCTION__ . ", wrong number of bytes.");
        }
        $this -> imgRasterData = $data;
        return $this -> imgRasterData;
    }
    
    /**
     * Output the image in column format.
     *
     * @param string $doubleDensity True for double density (24px) lines, false for single-density (8px) lines.
     * @return string[] an array, one item per line of output. All lines will be of equal size.
     */
    public function toColumnFormat($doubleDensity = false)
    {
        $out = array();
        $i = 0;
        while (($line = $this -> toColumnFormatLine($i, $doubleDensity)) !== null) {
            $out[] = $line;
            $i++;
        }
        return $out;
    }
    
    /**
     * Output image in column format. Must be called once for each line of output.
     */
    protected function toColumnFormatLine($lineNo, $doubleDensity)
    {
        // Currently double density in both directions, very experimental
        $widthPixels = $this -> getWidth();
        $heightPixels = $this -> getHeight();
        $widthBytes = $this -> getWidthBytes();
        $heightBytes = $this -> getHeightBytes();
        $lineHeight = $doubleDensity ? 3 : 1; // Vertical density. 1 or 3 (for 8 and 24 pixel lines)
        // Initialise to zero
        $x = $y = $bit = $byte = $byteVal = 0;
        $data = str_repeat("\x00", $widthPixels * $lineHeight);
        $yStart = $lineHeight * 8 * $lineNo;
        if ($yStart >= $heightPixels) {
            return null;
        }
        if (strlen($data) == 0) {
            return $data;
        }
        do {
            $yReal = $y + $yStart;
            if ($yReal < $heightPixels) {
                $byteVal |= (int)$this -> imgData[$yReal * $widthPixels + $x] << (7 - $bit);
            }
            $y++;
            $bit++;
            if ($y >= $lineHeight * 8) {
                $y = 0;
                $x++;
                $bit = 8;
                if ($x >= $widthPixels) {
                    $data[$byte] = chr($byteVal);
                    break;
                }
            }
            if ($bit >= 8) {
                $data[$byte] = chr($byteVal);
                $byteVal = 0;
                $bit = 0;
                $byte++;
            }
        } while (true);
        if (strlen($data) != $widthPixels * $lineHeight) {
            throw new Exception("Bug in " . __FUNCTION__ . ", wrong number of bytes.");
        }
        return $data;
    }
    
    /**
     * @return boolean True if GD is supported, false otherwise (a wrapper for the static version, for mocking in tests)
     */
    protected function isGdSupported()
    {
        return self::isGdLoaded();
    }
    
    /**
     * @return boolean True if Imagick is supported, false otherwise (a wrapper for the static version, for mocking in tests)
     */
    protected function isImagickSupported()
    {
        return self::isImagickLoaded();
    }
    
    
    /**
     * @return boolean True if GD is loaded, false otherwise
     */
    public static function isGdLoaded()
    {
        return extension_loaded('gd');
    }
    
    /**
     * @return boolean True if Imagick is loaded, false otherwise
     */
    public static function isImagickLoaded()
    {
        return extension_loaded('imagick');
    }
    

    /**
     * This is a convinience method to load an image from file, auto-selecting
     * an EscposImage implementation which uses an available library.
     *
     * The sub-classes can be constructed directly if you know that you will
     * have Imagick or GD on the print server.
     *
     * @param string $filename
     *  File to load from
     * @param string $allow_optimisations
     *  True to allow the fastest rendering shortcuts, false to force the library to read the image into an internal raster format and use PHP to render the image (slower but less fragile).
     * @param array $preferred
     *  Order to try to load libraries in- escpos-php supports pluggable image libraries. Items can be 'imagick', 'gd', 'native'.
     * @throws Exception
     *  Where no suitable library could be found for the type of file being loaded.
     * @return EscposImage
     *
     */
    public static function load($filename, $allow_optimisations = true, array $preferred = array('imagick', 'gd', 'native'))
    {
        /* Fail early if file is not readble */
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new Exception("File '$filename' does not exist, or is not readable.");
        }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        /* Choose the first implementation which can handle this format */
        foreach ($preferred as $implemetnation) {
            if ($implemetnation === 'imagick') {
                if (!self::isImagickLoaded()) {
                    // Skip option if Imagick is not loaded
                    continue;
                }
                return new \Mike42\Escpos\ImagickEscposImage($filename, $allow_optimisations);
            } elseif ($implemetnation === 'gd') {
                if (!self::isGdLoaded()) {
                    // Skip option if GD not loaded
                    continue;
                }
                return new \Mike42\Escpos\GdEscposImage($filename, $allow_optimisations);
            } elseif ($implemetnation === 'native') {
                if (!in_array($ext, array('wbmp', 'pbm', 'bmp'))) {
                    // Pure PHP is fastest way to generate raster output from wbmp and pbm formats.
                    continue;
                }
                return new \Mike42\Escpos\NativeEscposImage($filename, $allow_optimisations);
            } else {
                // Something else on the 'preferred' list.
                throw new InvalidArgumentException("'$implemetnation' is not a known EscposImage implementation");
            }
        }
        throw new InvalidArgumentException("No suitable EscposImage implementation found for '$filename'.");
    }
}
