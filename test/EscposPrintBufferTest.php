<?php
require_once(dirname(__FILE__) . "/../Escpos.php");
/**
 * Example strings are pangrams using different character sets, and are
 * testing correct code-table switching.
 * 
 * When printed, they should appear the same as in this source file.
 * 
 * Many of these test strings are from:
 * http://www.cl.cam.ac.uk/~mgk25/ucs/examples/quickbrown.txt
 */
class EscposPrintBufferTest extends PHPUnit_Framework_TestCase {
	protected $buffer;

	protected $outputFn;
	protected $outputFp;

	protected function setup() {
		$this -> outputFn = null;
		$this -> outputFp = null;
	}

	protected function setupOutputTest($name) {
		/* Print to a file - for checking output strings */
		$this -> outputFn = "test-$name";
		$this -> outputFp = fopen($this -> outputFn, "wb");
		$printer = new Escpos($this -> outputFp);
		$this -> buffer = new EscposPrintBuffer($printer, $this -> outputFp);
	}

	protected function checkOutput($expected = null) {
		/* Check those output strings */
		fclose($this -> outputFp);
		$outp = file_get_contents($this -> outputFn);
		unlink($this -> outputFn);
		if($expected === null) {
			echo "\n".$this -> outputFn . ": Output was:\n\"" . $this -> friendlyBinary($outp) . "\"\n";
		}
		$this -> outputFn = null;
		$this -> outputFp = null;
		$this -> assertEquals($expected, $outp);
	}

	protected function tearDown() {
		/* Remove test files when a case doesn't finish properly */
		if($this -> outputFn != null) {
			unlink($this -> outputFn);
		}
	}

	private function friendlyBinary($in) {
		/* Print out binary data with PHP \x00 escape codes,
			for builting test cases. */
		$chars = str_split($in);
		foreach($chars as $i => $c) {
			$code = ord($c);
			if($code < 32 || $code > 126) {
				$chars[$i] = "\\x" . bin2hex($c);
			}
		}
		return implode($chars);
	}

	public function testDanish() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> buffer -> writeText("Quizdeltagerne spiste jordbær med fløde, mens cirkusklovnen Wolther spillede på xylofon.");
		$this -> checkOutput();
	}

	public function testArabic() {
		
	}

	public function testGerman() {

	}

	public function testGreek() {

	}

	public function testEnglish() {

	}

	public function testSpanish() {

	}

	public function testFrench() {

	}

	public function testIrishGaelic() {

	}

	public function testIcelandic() {

	}

	public function testJapaneseHiragana() {

	}

	public function testJapaneseKatakana() {

	}

	public function testHebrew() {

	}

	public function testPolish() {

	}

	public function testRussian() {

	}

	public function testThai() {

	}

	public function testTurkish() {

	}
}

