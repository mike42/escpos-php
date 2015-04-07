<?php
class WindowsPrintConnectorTest extends PHPUnit_Framework_TestCase {
	public function testConstructLpt() {
		$this -> windowsLocalPrinterTest();
		$connector = new WindowsPrintConnector("LPT1");
		$connector -> finalize();
	}

	public function testConstructCom() {
		$this -> windowsLocalPrinterTest();
		$connector = new WindowsPrintConnector("COM1");
		$connector -> finalize();
	}

	public function testConstructLocalShare() {
		$connector = new WindowsPrintConnector("FooPrinter");
		$connector -> finalize();
	}

	public function testConstructSamba() {
		$connector = new WindowsPrintConnector("smb://foo-computer/FooPrinter");
		$connector -> finalize();
	}

	/**
	 * Tests which are about to use a WindowsPrintConnector to open a local printer.
	 */
	private function windowsLocalPrinterTest() {
		if(PHP_OS != "WINNT") {
			$this -> setExpectedException('BadMethodCallException');
		}
	}
}

