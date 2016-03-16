<?php

use Mike42\Escpos\EscposImage;

class EscposImageTest extends PHPUnit_Framework_TestCase {
	/**
	 * Checking loading of an empty image - requires no libraries
	 */
	public function testNoLibrariesBlank() {
		$this -> loadAndCheckImg(null, false, false, 0, 0, "");
	}

	/**
	 * BMP handling not yet implemented, but these will use
	 * a native PHP bitmap reader.
	 * This just tests that they are not being passed on to another library.
	 */
	public function testBmpBadFilename() {
		$this -> setExpectedException('Exception');
		$this -> loadAndCheckImg('not a real file.bmp', false, false, 1, 1, "\x80");
	}

	/**
	 * @medium
	 */
	public function testBmpBlack() {
		$this -> setExpectedException('Exception');
		$this -> loadAndCheckImg("canvas_black.bmp", false, false, 0, 0, "\x80");
	}

	/**
	 * @medium
	 */
	public function testBmpBlackWhite() {
		$this -> setExpectedException('Exception');
		$this -> loadAndCheckImg("black_white.bmp", false, false, 0, 0, "\xc0\x00");
	}

	/**
	 * @medium
	 */
	public function testBmpWhite() {
		$this -> setExpectedException('Exception');
		$this -> loadAndCheckImg("canvas_white.bmp", false, false, 0, 0, "\x00");
	}

	/**
	 * GD tests - Load tiny images and check how they are printed.
	 * These are skipped if you don't have gd.
	 */
	public function testGdBadFilename() {
		$this -> setExpectedException('Exception');
		$this -> loadAndCheckImg('not a real file.png', true, false, 1, 1, "\x80");
	}

	/**
	 * @medium
	 */
	public function testGdBlack() {
		foreach(array('png', 'jpg', 'gif') as $format) {
			$this -> loadAndCheckImg('canvas_black.' . $format, true, false, 1, 1, "\x80");
		}
	}

	/**
	 * @medium
	 */
	public function testGdBlackTransparent() {
		foreach(array('png', 'gif') as $format) {
			$this -> loadAndCheckImg('black_transparent.' . $format, true, false, 2, 2, "\xc0\x00");
		}
	}

	/**
	 * @medium
	 */
	public function testGdBlackWhite() {
		foreach(array('png', 'jpg', 'gif') as $format) {
			$this -> loadAndCheckImg('black_white.' . $format, true, false, 2, 2, "\xc0\x00");
		}
	}

	/**
	 * @medium
	 */
	public function testGdWhite() {
		foreach(array('png', 'jpg', 'gif') as $format) {
			$this -> loadAndCheckImg('canvas_white.' . $format, true, false, 1, 1, "\x00");
		}
	}

	/**
	 * Imagick tests - Load tiny images and check how they are printed
	 * These are skipped if you don't have imagick
	 */
	public function testImagickBadFilename() {
		$this -> setExpectedException('Exception');
		$this -> loadAndCheckImg('not a real file.png', false, true, 1, 1, "\x80");
	}

	/**
	 * @medium
	 */
	public function testImagickBlack() {
		foreach(array('png', 'jpg', 'gif') as $format) {
			$this -> loadAndCheckImg('canvas_black.' . $format, false, true, 1, 1, "\x80");
		}
	}

	/**
	 * @medium
	 */
	public function testImagickBlackTransparent() {
		foreach(array('png', 'gif') as $format) {
			$this -> loadAndCheckImg('black_transparent.' . $format, false, true, 2, 2, "\xc0\x00");
		}	
	}

	/**
	 * @medium
	 */
	public function testImagickBlackWhite() {
		foreach(array('png', 'jpg', 'gif') as $format) {
			$this -> loadAndCheckImg('black_white.' . $format, false, true, 2, 2, "\xc0\x00");
		}
	}

	/**
	 * @medium
	 */
	public function testImagickWhite() {
		foreach(array('png', 'jpg', 'gif') as $format) {
			$this -> loadAndCheckImg('canvas_white.' . $format, false, true, 1, 1, "\x00");
		}		
	}

	/**
	 * Mixed test - Same as above, but confirms that each tiny image can be loaded
	 * under any supported library configuration with the same results.
	 * These are skipped if you don't have gd AND imagick
	 */
	public function testLibraryDifferences() {
		if(!EscposImage::isGdLoaded() || !EscposImage::isImagickLoaded()) {
			$this -> markTestSkipped("both gd and imagick plugin are required for this test");
		}
		$inFile = array('black_white.png', 'canvas_black.png', 'canvas_white.png');
		foreach($inFile as $fn) {
			// Default check
			$im = new EscposImage(dirname(__FILE__) . "/resources/$fn");
			$width = $im -> getWidth();
			$height = $im -> getHeight();
			$data = $im -> toRasterFormat();
			// Gd check
			$this -> loadAndCheckImg($fn, true, false, $width, $height, $data);
			// Imagick check
			$this -> loadAndCheckImg($fn, false, true, $width, $height, $data);
		}
	}

	/**
	 * PDF tests - load tiny PDF and check for well-formedness
	 * These are also skipped if you don't have imagick
	 * @medium
	 */
	public function testPdfAllPages() {
		$this -> loadAndCheckPdf('doc.pdf', 1, 1, array("\x00", "\x80"));
	}

	public function testPdfBadFilename() {
		$this -> setExpectedException('Exception');
		$this -> loadAndCheckPdf('not a real file', 1, 1, array());
	}

	/**
	 * Load an EscposImage with (optionally) certain libraries disabled and run a check.
	 */
	private function loadAndCheckImg($fn, $gd, $imagick, $width, $height, $rasterFormat = null) {
		$img = $this -> getMockImage($fn === null ? null : dirname(__FILE__) . "/resources/$fn", $gd, $imagick);
		$this -> checkImg($img, $width, $height, $rasterFormat);
	}

	/**
	 * Same as above, loading document and checking pages against some expected values.
	 */
	private function loadAndCheckPdf($fn, $width, $height, array $rasterFormat = null) {
		if(!EscposImage::isImagickLoaded()) {
			$this -> markTestSkipped("imagick plugin required for this test");
		}
		$pdfPages = EscposImage::loadPdf(dirname(__FILE__) . "/resources/$fn", $width);
		$this -> assertTrue(count($pdfPages) == count($rasterFormat), "Got back wrong number of pages");
		foreach($pdfPages as $id => $img) {
			$this -> checkImg($img, $width, $height, $rasterFormat[$id]);
		}
	}

	/**
	 * Check image against known width, height, output.
	 */
	private function checkImg(EscposImage $img, $width, $height, $rasterFormat = null) {
		if($rasterFormat === null) {
			echo "\nImage was: " . $img -> getWidth() . "x" . $img -> getHeight() . ", data \"" . friendlyBinary($img -> toRasterFormat()) . "\"";
		}
		$this -> assertTrue($img -> getHeight() == $height);
		$this -> assertTrue($img -> getWidth() == $width);
		$this -> assertTrue($img -> toRasterFormat() == $rasterFormat);
	}

	/**
	 * Load up an EsposImage with given libraries disabled or enabled. Marks the test
	 * as skipped if you ask for a library which is not loaded.
	 */
	private function getMockImage($path, $gd, $imagick) {
		/* Sanity checks */
		if($gd && !EscposImage::isGdLoaded()) {
			$this -> markTestSkipped("gd plugin required for this test");
		}
		if($imagick && !EscposImage::isImagickLoaded()) {
			$this -> markTestSkipped("imagick plugin required for this test");
		}
		$stub = $this -> getMockBuilder('Mike42\Escpos\EscposImage')
				-> setMethods(array('isGdSupported', 'isImagickSupported'))
				-> disableOriginalConstructor()
				-> getMock();
		$stub -> method('isGdSupported')
				-> willReturn($gd);
		$stub -> method('isImagickSupported')
				-> willReturn($imagick);
		$stub -> __construct($path);
		return $stub;
	}
}