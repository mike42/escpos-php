<?php
/**
 * escpos-php, a Thermal receipt printer library, for use with
 * ESC/POS compatible printers.
 * 
 * Copyright (c) 2014-2015 Michael Billington <michael.billington@gmail.com>,
 * 	incorporating modifications by:
 *  - Roni Saha <roni.cse@gmail.com>
 *  - Gergely Radics <gerifield@ustream.tv>
 *  - Warren Doyle <w.doyle@fuelled.co>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
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
class EscposImage {
	/**
	 * @var string The image's bitmap data (if it is a Windows BMP).
	 */
	private $imgBmpData;
	
	
	/**
	 * @var string image data in rows: 1 for black, 0 for white.
	 */
	private $imgData;
	
	/**
	 * @var string cached raster format data to avoid re-computation
	 */
	private $imgRasterData;
	
	/**
	 * @var int height of the image
	 */
	private $imgHeight;

	/**
	 * @var int width of the image
	 */
	private $imgWidth;
	
	/**
	 * Load up an image
	 * 
	 * @param string $imgPath The path to the image to load. Currently only PNG graphics are supported.
	 */
	public function __construct($imgPath) {
		/* Can't use bitmaps yet */
		$this -> imgBmpData = null;
		$this -> imgRasterData = null;
		
		/* Load up using GD */
		$im = imagecreatefrompng($imgPath);
		if(!$im) {
			throw new Exception("Failed to load image '$imgPath'. Must be a PNG file.");
		}
		
		/* Make a string of 1's and 0's */
		$this -> imgHeight = imagesy($im);
		$this -> imgWidth = imagesx($im);
		$this -> imgData = str_repeat("\0", $this -> imgHeight * $this -> imgWidth);
 		for($y = 0; $y < $this -> imgHeight; $y++) {
 			for($x = 0; $x < $this -> imgWidth; $x++) {
 				/* Faster to average channels, blend alpha and negate the image here than via filters (tested!) */
 				$cols = imagecolorsforindex($im, imagecolorat($im, $x, $y));
 				$greyness = (int)($cols['red'] + $cols['red'] + $cols['blue']) / 3;
 				$black = (255 - $greyness) >> (7 + ($cols['alpha'] >> 6));
 				$this -> imgData[$y * $this -> imgWidth + $x] = $black;
 			}
 		}
	}
	
	/**
	 * @return int height of the image in pixels
	 */
	public function getHeight() {
		return $this -> imgHeight;
	}
	
	/**
	 * @return int Number of bytes to represent a row of this image
	 */
	public function getHeightBytes() {
		return (int)(($this -> imgHeight + 7) / 8);
	}
	
	/**
	 * @return int Width of the image
	 */
	public function getWidth() {
		return $this -> imgWidth;
	}
	
	/**
	 * @return int Number of bytes to represent a row of this image
	 */
	public function getWidthBytes() {
		return (int)(($this -> imgWidth + 7) / 8);
	}
	
	/**
	 * @return string binary data of the original file, for function which accept bitmaps.
	 */
	public function getWindowsBMPData() {
		return $this -> imgBmpData;
	}
	
	/**
	 * @return boolean True if the image was a windows bitmap, false otherwise
	 */
	public function isWindowsBMP() {
		return $this -> imgBmpData != null;
	}
	
	/**
	 * Output the image in raster (row) format. This can result in padding on the right of the image, if its width is not divisible by 8.
	 * 
	 * @throws Exception Where the generated data is unsuitable for the printer (indicates a bug or oversized image).
	 * @return string The image in raster format.
	 */
	public function toRasterFormat() {
		if($this -> imgRasterData != null) {
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
		do {
			$byteVal |= (int)$this -> imgData[$y * $widthPixels + $x] << (7 - $bit);
			$x++;
			$bit++;
			if($x >= $widthPixels) {
				$x = 0;
				$y++;
				$bit = 8;
				if($y >= $heightPixels) {
					$data[$byte] = chr($byteVal);
					break;
				}
			}
			if($bit >= 8) {
				$data[$byte] = chr($byteVal);
				$byteVal = 0;
				$bit = 0;
				$byte++;
			}
		} while(true);
 		if(strlen($data) != ($this -> getWidthBytes() * $this -> getHeight())) {
 			throw new Exception("Bug in " . __FUNCTION__ . ", wrong number of bytes.");
 		}
 		$this -> imgRasterData = $data;
 		return $this -> imgRasterData;
	}
	
	/**
	 * Output image in column format. This format results in padding at the base and right of the image, if its height and width are not divisible by 8.
	 */
	private function toColumnFormat() {
		/* Note: This function is marked private, as it is not yet used and may be buggy. */
		$widthPixels = $this -> getWidth();
		$heightPixels = $this -> getHeight();
		$widthBytes = $this -> getWidthBytes();
		$heightBytes = $this -> getHeightBytes();
		$x = $y = $bit = $byte = $byteVal = 0;
		$data = str_repeat("\0", $widthBytes * $heightBytes * 8);
 		do {
 			$byteVal |= (int)$this -> imgData[$y * $widthPixels + $x] << (8 - $bit);
 			$y++;
 			$bit++;
 			if($y >= $heightPixels) {
 				$y = 0;
 				$x++;
 				$bit = 8;
 				if($x >= $widthPixels) {
 					$data[$byte] = chr($byteVal);
 					break;
 				}
 			}
 			if($bit >= 8) {
 				$data[$byte] = chr($byteVal);
 				$byteVal = 0;
 				$bit = 0;
 				$byte++;
 			}
 		} while(true);
  		if(strlen($data) != ($widthBytes * $heightBytes * 8)) {
  			throw new Exception("Bug in " . __FUNCTION__ . ", wrong number of bytes. Should be " . ($widthBytes * $heightBytes * 8) . " but was " . strlen($data));
  		}
		return $data;
	}
}