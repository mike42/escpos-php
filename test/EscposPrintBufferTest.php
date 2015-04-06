<?php
require_once(dirname(__FILE__) . "/../Escpos.php");
require_once(dirname(__FILE__) . "/../src/DummyPrintConnector.php");

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
	protected $outputConnector;
	
	protected function setup() {
		$this -> outputConnector = new DummyPrintConnector();
		$printer = new Escpos($this -> outputConnector);
		$this -> buffer = new EscposPrintBuffer($printer, $this -> outputConnector);
	}
	
	protected function checkOutput($expected = null) {
		/* Check those output strings */
		$outp = $this -> outputConnector -> getData();
		if($expected === null) {
			echo "\nOutput was:\n\"" . $this -> friendlyBinary($outp) . "\"\n";
		}
		$this -> assertEquals($expected, $outp);
	}

	protected function tearDown() {
		$this -> outputConnector -> finalize();
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
		$this -> markTestSkipped();
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

