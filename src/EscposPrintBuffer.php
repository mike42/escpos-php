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
	// List of available characters
	private static $available = null;

	private static $availableFile = "/charmap-available.ser";

	// True if we are auto-switching 
	private $auto;

	// Current character table (ESC/POS table number)
	private $characterTable;

	// Printer for output
	private $printer;

	function __construct() {
// 		ini_set('mbstring.substitute_character', "?");
		
		$this -> printer = null;

// 	TODO Not yet used
// 		$this -> auto = true;
// 		$this -> characterTable = Escpos::CHARSET_CP437;
//  		if(self::$available == null) {
//  			self::$available = self::loadAvailableCharacters();
//  		}
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
	
	function getPrinter() {
		return $this -> printer;
	}

	function setPrinter(Escpos $printer = null) {
		$this -> printer = $printer;
	}
	
	// Single-byte, in current encoding. Non-printable characters will be stripped out here.
	function writeTextRaw($text) {
		
		// TODO Call write, passing on printable characters only!
		$this -> write($text);
	}

	private function write($data) {
		if($this -> printer == null) {
			throw new LogicException("Not attached to a printer.");
		}
		$this -> printer -> getPrintConnector() -> write($data);
	}

	// Figure out what encoding some text is
	private function identify($text) {
		throw new Exception("Not implemented");
		// 	TODO Not yet used
		//return Escpos::CHARSET_CP437;
	}
}
