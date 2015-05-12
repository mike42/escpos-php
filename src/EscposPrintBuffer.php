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
 * This class manages newlines and character encoding for the target printer, and
 * can be interchanged for an image-bassed buffer (ImagePrintBuffer) if you can't
 * get it operating properly on your machine.
 */
class EscposPrintBuffer implements PrintBuffer {
	const INPUT_ENCODING = "UTF-8";
	
	const COMPRESS_CACHE = true;
	
	const REPLACEMENT_CHAR = "?";
	
	/**
	 * This array maps Escpos character tables to names iconv encodings
	 */
	// List of available characters
	private $available = null;
	
	// True if we are auto-switching 
	private $auto;

	// Maps of UTF-8 to code-pages
	private $encode = null;
	
	// Printer for output
	private $printer;

	function __construct() {
		$this -> printer = null;
 		$this -> auto = true;
	}

	private function loadAvailableCharacters() {
		$supportedCodePages = $this -> printer -> getPrinterCapabilityProfile() -> getSupportedCodePages();
		$capabilityClassName = get_class($this -> printer -> getPrinterCapabilityProfile());
		$cacheFile = dirname(__FILE__) . "/cache/Characters-" . $capabilityClassName . ".ser" . (self::COMPRESS_CACHE ? ".gz" : "");
		$cacheKey = md5(serialize($supportedCodePages));
		/* Check for pre-generated file */
 		if(file_exists($cacheFile)) {
 			$cacheData = file_get_contents($cacheFile);
 			if(self::COMPRESS_CACHE) {
 				$cacheData = gzdecode($cacheData);
 			}
 			if($cacheData) {
	 			$dataArray = unserialize($cacheData);
	 			if(isset($dataArray["key"]) && isset($dataArray["available"]) && isset($dataArray["encode"]) && $dataArray["key"] == $cacheKey) {
	 				$this -> available = $dataArray["available"];
	 				$this -> encode = $dataArray["encode"];
	 				return;
	 			}
 			}
 		}
		/* Generate conversion tables */
 		$encode = array();
 		$available = array();
 		foreach($supportedCodePages as $num => $characterMap) {
 			$encode[$num] = array();
 			if($characterMap === false) {
 				continue;
 			}
 			for($char = 128; $char <= 255; $char++) {
 				$utf8 = @iconv($characterMap, self::INPUT_ENCODING, chr($char));
 				if($utf8 == '') {
 					continue;
 				}
 				if(iconv(self::INPUT_ENCODING, $characterMap, $utf8) != chr($char)) {
 					// Avoid non-canonical conversions
 					continue;
 				}
 				if(!isset($available[$utf8])) {
 					$available[$utf8] = array();
 				}
 				$available[$utf8][$num] = true;
 				$encode[$num][$utf8] = chr($char);
 			}
 		}
		/* Use generated data */
 		$dataArray = array("available" => $available, "encode" => $encode, "key" => $cacheKey);
 		$this -> available = $dataArray["available"];
 		$this -> encode = $dataArray["encode"];
 		$cacheData = serialize($dataArray);
 		if(self::COMPRESS_CACHE) {
 			$cacheData = gzencode($cacheData);
 		}
 		/* Attempt to cache, but don't worry if we can't */
 		@file_put_contents($cacheFile, $cacheData);
	}

	// Multibyte
	public function writeText($text) {	
		if($this -> printer == null) {
			throw new LogicException("Not attached to a printer.");
		}
		if($text == null) {
			return;
		}
		if(!mb_detect_encoding($text, self::INPUT_ENCODING, true)) {
			// Assume that the user has already put non-UTF8 into the target encoding.
			return $this -> writeTextRaw($text);
		}
		if(!$this -> auto) {
			// If we are not auto-switching characters, then pass it on directly
			$encoding = $this -> characterTable;
			return $this -> writeTextUsingEncoding($text, $encoding);
		}
		$i = 0;
		$j = 0;
		$len = mb_strlen($text, self::INPUT_ENCODING);
		while($i < $len) {
			$matching = true;
			if(($encoding = $this -> identify(mb_substr($text, $i, 1, self::INPUT_ENCODING))) === false) {
				// Un-encodeable text
				$encoding = $this -> getPrinter() -> getCharacterTable();
			}
			$i++;
			$j = 1;
			do {
				$char = mb_substr($text, $i, 1, self::INPUT_ENCODING);
				$matching = !isset($this -> available[$char]) || isset($this -> available[$char][$encoding]);
				if($matching) {
					$i++;
					$j++;
				}
			} while($matching && $i < $len);
			$this -> writeTextUsingEncoding(mb_substr($text, $i - $j, $j, self::INPUT_ENCODING), $encoding);
		}	
	}

	// Multibyte
	private function writeTextUsingEncoding($text, $encodingNo) {
		//echo "\n$encodingNo: $text\n";
		
 		$encodeMap = $this -> encode[$encodingNo];
 		$len = mb_strlen($text, self::INPUT_ENCODING);
 		$rawText = str_repeat(self::REPLACEMENT_CHAR, $len);
 		for($i = 0; $i < $len; $i++) {
 			$char = mb_substr($text, $i, 1, self::INPUT_ENCODING);
 			if(isset($encodeMap[$char])) {
 				$rawText[$i] = $encodeMap[$char];
 			} else if($this -> asciiCheck($char)) {
 				$rawText[$i] = $char;
 			}
 		}
 		if($this -> printer -> getCharacterTable() != $encodingNo) {
 			$this -> printer -> selectCharacterTable($encodingNo);
 		}
		$this -> writeTextRaw($rawText);
	}
	
	private function asciiCheck($char, $extended = false) {
		if(strlen($char) != 1) {
			// Multi-byte string
			return false;
		}
		$num = ord($char);
		if($num > 31 && $num < 127) { // Printable
			return true;
		}
		if($num == 10) { // New-line (printer will take these)
			return true;
		}
		if($extended && $num > 127) {
			return true;
		}
		return false;
	}
	
	public function getPrinter() {
		return $this -> printer;
	}

	public function setPrinter(Escpos $printer = null) {
		$this -> printer = $printer;
		$this -> loadAvailableCharacters();
	}
	
	// Single-byte, in current encoding. Non-printable characters will be stripped out here.
	public function writeTextRaw($text) {
		if($this -> printer == null) {
			throw new LogicException("Not attached to a printer.");
		}
		if(strlen($text) == 0) {
			return;
		}
		// Pass only printable characters
		for($i = 0; $i < strlen($text); $i++) {
			$c = substr($text, $i, 1);
			if(!self::asciiCheck($c, true)) {
				$text[$i] = self::REPLACEMENT_CHAR;
			}
		}
		$this -> write($text);
	}

	private function write($data) {
		$this -> printer -> getPrintConnector() -> write($data);
	}

	// Figure out what encoding some text is
	private function identify($text) {
		// TODO instead, count points for each encoding, choose the one which encodes the farthest into the string.
		$char = mb_substr($text, 0, 1, self::INPUT_ENCODING);
		if(!isset($this -> available[$char])) {
			// Character not available anywhere
			return false;
		}
		foreach($this -> available[$char] as $encodingNo => $true) {
			// Return first code-page where it is available
			return $encodingNo;
		}
		return false;
	}
}
