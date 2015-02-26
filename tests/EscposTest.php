<?php
require_once(dirname(__FILE__) . "/../Escpos.php");

class EscposTest extends PHPUnit_Framework_TestCase {
	private $printer;

	private $outputFn;
	private $outputFp;

	private $expectedOutputFn;

	protected function setup() {
		$this -> outputFn = null;
		$this -> outputFp = null;
	}

	protected function setupTest() {
		$this -> printer = new Escpos(fopen("/dev/null", "wb"));
	}

	private function setupOutputTest($name) {
		$this -> outputFn = "test-$name";
		$this -> outputFp = fopen($this -> outputFn, "wb");
		$this -> printer = new Escpos($this -> outputFp);
	}

	private function checkOutput($expected = null) {
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

    public function testInitializeOutput() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> checkOutput("\x1b\x40");
    }

    public function testTextStringOutput() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> text("The quick brown fox jumps over the lazy dog\n");
		$this -> checkOutput("\x1b@The quick brown fox jumps over the lazy dog\n");
    }

    public function testTextString() {
		$this -> setupTest();
		$this -> printer -> text("String");
		$this -> printer -> text(123);
		$this -> printer -> text();
		$this -> printer -> text(null);
		$this -> printer -> text(1.2);
    }

    public function testTextObject() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> text(new DateTime());
	}

    public function testTextObjectToString() {
		$this -> setupTest();
		$this -> printer -> text(new FooBar("FooBar"));
	}


    public function testTextDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> text();
		$this -> checkOutput("\x1b@");
    }

    public function testFeedDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> feed();
		$this -> checkOutput("\x1b@\x0a");
    }

    public function testFeed3Lines() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> feed(3);
		$this -> checkOutput("\x1b@\x1bd\x03");
    }

    public function testSelectPrintModeDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> selectPrintMode();
		$this -> checkOutput("\x1b@\x1b!\x00");
    }

    public function testSetUnderlineDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setUnderline();
		$this -> checkOutput("\x1b@\x1b-\x01");
    }

    public function testSetUnderlineOff() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setUnderline(0);
		$this -> checkOutput("\x1b@\x1b-\x00");
    }

    public function testSetUnderlineDbl() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setUnderline(2);
		$this -> checkOutput("\x1b@\x1b-\x02");
    }


/* Functions to test

	function testSelectPrintMode($mode = self::NUL) {
	function testSetUnderline($underline = 1) {
	function testSetEmphasis($on = false) {
	function testSetDoubleStrike($on = false) {
	function testFont($font = self::FONT_A) {
	function testJustification($justification = self::JUSTIFY_LEFT) {
	function testFeedReverse($lines = 1) {
	function testCut($mode = self::CUT_FULL, $lines = 3) {
	function testSetBarcodeHeight($height = 8) {
	function testBarcode($content, $type = self::BARCODE_CODE39) {
	function testPulse($pin = 0, $on_ms = 120, $off_ms = 240) {
*/
}

/*
 * For testing that string-castable objects are handled
 */
class FooBar {
	private $foo;
	public function __construct($foo) {
		$this -> foo = $foo;
	}
	
	public function __toString() {
		return $this -> foo;
	}
}
?>
