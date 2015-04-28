<?php
require_once(dirname(__FILE__) . "/../src/RTLBuffer.php");
// Tests over the currently-suggested work-around for RTL text.
class RTLBufferTest extends PHPUnit_Framework_TestCase {
	protected $printer;
	protected $outputConnector;
	protected $epsonEncodings;
	protected $rtlEncodings;
	
	protected function setup() {
		/* Print to nowhere- for testing which inputs are accepted */
		$this -> outputConnector = new DummyPrintConnector();
		$this -> printer = new Escpos($this -> outputConnector);
		$this -> epsonEncodings = array(
			'CP437' => 0,
			'CP1256' => 50,
			'CP864' => 37);
		$this -> rtlEncodings = array('CP1256');
	}
	
	protected function checkOutput($expected = null) {
		/* Check those output strings */
		$outp = $this -> outputConnector -> getData();
		if($expected === null) {
			echo "\nOutput was:\n\"" . $this -> friendlyBinary($outp) . "\"\n";
		}
		$this -> assertEquals($expected, $outp);
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
	
	protected function tearDown() {
		$this -> outputConnector -> finalize();
	}
	
	function testRTL_UTF8() {
		mb_internal_encoding("utf-8");
		$buf = new RTLBuffer($this -> printer, $this -> epsonEncodings, $this -> rtlEncodings);
		$buf -> output("ص.ب. ١١٠٦ ر.ب. ٣١١ صحار، سلطنة عمان");
		$this -> checkOutput("\x1b@\x1bt2\xe4\xc7\xe3\xda \xc9\xe4\xd8\xe1\xd3 \xa1\xd1\xc7\xcd\xd5 \x1bt%\xb3\xb1\xb1 \x1bt2.\xc8.\xd1 \x1bt%\xb1\xb1\xb0\xb6 \x1bt2.\xc8.\xd5\x0a");
	}
	
	function testRTL_NotUTF8() {
		// Internal encoding should not affect this. Function should take UTF8
		mb_internal_encoding("iso-8859-1");
		$buf = new RTLBuffer($this -> printer, $this -> epsonEncodings, $this -> rtlEncodings);
		$buf -> output("ص.ب. ١١٠٦ ر.ب. ٣١١ صحار، سلطنة عمان");
		$this -> checkOutput("\x1b@\x1bt2\xe4\xc7\xe3\xda \xc9\xe4\xd8\xe1\xd3 \xa1\xd1\xc7\xcd\xd5 \x1bt%\xb3\xb1\xb1 \x1bt2.\xc8.\xd1 \x1bt%\xb1\xb1\xb0\xb6 \x1bt2.\xc8.\xd5\x0a");
	}
}