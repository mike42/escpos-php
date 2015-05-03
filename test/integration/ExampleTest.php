<?php
class ExampleTest extends PHPUnit_Framework_TestCase {
	/* Verify that the examples don't fizzle out with fatal errors */
	private $exampleDir;
	
	public function setup() {
		$this -> exampleDir = dirname(__FILE__) . "/../../example/";
	}
	
	public function testBitImage() {
		$this -> requireGraphicsLibrary();
		$outp = $this -> runExample("bit-image.php");
		$this -> outpTest($outp, "bit-image.bin");
	}
	
	public function testCharacterSet() {
		// TODO example not yet ready due to character encoding development work
		$this -> markTestIncomplete("Character set example not yet ready.");
	}
	
	private function outpTest($outp, $fn) {
		$file = dirname(__FILE__) . "/resources/output/".$fn;
		if(!file_exists($file)) {
			file_put_contents($file, $outp);
		}
		$this -> assertEquals($outp, file_get_contents($file));
	}
	
	public function testDemo() {
		$this -> requireGraphicsLibrary();
		$outp = $this -> runExample("demo.php");
		$this -> outpTest($outp, "demo.bin");
	}
	
	public function testGraphics() {
		$this -> requireGraphicsLibrary();
		$outp = $this -> runExample("graphics.php");
		$this -> outpTest($outp, "graphics.bin");
	}
	
	public function testReceiptWithLogo() {
		$this -> requireGraphicsLibrary();
		$outp = $this -> runExample("receipt-with-logo.php");
		$this -> outpTest($outp, "receipt-with-logo.bin");
	}
	
	public function testQrCode() {
		$outp = $this -> runExample("qr-code.php");
		$this -> outpTest($outp, "qr-code.bin");
	}
	
	public function testPrintFromPdf() {
		if(!EscposImage::isImagickLoaded()) {
			$this -> markTestSkipped("imagick plugin required for this test");
		}
		$outp = $this -> runExample("print-from-pdf.php");
		$this -> outpTest(gzcompress($outp, 9), "print-from-pdf.bin.z"); // Compressing output because it's ~1MB
	}
	
	public function testInterfaceEthernet() {
		$outp = $this -> runExample("interface/ethernet.php");
		$this -> outpTest($outp, "interface-ethernet.bin");
	}
	
	public function testInterfaceLinuxUSB() {
		$outp = $this -> runExample("interface/linux-usb.php");
		$this -> outpTest($outp, "interface-linux-usb.bin");
	}
	
	public function testInterfaceWindowsUSB() {
		// Output varies between platforms, not checking.
		$outp = $this -> runExample("interface/windows-usb.php");
	}
	
	public function testInterfaceSMB() {
		// Output varies between platforms, not checking.
		$outp = $this -> runExample("interface/smb.php");
	}
	
	public function testInterfaceWindowsLPT() {
		// Output varies between platforms, not checking.
		$outp = $this -> runExample("interface/windows-lpt.php");
	}
	
	private function runExample($fn) {
		// Change directory and check script
		chdir($this -> exampleDir);
		$this -> assertTrue(file_exists($fn), "Script $fn not found.");
		// Run command and save output
		ob_start();
		passthru("php " . escapeshellarg($fn), $retval);
		$outp = ob_get_contents();
		ob_end_clean();
		// Check return value
		$this -> assertEquals(0, $retval, "Example $fn exited with status $retval");
		return $outp;
	}
	
	protected function requireGraphicsLibrary() {
		if(!EscposImage::isGdLoaded() && !EscposImage::isImagickLoaded()) {
			$this -> markTestSkipped("This test requires a graphics library.");
		}
	}
}
