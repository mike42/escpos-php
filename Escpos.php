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
 * This class generates ESC/POS printer control commands for compatible printers.
 * See README.md for a summary of compatible printers and supported commands, and
 * basic usage.
 * 
 * See example/demo.php for a detailed print-out demonstrating the range of commands
 * implemented in this project.
 * 
 * Note that some functions have not been implemented:
 * 		- Set paper sensors
 * 		- Select print colour
 * 		- Code93 and code128 barcodes
 * 
 * Please direct feature requests, bug reports and contributions to escpos-php
 * on Github:
 * 		- https://github.com/mike42/escpos-php
 */
require_once(dirname(__FILE__) . "/src/EscposImage.php");
require_once(dirname(__FILE__) . "/src/PrintBuffer.php");
require_once(dirname(__FILE__) . "/src/EscposPrintBuffer.php");
require_once(dirname(__FILE__) . "/src/PrintConnector.php");
require_once(dirname(__FILE__) . "/src/WindowsPrintConnector.php");
require_once(dirname(__FILE__) . "/src/FilePrintConnector.php");
require_once(dirname(__FILE__) . "/src/NetworkPrintConnector.php");
require_once(dirname(__FILE__) . "/src/AbstractCapabilityProfile.php");
require_once(dirname(__FILE__) . "/src/DefaultCapabilityProfile.php");
require_once(dirname(__FILE__) . "/src/SimpleCapabilityProfile.php");
require_once(dirname(__FILE__) . "/src/EposTepCapabilityProfile.php");
require_once(dirname(__FILE__) . "/src/StarCapabilityProfile.php");
require_once(dirname(__FILE__) . "/src/CodePage.php");
require_once(dirname(__FILE__) . "/src/ImagePrintBuffer.php");

class Escpos {
	/* ASCII codes */
	const NUL = "\x00";
	const LF = "\x0a";
	const ESC = "\x1b";
	const FS = "\x1c";
	const GS = "\x1d";

	/* Barcode types */
	const BARCODE_UPCA = 0;
	const BARCODE_UPCE = 1;
	const BARCODE_JAN13 = 2;
	const BARCODE_JAN8 = 3;
	const BARCODE_CODE39 = 4;
	const BARCODE_ITF = 5;
	const BARCODE_CODABAR = 6;
		
	/* Cut types */
	const CUT_FULL = 65;
	const CUT_PARTIAL = 66;
	
	/* Fonts */
	const FONT_A = 0;
	const FONT_B = 1;
	const FONT_C = 2;
	
	/* Image sizing options */
	const IMG_DEFAULT = 0;
	const IMG_DOUBLE_WIDTH = 1;
	const IMG_DOUBLE_HEIGHT = 2;
	
	/* Justifications */
	const JUSTIFY_LEFT = 0;
	const JUSTIFY_CENTER = 1;
	const JUSTIFY_RIGHT = 2;
	
	/* Print mode constants */
	const MODE_FONT_A = 0;
	const MODE_FONT_B = 1;
	const MODE_EMPHASIZED = 8;
	const MODE_DOUBLE_HEIGHT = 16;
	const MODE_DOUBLE_WIDTH = 32;
	const MODE_UNDERLINE = 128;
	
	/* QR code error correction levels */
	const QR_ECLEVEL_L = 0;
	const QR_ECLEVEL_M = 1;
	const QR_ECLEVEL_Q = 2;
	const QR_ECLEVEL_H = 3;
	
	/* QR code models */
	const QR_MODEL_1 = 1;
	const QR_MODEL_2 = 2;
	const QR_MICRO = 3;
	
	/* Underline */
	const UNDERLINE_NONE = 0;
	const UNDERLINE_SINGLE = 1;
	const UNDERLINE_DOUBLE = 2;
	
	/**
	 * @var PrintBuffer The printer's output buffer.
	 */
	private $buffer;
	
	/**
	 * @var PrintConnector
	 */
	private $connector;

	/**
	 * @var AbstractCapabilityProfile
	 */
	private $profile;
	
	/**
	 * @var int Current character code table
	 */
	private $characterTable;
	
	/**
	 * Construct a new print object
	 * 
	 * @param PrintConnector $connector The PrintConnector to send data to. If not set, output is sent to standard output.
	 */
	function __construct(PrintConnector $connector = null) {
		if(is_null($connector)) {
			if(php_sapi_name() == 'cli') {
				$connector = new FilePrintConnector("php://stdout");
			} else {
				throw new InvalidArgumentException("Argument passed to Escpos::__construct() must implement interface PrintConnector, null given.");
			}
		}
		$this -> connector = $connector;
		$this -> profile = DefaultCapabilityProfile::getInstance();
		$buffer = new EscposPrintBuffer();
		$this -> buffer = null;
		$this -> setPrintBuffer($buffer);
		$this -> initialize();
	}
	
	/**
	 * Print a barcode.
	 *
	 * @param string $content The information to encode.
	 * @param int $type The barcode standard to output. If not specified, `Escpos::BARCODE_CODE39` will be used.
	 */
	function barcode($content, $type = self::BARCODE_CODE39) {
		// TODO validation on barcode() inputs
		$this -> connector -> write(self::GS . "k" . chr($type) . $content . self::NUL);
	}
	
	/**
	 * Print an image, using the older "bit image" command. This creates padding on the right of the image,
	 * if its width is not divisible by 8.
	 * 
	 * Should only be used if your printer does not support the graphics() command.
	 * 
	 * @param EscposImage $img The image to print
	 * @param EscposImage $size Size modifier for the image.
	 */
	function bitImage(EscposImage $img, $size = self::IMG_DEFAULT) {
		self::validateInteger($size, 0, 3, __FUNCTION__);
		$header = self::dataHeader(array($img -> getWidthBytes(), $img -> getHeight()), true);
		$this -> connector -> write(self::GS . "v0" . chr($size) . $header);
		$this -> connector -> write($img -> toRasterFormat());
	}
	
	/**
	 * Close the underlying buffer. With some connectors, the
	 * job will not actually be sent to the printer until this is called.
	 */
	function close() {
		$this -> connector -> finalize();
	}
	
	/**
	 * Cut the paper.
	 *
	 * @param int $mode Cut mode, either Escpos::CUT_FULL or Escpos::CUT_PARTIAL. If not specified, `Escpos::CUT_FULL` will be used.
	 * @param int $lines Number of lines to feed
	 */
	function cut($mode = self::CUT_FULL, $lines = 3) {
		// TODO validation on cut() inputs
		$this -> connector -> write(self::GS . "V" . chr($mode) . chr($lines));
	}
	
	/**
	 * Print and feed line / Print and feed n lines.
	 * 
	 * @param int $lines Number of lines to feed
	 */
	function feed($lines = 1) {
		self::validateInteger($lines, 1, 255, __FUNCTION__);
		if($lines <= 1) {
			$this -> connector -> write(self::LF);
		} else {
			$this -> connector -> write(self::ESC . "d" . chr($lines));
		}
	}
	
	/**
	 * Print and reverse feed n lines.
	 *
	 * @param int $lines number of lines to feed. If not specified, 1 line will be fed.
	 */
	function feedReverse($lines = 1) {
		self::validateInteger($lines, 1, 255, __FUNCTION__);
		$this -> connector -> write(self::ESC . "e" . chr($lines));
	}

	/**
	 * @return number
	 */
	function getCharacterTable() {
		return $this -> characterTable;
	}
	
	/**
	 * @return PrintBuffer
	 */
	function getPrintBuffer() {
		return $this -> buffer;
	}

	/**
	 * @return PrintConnector
	 */
	function getPrintConnector() {
		return $this -> connector;
	}

	/**
	 * @return AbstractCapabilityProfile
	 */
	function getPrinterCapabilityProfile() {
		return $this -> profile;
	}
	
	/**
	 * Print an image to the printer.
	 * 
	 * Size modifiers are:
	 * - IMG_DEFAULT (leave image at original size)
	 * - IMG_DOUBLE_WIDTH
	 * - IMG_DOUBLE_HEIGHT
	 * 
	 * See the example/ folder for detailed examples.
	 * 
	 * The function bitImage() takes the same parameters, and can be used if
	 * your printer doesn't support the newer graphics commands.
	 * 
	 * @param EscposImage $img The image to print.
	 * @param int $size Output size modifier for the image.
	 */
	function graphics(EscposImage $img, $size = self::IMG_DEFAULT) {
		self::validateInteger($size, 0, 3, __FUNCTION__);
		$imgHeader = self::dataHeader(array($img -> getWidth(), $img -> getHeight()), true);
		$tone = '0';
		$colors = '1';
		$xm = (($size & self::IMG_DOUBLE_WIDTH) == self::IMG_DOUBLE_WIDTH) ? chr(2) : chr(1);
		$ym = (($size & self::IMG_DOUBLE_HEIGHT) == self::IMG_DOUBLE_HEIGHT) ? chr(2) : chr(1);
		$header = $tone . $xm . $ym . $colors . $imgHeader;
		$this -> wrapperSendGraphicsData('0', 'p', $header . $img -> toRasterFormat());
		$this -> wrapperSendGraphicsData('0', '2');
	}
	
	/**
	 * Initialize printer. This resets formatting back to the defaults.
	 */
	function initialize() {
		$this -> connector -> write(self::ESC . "@");
		$this -> characterTable = 0;
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
		self::validateInteger($pin, 0, 1, __FUNCTION__);
		self::validateInteger($on_ms, 1, 511, __FUNCTION__);
		self::validateInteger($off_ms, 1, 511, __FUNCTION__);
		$this -> connector -> write(self::ESC . "p" . chr($pin + 48) . chr($on_ms / 2) . chr($off_ms / 2));
	}
	
	/**
	 * Print the given data as a QR code on the printer.
	 * 
	 * @param string $content The content of the code. Numeric data will be more efficiently compacted.
	 * @param int $ec Error-correction level to use. One of Escpos::QR_ECLEVEL_L (default), Escpos::QR_ECLEVEL_M, Escpos::QR_ECLEVEL_Q or Escpos::QR_ECLEVEL_H. Higher error correction results in a less compact code.
	 * @param int $size Pixel size to use. Must be 1-16 (default 3)
	 * @param int $model QR code model to use. Must be one of Escpos::QR_MODEL_1, Escpos::QR_MODEL_2 (default) or Escpos::QR_MICRO (not supported by all printers).
	 */
	function qrCode($content, $ec = self::QR_ECLEVEL_L, $size = 3, $model = self::QR_MODEL_2) {
		self::validateString($content, __FUNCTION__);
		self::validateInteger($ec, 0, 3, __FUNCTION__);
		self::validateInteger($size, 1, 16, __FUNCTION__);
		self::validateInteger($model, 1, 3, __FUNCTION__);
		if($content == "") {
			return;
		}
		if(!$this -> profile -> getSupportsQrCode()) {
			// TODO use software rendering via phpqrcode instead
			throw new Exception("QR codes are not supported on your printer.");
		}
		$cn = '1'; // Code type for QR code
		// Select model: 1, 2 or micro.
		$this -> wrapperSend2dCodeData(chr(65), $cn, chr(48 + $model) . chr(0));
		// Set dot size.
		$this -> wrapperSend2dCodeData(chr(67), $cn, chr($size));
		// Set error correction level: L, M, Q, or H
		$this -> wrapperSend2dCodeData(chr(69), $cn, chr(48 + $ec));
		// Send content & print
		$this -> wrapperSend2dCodeData(chr(80), $cn, $content, '0');
		$this -> wrapperSend2dCodeData(chr(81), $cn, '', '0');
	}

	/**
	 * Switch character table (code page) manually. Used in conjunction with textRaw() to
	 * print special characters which can't be encoded automatically.
	 * 
	 * @param int $table The table to select. Available code tables are model-specific.
	 */
	function selectCharacterTable($table = 0) {
		self::validateInteger($table, 0, 255, __FUNCTION__);
		$supported = $this -> profile -> getSupportedCodePages();
		if(!isset($supported[$table])) {
			throw new InvalidArgumentException("There is no code table $table allowed by this printer's capability profile.");
		}
		$this -> characterTable = $table;
		if($this -> profile -> getSupportsStarCommands()) {
			/* Not an ESC/POS command: STAR printers stash all the extra code pages under a different command. */
			$this -> connector -> write(self::ESC . self::GS . "t" . chr($table));
			return;
		}
		$this -> connector -> write(self::ESC . "t" . chr($table));
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

		$this -> connector -> write(self::ESC . "!" . chr($mode));
	}
	
	/**
	 * Set barcode height.
	 *
	 * @param int $height Height in dots. If not specified, 8 will be used.
	 */
	function setBarcodeHeight($height = 8) {
		self::validateInteger($height, 1, 255, __FUNCTION__);
		$this -> connector -> write(self::GS . "h" . chr($height));
	}
	
	/**
	 * Turn double-strike mode on/off.
	 *
	 * @param boolean $on true for double strike, false for no double strike
	 */
	function setDoubleStrike($on = true) {
		self::validateBoolean($on, __FUNCTION__);
		$this -> connector -> write(self::ESC . "G". ($on ? chr(1) : chr(0)));
	}
	
	/**
	 * Turn emphasized mode on/off.
	 *
	 *  @param boolean $on true for emphasis, false for no emphasis
	 */
	function setEmphasis($on = true) {
		self::validateBoolean($on, __FUNCTION__);
		$this -> connector -> write(self::ESC . "E". ($on ? chr(1) : chr(0)));
	}
	
	/**
	 * Select font. Most printers have two fonts (Fonts A and B), and some have a third (Font C).
	 *
	 * @param int $font The font to use. Must be either Escpos::FONT_A, Escpos::FONT_B, or Escpos::FONT_C.
	 */
	function setFont($font = self::FONT_A) {
		self::validateInteger($font, 0, 2, __FUNCTION__);
		$this -> connector -> write(self::ESC . "M" . chr($font));
	}
	
	/**
	 * Select justification.
	 *
	 * @param int $justification One of Escpos::JUSTIFY_LEFT, Escpos::JUSTIFY_CENTER, or Escpos::JUSTIFY_RIGHT.
	 */
	function setJustification($justification = self::JUSTIFY_LEFT) {
		self::validateInteger($justification, 0, 2, __FUNCTION__);
		$this -> connector -> write(self::ESC . "a" . chr($justification));
	}
	
	/**
	 * Attach a different print buffer to the printer. Buffers are responsible for handling text output to the printer.
	 * 
	 * @param PrintBuffer $buffer The buffer to use.
	 * @throws InvalidArgumentException Where the buffer is already attached to a different printer.
	 */
	function setPrintBuffer(PrintBuffer $buffer) {
		if($buffer === $this -> buffer) {
			return;
		}
		if($buffer -> getPrinter() != null) {
			throw new InvalidArgumentException("This buffer is already attached to a printer.");
		}
		if($this -> buffer !== null) {
			$this -> buffer -> setPrinter(null);
		}
		$this -> buffer = $buffer;
		$this -> buffer -> setPrinter($this);
	}
	
	/**
	 * Use an alternative capability profile for this printer.
	 * 
	 * @param AbstractCapabilityProfile $profile Profile to use
	 */
	function setPrinterCapabilityProfile(AbstractCapabilityProfile $profile) {
		$this -> profile = $profile;
	}
	
	/**
	 * Set black/white reverse mode on or off. In this mode, text is printed white on a black background.
	 * 
	 * @param boolean $on True to enable, false to disable.
	 */
	function setReverseColors($on = true) {
		self::validateBoolean($on, __FUNCTION__);
		$this -> connector -> write(self::GS . "B" . ($on ? chr(1) : chr(0)));
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
		$this -> connector -> write(self::ESC . "-". chr($underline));
	}
	
	/**
	 * Add text to the buffer.
	 *
	 * Text should either be followed by a line-break, or feed() should be called
	 * after this to clear the print buffer.
	 *
	 * @param string $str Text to print
	 */
	function text($str = "") {
		self::validateString($str, __FUNCTION__);
		$this -> buffer -> writeText((string)$str);
	}
	
	/**
	 * Add text to the buffer without attempting to interpret chararacter codes.
	 *
	 * Text should either be followed by a line-break, or feed() should be called
	 * after this to clear the print buffer.
	 *
	 * @param string $str Text to print
	 */
	function textRaw($str = "") {
		self::validateString($str, __FUNCTION__);
		$this -> buffer -> writeTextRaw((string)$str);
	}
	
	/**
	 * Wrapper for GS ( k, to calculate and send correct data length.
	 * 
	 * @param string $fn Function to use
	 * @param string $cn Output code type. Affects available data
	 * @param string $data Data to send.
	 * @param string $m Modifier/variant for function. Often '0' where used.
	 * @throws InvalidArgumentException Where the input lengths are bad.
	 */
	private function wrapperSend2dCodeData($fn, $cn, $data = '', $m = '') {
		if(strlen($m) > 1 || strlen($cn) != 1 || strlen($fn) != 1) {
			throw new InvalidArgumentException("wrapperSend2dCodeData: cn and fn must be one character each.");
		}
		$header = $this -> intLowHigh(strlen($data) + strlen($m) + 2, 2);
		$this -> connector -> write(self::GS . "(k" . $header . $cn . $fn . $m . $data);
	}
	
	/**
	 * Wrapper for GS ( L, to calculate and send correct data length.
	 *
	 * @param string $m Modifier/variant for function. Usually '0'.
	 * @param string $fn Function number to use, as character.
	 * @param string $data Data to send.
	 * @throws InvalidArgumentException Where the input lengths are bad.
	 */
	private function wrapperSendGraphicsData($m, $fn, $data = '') {
		if(strlen($m) != 1 || strlen($fn) != 1) {
			throw new InvalidArgumentException("wrapperSendGraphicsData: m and fn must be one character each.");
		}
		$header = $this -> intLowHigh(strlen($data) + 2, 2);
		$this -> connector -> write(self::GS . "(L" . $header . $m . $fn . $data);
	}
	
	/**
	 * Convert widths and heights to characters. Used before sending graphics to set the size.
	 * 
	 * @param array $inputs
	 * @param boolean $long True to use 4 bytes, false to use 2
	 * @return string
	 */
	private static function dataHeader(array $inputs, $long = true) {
		$outp = array();
		foreach($inputs as $input) {
			if($long) {
				$outp[] = Escpos::intLowHigh($input, 2);
			} else {
				self::validateInteger($input, 0 , 255, __FUNCTION__);
				$outp[] = chr($input);
			}
		}
		return implode("", $outp);
	}
	
	/**
	 * Generate two characters for a number: In lower and higher parts, or more parts as needed.
	 * @param int $int Input number
	 * @param int $length The number of bytes to output (1 - 4).
	 */
	private static function intLowHigh($input, $length) {
		$maxInput = (256 << ($length * 8) - 1);
		self::validateInteger($length, 1, 4, __FUNCTION__);
		self::validateInteger($input, 0, $maxInput, __FUNCTION__);
		$outp = "";
		for($i = 0; $i < $length; $i++) {
			$outp .= chr($input % 256);
			$input = (int)($input / 256);
		}
		return $outp;
	}
	
	/**
	 * Throw an exception if the argument given is not a boolean
	 * 
	 * @param boolean $test the input to test
	 * @param string $source the name of the function calling this
	 */
	protected static function validateBoolean($test, $source) {
		if(!($test === true || $test === false)) {
			throw new InvalidArgumentException("Argument to $source must be a boolean");
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
	protected static function validateInteger($test, $min, $max, $source) {
		if(!is_integer($test) || $test < $min || $test > $max) {
			throw new InvalidArgumentException("Argument to $source must be a number between $min and $max, but $test was given.");
		}
	}
	
	/**
	 * Throw an exception if the argument given can't be cast to a string
	 *
	 * @param string $test the input to test
	 * @param string $source the name of the function calling this
	 */
	protected static function validateString($test, $source) {
		if (is_object($test) && !method_exists($test, '__toString')) {
			throw new InvalidArgumentException("Argument to $source must be a string");
		}
	}
}
