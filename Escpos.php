<?php
/**
 * escpos-php, a Thermal receipt printer library, for use with
 * ESC/POS compatible printers
 *
 * Copyright (c) 2014-2015 Michael Billington <michael.billington@gmail.com>
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
 * Class for generating ESC/POS printer control commands, as documented at the following URL:
 * http://content.epson.de/fileadmin/content/files/RSD/downloads/escpos.pdf
 * 
 * See test.php for example usage.
 * Some functions have not been implemented:
 * 		- set paper sensors
 * 		- graphical output
 * 		- select print colour
 * 		- select character code table
 * 		- turn white/black reverse printing mode on/off
 * 		- code93 and code128 barcodes‎
 */
class Escpos {
	/* ASCII codes */
	const NUL = "\x00";
	const LF = "\x0a";
	const ESC = "\x1b";
	const GS = "\x1d";
	
	/* Print mode constants */
	const MODE_FONT_A = 0;
	const MODE_FONT_B = 1;
	const MODE_EMPHASIZED = 8;
	const MODE_DOUBLE_HEIGHT = 16;
	const MODE_DOUBLE_WIDTH = 32;
	const MODE_UNDERLINE = 128;
	
	/* Fonts */
	const FONT_A = 0;
	const FONT_B = 1;
	const FONT_C = 2;
	
	/* Justifications */
	const JUSTIFY_LEFT = 0;
	const JUSTIFY_CENTER = 1;
	const JUSTIFY_RIGHT = 2;
	
	/* Cut types */
	const CUT_FULL = 65;
	const CUT_PARTIAL = 66;
	
	/* Underline */
	const UNDERLINE_NONE = 0;
	const UNDERLINE_SINGLE = 1;
	const UNDERLINE_DOUBLE = 2;

	/* Barcode types */
	const BARCODE_UPCA = 0;
	const BARCODE_UPCE = 1;
	const BARCODE_JAN13 = 2;
	const BARCODE_JAN8 = 3;
	const BARCODE_CODE39 = 4;
	const BARCODE_ITF = 5;
	const BARCODE_CODABAR = 6;
	private $fp;
	
	/**
	 * @param resource $fp File pointer to print to
	 */
	function __construct($fp = null) {
		if(is_null($fp)) {
			$fp = fopen("php://stdout", "wb");
		}
		$this -> fp = $fp;
		
		$this -> initialize();
	}

	/**
	 * Add text to the buffer.
	 *
	 * Text should either be followed by a line-break, or feed() should be called after this.
	 * 
	 * @param string $str Text to print
	 */
	function text($str = "") {
		self::validateString($str, __FUNCTION__);
		fwrite($this -> fp, (string)$str);
	}

	/**
	 * Print and feed line / Print and feed n lines.
	 * 
	 * @param int $lines Number of lines to feed
	 */
	function feed($lines = 1) {
		self::validateInteger($lines, 1, 255, __FUNCTION__);
		if($lines <= 1) {
			fwrite($this -> fp, self::LF);
		} else {
			fwrite($this -> fp, self::ESC . "d" . chr($lines));
		}
	}
	
	/**
	 * Select print mode(s).
	 * 
	 * Several MODE_* constants can be OR'd together passed to this function's `$mode` argument. The valid modes are:
	 *  - MODE_FONT_A
	 *  - MODE_FONT_B
	 *  - MODE_EMPHASIZED
	 *  - MODE_DOUBLE_HEIGHT
	 *  - MODE_DOUBLE_WIDTH
	 *  - MODE_UNDERLINE
	 * 
	 * @param int $mode The mode to use. Default is Escpos::MODE_FONT_A, with no special formatting. This has a similar effect to running initialize().
	 */
	function selectPrintMode($mode = self::MODE_FONT_A) {
		$allModes = self::MODE_FONT_B | self::MODE_EMPHASIZED | self::MODE_DOUBLE_HEIGHT | self::MODE_DOUBLE_WIDTH | self::MODE_UNDERLINE;
		if(!is_integer($mode) || $mode < 0 || ($mode & $allModes) != $mode) {
			throw new InvalidArgumentException("Test");
		}

		fwrite($this -> fp, self::ESC . "!" . chr($mode));
	}
	
	/**
	 * Set underline for printed text.
	 * 
	 * Argument can be true/false, or one of UNDERLINE_NONE,
	 * UNDERLINE_SINGLE or UNDERLINE_DOUBLE.
	 * 
	 * @param int $underline Either true/false, or one of Escpos::UNDERLINE_NONE, Escpos::UNDERLINE_SINGLE or Escpos::UNDERLINE_DOUBLE. Defaults to Escpos::UNDERLINE_SINGLE.
	 */
	function setUnderline($underline = self::UNDERLINE_SINGLE) {
		/* Map true/false to underline constants */
		if($underline === true) {
			$underline = self::UNDERLINE_SINGLE;
		} else if($underline === false) {
			$underline = self::UNDERLINE_NONE;
		}
		/* Set the underline */
		self::validateInteger($underline, 0, 2, __FUNCTION__);
		fwrite($this -> fp, self::ESC . "-". chr($underline));
	}
	
	/**
	 * Initialize printer. This resets formatting back to the defaults.
	 */
	function initialize() {
		fwrite($this -> fp, self::ESC . "@");
	}
	
	/**
	 * Turn emphasized mode on/off.
	 * 
	 *  @param boolean $on true for emphasis, false for no emphasis
	 */
	function setEmphasis($on = true) {
		self::validateBoolean($on, __FUNCTION__);
		fwrite($this -> fp, self::ESC . "E". ($on ? chr(1) : chr(0)));
	}
	
	/**
	 * Turn double-strike mode on/off.
	 * 
	 * @param boolean $on true for double strike, false for no double strike
	 */
	function setDoubleStrike($on = true) {
		self::validateBoolean($on, __FUNCTION__);
		fwrite($this -> fp, self::ESC . "G". ($on ? chr(1) : chr(0)));
	}
	
	/**
	 * Select font. Most printers have two fonts (Fonts A and B), and some have a third (Font C).
	 * 
	 * @param int $font The font to use. Must be either Escpos::FONT_A, Escpos::FONT_B, or Escpos::FONT_C.
	 */
	function setFont($font = self::FONT_A) {
		self::validateInteger($font, 0, 2, __FUNCTION__);
		fwrite($this -> fp, self::ESC . "M" . chr($font));
	}

	/**
	 * Select justification.
	 * 
	 * @param int $justification One of Escpos::JUSTIFY_LEFT, Escpos::JUSTIFY_CENTER, or Escpos::JUSTIFY_RIGHT.
	 */
	function setJustification($justification = self::JUSTIFY_LEFT) {
		self::validateInteger($justification, 0, 2, __FUNCTION__);
		fwrite($this -> fp, self::ESC . "a" . chr($justification));
	}
	
	/**
	 * Print and reverse feed n lines.
	 * 
	 * @param int $lines number of lines to feed. If not specified, 1 line will be fed.
	 */
	function feedReverse($lines = 1) {
		self::validateInteger($lines, 1, 255, __FUNCTION__);
		fwrite($this -> fp, self::ESC . "e" . chr($lines));
	}
	
	/**
	 * Cut the paper.
	 * 
	 * @param int $mode Cut mode, either Escpos::CUT_FULL or Escpos::CUT_PARTIAL. If not specified, `Escpos::CUT_FULL` will be used.
	 * @param int $lines Number of lines to feed
	 */
	function cut($mode = self::CUT_FULL, $lines = 3) {
		// TODO validation on cut() inputs
		fwrite($this -> fp, self::GS . "V" . chr($mode) . chr($lines));
	}

	/**
	 * Set barcode height.
	 * 
	 * @param int $height Height in dots. If not specified, 8 will be used.
	 */
	function setBarcodeHeight($height = 8) {
		self::validateInteger($height, 1, 255, __FUNCTION__);
		fwrite($this -> fp, self::GS . "h" . chr($height));
	}
	
	/**
	 * Print a barcode.
	 * 
	 * @param string $content The information to encode.
	 * @param int $type The barcode standard to output. If not specified, `Escpos::BARCODE_CODE39` will be used.
	 */
	function barcode($content, $type = self::BARCODE_CODE39) {
		// TODO validation on barcode() inputs
		fwrite($this -> fp, self::GS . "k" . chr($type) . $content . self::NUL);
	}

	/**
	 * Generate a pulse, for opening a cash drawer if one is connected.
	 * The default settings should open an Epson drawer.
	 *
	 * @param int $pin 0 or 1, for pin 2 or pin 5 kick-out connector respectively.
	 * @param int $on_ms pulse ON time, in milliseconds.
	 * @param int $off_ms pulse OFF time, in milliseconds.
	 */
	function pulse($pin = 0, $on_ms = 120, $off_ms = 240) {
		// TODO validation on pulse() inputs
		fwrite($this -> fp, self::ESC . "p" . chr($pin + 48) . chr($on_ms / 2) . chr($off_ms / 2));
	}

	/**
	 * Throw an exception if the argument given is not a boolean
	 * 
	 * @param boolean $test the input to test
	 * @param string $source the name of the function calling this
	 */
	private static function validateBoolean($test, $source) {
		if(!($test === true || $test === false)) {
			throw new InvalidArgumentException("Argument to $source must be a boolean");
		}
	}

	/**
	 * Throw an exception if the argument given can't be cast to a string
	 * 
	 * @param string $test the input to test
	 * @param string $source the name of the function calling this
	 */
	private static function validateString($test, $source) {
		if (is_object($test) && !method_exists($test, '__toString')) {
			throw new InvalidArgumentException("Argument to $source must be a string");
		}
	}

	/**
	 * Throw an exception if the argument given is not an integer within the specified range
	 * 
	 * @param int $test the input to test
	 * @param int $min the minimum allowable value (inclusive)
	 * @param int $max the maximum allowable value (inclusive)
	 * @param string $source the name of the function calling this
	 */
	private static function validateInteger($test, $min, $max, $source) {
		if(!is_integer($test) || $test < $min || $test > $max) {
			throw new InvalidArgumentException("Argument to $source must be a number between $min and $max");
		}
	}
}
