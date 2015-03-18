<?php
class EscposPrintBuffer {
	private $autoTable;

	private $characterTable;

	private $empty;

	private $fp;
	
	private $printer;

	// Local availability table up here
	// Local mapping of character tables to iconv character sets also up here

	function __construct(Escpos $printer, $fp) {
		$this -> fp = $fp;
		
		// Load up or generate the latest listing
	}

	function getCharacterTable() {
		return $this -> characterTable;
	}

	function writeText($text) {
		// identify, write reapeat (if auto)
		// Otherwise just write, fill with ?
	}

	private function writeTextUsingEncoding($text, $encoding) {
		// If encoding is not current encoding.. send switch code.
		
		
		// Call writeTextRaw on the result of iconv'ing as many characters as we can represent.
		
		// Return any remaining characters.
	}

	function writeTextRaw($text) {
		
		// Call write, passing on printable characters only!
		
	}

	function write($autoNewline = false, $createNewLine = false) {
		
	}

	private function identify($text) {
		
	}
}
