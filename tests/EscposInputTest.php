<?php
require_once(dirname(__FILE__) . "/../Escpos.php");

class EscposInputTest extends PHPUnit_Framework_TestCase {
	private $printer;

	private $outputFn;
	private $outputFp;

	private $expectedOutputFn;

	protected function setup() {
		$this -> outputFn = null;
		$this -> outputFp = null;
	}

	private function setupOutputTest($name) {
		$this -> outputFn = "test-$name";
		$this -> outputFp = fopen($this -> outputFn, "wb");
		$this -> printer = new Escpos($this -> outputFp);
	}

	private function checkOutputTest($expected = null) {
		fclose($this -> outputFp);
		$outp = file_get_contents($this -> outputFn);
		unlink($this -> outputFn);
		$this -> outputFn = null;
		$this -> outputFp = null;
		if($expected === null) {
			echo "\nOutput was:\n\"" . $this -> friendlyBinary($outp) . "\"\n";
		}
		$this -> assertEquals($outp, $expected);
	}

	private function friendlyBinary($in) {
		$chars = str_split($in);
		foreach($chars as $i => $c) {
			$code = ord($c);
			if($code < 32 || $code > 126) {
				$chars[$i] = "\\x" . bin2hex($c);
			}
		}
		return implode($chars);
	}

    public function testInitialize() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> checkOutputTest("\x1b\x40");
    }

    public function testTextString() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> text("The quick brown fox jumps over the lazy dog\n");
		$this -> checkOutputTest("\x1b@The quick brown fox jumps over the lazy dog\n");
    }

    public function testTextDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> text();
		$this -> checkOutputTest("\x1b@");
    }


/* Functions to test
	function __construct($fp = null) {
	function testText($str = "") {
	function testFeed($lines = 1) {
	function testSelectPrintMode($mode = self::NUL) {
	function testSetUnderline($underline = 1) {
	function testSetEmphasis($on = false) {
	function testSetDoubleStrike($on = false) {
	function setFont($font = self::FONT_A) {
	function setJustification($justification = self::JUSTIFY_LEFT) {
	function feedReverse($lines = 1) {
	function cut($mode = self::CUT_FULL, $lines = 3) {
	function setBarcodeHeight($height = 8) {
	function barcode($content, $type = self::BARCODE_CODE39) {
	function pulse($pin = 0, $on_ms = 120, $off_ms = 240) {
*/
}
?>
