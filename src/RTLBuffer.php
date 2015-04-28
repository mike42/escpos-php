<?php
/**
 * Simple mixed right-to-left, left-to-right text layout & character encoding handler to run on top
 * of escpos-php.
 * 
 * This works by reading the text in-order, separating it into chunks by character encoding,
 * and then playing it back in reverse, also reversing right-to-left chunks, for convincing-looking
 * layout of simple texts. This is NOT a compliant unicode layout engine (patches welcome).
 * 
 * NOTE: This is more of a work-around than a feature. This functionality will be merged into EscposPrintBuffer
 * once a compliant layout engine is available.
 */
//Uncomment below line for extra output
//define("RTL_DEBUG", true);

class RTLBuffer {
	/**
	 * @var array List of RTL encodings
	 */
	private $rtlList;

	/**
	 * @var array List of available encodings, mapping them to printer-specific code pages ('CP437' => 0)
	 */
	private $encodings;

	/**
	 * @var array List of available characters, and which table they are found in ('a' => 'CP437');
	 */
	private $available;

	/**
	 * @var array Maps of UTF-8 to local encodings. 'CP437' => array('a' => 'a'), etc.
	 */
	private $maps;

	/**
	 * @var EscposPrinter The printer to send commands to.
	 */
	private $printer;

	/**
	 * @var string The most-recently switched-to encoding.
	 */
	private $curEncoding;
	
	/**
	 * @param Escpos $printer Printer to send output to
	 * @param array $encodings Priority-order map of encodings names to printer encoding numbers.
	 * @param array $rtlList List of encodings which contain RTL characters.
	 * @throws InvalidArgumentException Where no encodings are given.
	 */
	public function __construct(Escpos $printer, array $encodings, array $rtlList = array()) {
		if(count($encodings) == 0) {
			throw new InvalidArgumentException("RTLBuffer: No encodings given");
		}
		$this -> printer = $printer;
		$this -> encodings = $encodings;
		$this -> available = array();
		$this -> maps = array();
		$this -> rtlList = $rtlList;
		$this -> curEncoding = null;
		foreach($this -> encodings as $encoding => $num) {
			if($this -> curEncoding == null) {
				$this -> curEncoding = $encoding; // Assume the first encoding is the default.
			}
			if(isset($this -> maps[$encoding])) {
				continue;
			}
			$this -> maps[$encoding] = $this -> makeMap($this -> available, $encoding);		
		}
	}

	/**
	 * Print a line of RTL text.
	 * 
	 * @param string $str Text to output. A line-break is automatically added at the end, as a full line is required.
	 */
	public function output($str) {
		$curChunkStr = "";
		$curEncoding = "";
		$chunks = array();
		// Read string order and split into directional and encoding chunks
		$len = mb_strlen($str);

		for($i = 0; $i < $len; $i++) {
			$c = mb_substr($str, $i, 1);
			if(defined("RTL_DEBUG")) {
				echo "- '$c'\n";
			}
			if(!isset($this -> available[$c])) {
				// Skip unavailable characters
				continue;
			}
			if(in_array($c, array(".",",","?","!","'"))) { // Include trailing punctuation
				$newEncoding = $curEncoding;
			} else {
				$newEncoding = $this -> available[$c];
			}
			if($newEncoding != $curEncoding) {
				if($curEncoding != "") {
					$chunks[] = new RTLChunk($curChunkStr, $curEncoding, in_array($curEncoding, $this -> rtlList));
				}
				$curEncoding = $newEncoding;
				$curChunkStr = "";
			}
			$curChunkStr .= $c;
		}
		$chunks[] = new RTLChunk($curChunkStr, $curEncoding, in_array($curEncoding, $this -> rtlList));
		if(defined("RTL_DEBUG")) {
			print_r($chunks);
		}
		// Print chunks in reverse order (left-most first).
		for($i = count($chunks) - 1; $i >= 0; $i--) {
			$chunk = $chunks[$i];
			$encoding = $chunk -> getEncoding();
			if($chunk -> canEncodeAs($this -> maps[$this -> curEncoding])) {
				// Change code-page only where necessary
				$encoding = $this -> curEncoding;
			} else {
				$this -> setPrinterEncoding($encoding);
			}
			
			$map = $this -> maps[$encoding];
			if(defined("RTL_DEBUG")) {
				echo "\n>> " . $chunk -> getText() . "\n";
				$this -> printer -> textRaw("'" . $chunk -> encode($map) . "'\n");
			} else {
				$this -> printer -> textRaw($chunk -> encode($map));
			}
		}
		$this -> printer -> textRaw("\n");
	}

	/**
	 * Create & return a map of UTF-8 characters into this encoding
	 * 
	 * @param string $available Map of available chars to encodings, modified when this is called.
	 * @param string $targetEncoding Encoding to use, eg 'CP437'
	 * @return multitype:string  A map of UTF-8 chars to local chars.
	 */
	private function makeMap(&$available, $targetEncoding) {
		// Make map of target encoding v UTF-8
		$map = array();
		for($i = 0; $i < 255; $i++) {
			$native = chr($i);
			$utf8 = @iconv($targetEncoding, 'UTF-8', $native);
			if($utf8 == '') {
				continue;
			}
			$map[$utf8] = $native;
			if(!isset($available[$utf8])) {
				$available[$utf8] = $targetEncoding;
			}
		}
		return $map;
	}

	/**
	 * @param string $encoding Encoding to use, eg 'CP437'.
	 */
	private function setPrinterEncoding($encoding) {
		$encodingNum = $this -> encodings[$encoding];
		$this -> printer -> selectCharacterTable($encodingNum);
		$this -> curEncoding = $encoding;
	}
}

/**
 * Print a chunk of text, may or may not be reversed.
 */
class RTLChunk {
	private $text;
	private $isRtl;
	private $encoding;

	public function __construct($text, $encoding, $isRtl) {
		$this -> text = $text;
		$this -> encoding = $encoding;
		$this -> isRtl = $isRtl;
	}

	public function canEncodeAs(array $map) {
		$str = $this -> text;
		$len = mb_strlen($str);
		for($i = 0; $i < $len; $i++) {
			$utf8 = mb_substr($str, $i, 1);
			if(!isset($map[$utf8])) {
				return false;
			}
		}
		return true;
	}
	
	public function encode(array $map) {
		// Convert UTF8 to the target encoding
		$str = $this -> text;
		$len = mb_strlen($str);
		$outp = str_repeat("?", $len);
		for($i = 0; $i < $len; $i++) {
			$utf8 = mb_substr($str, $i, 1);
			if(isset($map[$utf8])) {
				$outp[$i] = $map[$utf8];
			}
		}
		if($this -> isRtl || strlen($outp) <= 1) {
			return strrev($outp);
		} else {
			return $outp;
		}
	}

	public function getEncoding() {
		return $this -> encoding;
	}

	public function getText() {
		return $this -> text;
	}
}
