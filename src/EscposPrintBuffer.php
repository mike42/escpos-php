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
 * This class wraps the print connector and manages newlines and character encoding.
 * 
 * It is clearly a work in progress, details at https://github.com/mike42/escpos-php/issues/6
 */
class EscposPrintBuffer {
	/**
	 * This array maps Escpos character tables to names iconv encodings
	 */
// 	TODO Not yet used
// 	private static $characterMaps = array(
// 			Escpos::CHARSET_CP437 => "CP437",
// 			Escpos::CHARSET_CP850 => "CP850",
// 			Escpos::CHARSET_CP860 => "CP860",
// 			Escpos::CHARSET_CP863 => "CP863",
// 			Escpos::CHARSET_CP865 => "CP865",
// 			Escpos::CHARSET_CP851 => "CP851",
// 			Escpos::CHARSET_CP857 => "CP857",
// 			Escpos::CHARSET_CP737 => "CP737",
// 			Escpos::CHARSET_ISO8859_7 => "ISO_8859-7",
// 			Escpos::CHARSET_CP1252 => "CP1252",
// 			Escpos::CHARSET_CP866 => "CP866",
// 			Escpos::CHARSET_CP852 => "CP852",
// 			Escpos::CHARSET_TCVN3_1 => "TCVN",
// 			Escpos::CHARSET_CP775 => "CP775",
// 			Escpos::CHARSET_CP855 => "CP855",
// 			Escpos::CHARSET_CP861 => "CP861",
// 			Escpos::CHARSET_CP862 => "CP862",
// 			Escpos::CHARSET_CP864 => "CP864",
// 			Escpos::CHARSET_CP869 => "CP869",
// 			Escpos::CHARSET_ISO8859_2 => "ISO_8859-2",
// 			Escpos::CHARSET_ISO8859_15 => "ISO_8859-15",
// 			Escpos::CHARSET_CP1125 => "CP1125",
// 			Escpos::CHARSET_CP1250 => "CP1250",
// 			Escpos::CHARSET_CP1251 => "CP1251",
// 			Escpos::CHARSET_CP1253 => "CP1253",
// 			Escpos::CHARSET_CP1254 => "CP1254",
// 			Escpos::CHARSET_CP1255 => "CP1255",
// 			Escpos::CHARSET_CP1256 => "CP1256",
// 			Escpos::CHARSET_CP1257 => "CP1257",
// 			Escpos::CHARSET_CP1258 => "CP1258",
// 			Escpos::CHARSET_RK1048 => "RK1048"
// 		);
	// List of available characters
	private static $available = null;

	private static $availableFile = "/charmap-available.ser";

	// True if we are auto-switching 
	private $auto;

	// Current character table (ESC/POS table number)
	private $characterTable;

	// File pointer for output
	private $connector;
	
	// Printer for output
	private $printer;

	function __construct(Escpos $printer, PrintConnector $connector) {
// 		ini_set('mbstring.substitute_character', "?");
		$this -> connector = $connector;
		$this -> printer = $printer;

// 	TODO Not yet used
// 		$this -> auto = true;
// 		$this -> characterTable = Escpos::CHARSET_CP437;
//  		if(self::$available == null) {
//  			self::$available = self::loadAvailableCharacters();
//  		}
	}

	/**
	 * Finalize the underlying connector
	 */
	function finalize() {
		// TODO final line break if needed
		$this -> connector -> finalize();
	}
	
	static function generateAvailableCharacters() {
		throw new Exception("Not implemented");
		// 	TODO Not yet used
// 		$encode = array();
// 		$available = array();
// 		foreach(self::$characterMaps as $num => $characterMap) {
// 			for($char = 128; $char <= 255; $char++) {
// 				$utf8 = @iconv($characterMap, 'UTF-8', chr($char));
// 				if($utf8 == '') {
// 					continue;
// 				}
// 				if(iconv('UTF-8', $characterMap, $utf8) != chr($char)) {
// 					continue;
// 				}
// 				if(!isset($available[$utf8])) {
// 					$available[$utf8] = array();
// 				}
// 				$available[$utf8][$num] = true;
// 			}
// 		}
// 		/* Attempt to cache, but don't worry if we can't */
// 		$data = serialize($available);
// 		if($data !== false) {
// 			@file_put_contents(dirname(__FILE__) . self::$availableFile, $data);
// 		}
// 		return $available;
	}

	static function loadAvailableCharacters() {
		throw new Exception("Not implemented");
		// 	TODO Not yet used
// 		if(file_exists(dirname(__FILE__) . self::$availableFile)) {
// 			return unserialize(file_get_contents(dirname(__FILE__) . self::$availableFile));
// 		}
// 		return self::generateAvailableCharacters();
	}

	function getCharacterTable() {
		// 	TODO Not yet used
		throw new Exception("Not implemented");
//		return $this -> characterTable;
	}

	// Multibyte
	function writeText($text) {
		$this -> write($text);
		
		
// 		// 	TODO Not yet used
// 		if($text == null) {
// 			return;
// 		}
// 		if(!mb_detect_encoding($text, 'UTF-8', true)) {
// 			// Assume that the user has already put non-UTF8 into the target encoding.
// 			return $this -> writeTextRaw($text);
// 		}
// 		if(!$this -> auto) {
// 			// If we are not auto-switching characters, then pass it on directly
// 			$encoding = $this -> characterTable;
// 			return $this -> writeTextUsingEncoding($text, $encoding);
// 		}
// 		$i = 0;
// 		$j = 0;
// 		while($i < mb_strlen($text)) {
// 			$encoding = $this -> identify($text);
// 			$j = 0;
// 			do {
// 				$char = mb_substr($text, $i, 1);
// 				$matching = !isset(self::$available[$char]) || (isset(self::$available[$char][$encoding]));
// 				$i++;
// 				$j++;
// 			} while($matching);
// 			$this -> writeTextUsingEncoding(mb_substr($text, $i - $j, $j), $encoding);
// 		}	
	}

	// Multibyte
	private function writeTextUsingEncoding($text, $encodingNo) {
		throw new Exception("Not implemented");
		// 	TODO Not yet used
// 		$encoding = self::$characterMaps[$encodingNo];
// 		$rawText = str_repeat("?", mb_strlen($text));
// 		for($i = 0; $i < mb_strlen($text); $i++) {
// 			$char = "a";//mb_substr($text, $i, 1);
// 			echo $char . "\n";
// echo $encoding . "\n";
// 			$rawChar = iconv('UTF-8', $encoding, $text);
// 			if(strlen($rawChar) == 1) {
// 				$rawText[$i] = $rawChar;
// 			}
// 		}

// 		echo $rawText . "\n";
// die();


		

// 		$this -> writeTextRaw($rawText);
// 		// If encoding is not current encoding.. send switch code.
		
		
// 		// Call writeTextRaw on the result of iconv'ing as many characters as we can represent.
		
// 		// Return any remaining characters.
	}

	// Single-byte, in current encoding. Non-printable characters will be stripped out here.
	function writeTextRaw($text) {
		
		// TODO Call write, passing on printable characters only!
		$this -> write($text);
	}

	function write($data) {
		$this -> connector -> write($data);
	}

	// Figure out what encoding some text is
	private function identify($text) {
		throw new Exception("Not implemented");
		// 	TODO Not yet used
		//return Escpos::CHARSET_CP437;
	}
}
