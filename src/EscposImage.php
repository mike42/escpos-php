<?php
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
	 * @param string $path
	 */
	public function __construct($imgPath) {
		/* Can't use bitmaps yet */
		$this -> imgBmpData = null;
		
		/* Load up using GD */
		$im = imagecreatefrompng($imgPath);
		if(!$im) {
			throw new Exception("Failed to load image '$imgPath'.");
		}
		
		/* Make a string of 1's and 0's */
  		imagefilter($im, IMG_FILTER_GRAYSCALE);
  		imagefilter($im, IMG_FILTER_NEGATE);
		$this -> imgHeight = imagesy($im);
		$this -> imgWidth = imagesx($im);
		$this -> imgData = str_repeat("0", $this -> imgHeight * $this -> imgWidth);
 		for($y = 0; $y < $this -> imgHeight; $y++) {
 			for($x = 0; $x < $this -> imgWidth; $x++) {
 				$cols = imagecolorsforindex($im, imagecolorat($im, $x, $y));
 				$val = $cols['red'] >> 7;
 				$this -> imgData[$y * $this -> imgWidth + $x] = $val;
 			}
 		}
	}
	
	/**
	 */
	public function getHeight() {
		return $this -> imgHeight;
	}
	
	/**
	 * Width of the image
	 */
	public function getWidth() {
		return $this -> imgWidth;
	}
	
	/**
	 * Number of bytes to represent a row of this image
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
		return $this -> imgBmpData == null;
	}
	
	/** 
	 * Output the image in raster format.
	 * 
	 * @return string The image in ESC/POS raster format
	 */
	public function toRasterFormat() {
		$widthBytes = $this -> getWidthBytes();
		$heightDots = $this -> getHeight();
		$widthDots = $this -> getWidth();
		$row = array();
		for($y = 0; $y < $heightDots; $y++) {
			// For each row
			$column = str_repeat("\0", $widthBytes);
			for($byte = 0; $byte < $widthBytes; $byte++) {
				// For each byte
				$char = 0;
				for($bit = 0; $bit < 8; $bit++) {
					// For each bit
					$x = $byte * 8 + $bit;
					if($x < $this -> imgWidth) {
						$char |= (int)$this -> imgData[$y * $this -> imgWidth + $x] << (7 - $bit);
					}
				}
				$column[$byte] = chr($char);
			}
 			$row[] = $column;
 		}
 		
 		/* Join up */
 		$data = implode($row, "");
 		if(strlen($data) != ($this -> getWidthBytes() * $this -> getHeight())) {
 			throw new Exception("Bug in " . __FUNCTION__ . ", wrong number of bytes.");
 		}
		return $data;
	}
}
