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
 				$this -> imgData[$y * $this -> imgWidth + $x] = ($cols['red'] >> 7);
 			}
 		}
	}
	
	/**
	 */
	public function getHeight() {
		return $this -> imgHeight;
	}
	
	/**
	 * Number of bytes to represent a row of this image
	 */
	public function getHeightBytes() {
		return (int)(($this -> imgHeight + 7) / 8);
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
		return $this -> imgBmpData != null;
	}
	

	/**
	 * Output the image in raster (row) format. This can result in padding on the right of the image, if its width is not divisible by 8.
	 *
	 * @return string The image in ESC/POS raster format
	 * @throws Exception Where the generated data is unsuitable for the printer (indicates a bug or oversized image).
	 * @return string The image in raster format, with header.
	 */
	public function toRasterFormat() {
		// TODO remove string array & implode()- should write directly to an output array.
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
 		
 		/* Join up and check output length */
 		$data = implode($row, "");
 		if(strlen($data) != ($this -> getWidthBytes() * $this -> getHeight())) {
 			throw new Exception("Bug in " . __FUNCTION__ . ", wrong number of bytes.");
 		}
 		return $data;
	}
	
	/**
	 * Output image in column format. This format results in padding at the base and right of the image, if its height and width are not divisible by 8.
	 */
	public function toColumnFormat() {
		$widthPixels = $this -> getWidth();
		$heightPixels = $this -> getHeight();
		$widthBytes = $this -> getWidthBytes();
		$heightBytes = $this -> getHeightBytes();
		$x = $y = $bit = $byte = $byteVal = 0;
		$data = str_repeat("\255", $widthBytes * $heightBytes * 8);
 		do {
 			$byteVal |= (int)$this -> imgData[$y * $this -> imgWidth + $x] << (8 - $bit);
 			echo "($x, $y) $byte / $bit\t".((int)$byteVal)."\n";
 			$y++;
 			$bit++;
 			if($y >= $heightPixels) {
 				$y = 0;
 				$x++;
 				$bit = 8;
 				if($x >= $widthPixels) {
 					break;
 				}
 			}
 			if($bit >= 8) {
 				$data[$byte] = $byteVal;
 				$byteVal = 0;
 				$bit = 0;
 				$byte++;
 			}
 		} while(true);
		/* Check output length */
 		for($i = 0; $i < strlen($data); $i++) {
 			echo (int)$data[$i]. "\n";
 		}
  		if(strlen($data) != ($widthBytes * $heightBytes * 8)) {
  			throw new Exception("Bug in " . __FUNCTION__ . ", wrong number of bytes. Should be " . ($widthBytes * $heightBytes * 8) . " but was " . strlen($data));
  		}
		return $data;
	}
}