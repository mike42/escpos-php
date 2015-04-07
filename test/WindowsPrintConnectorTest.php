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
		$this -> windowsOnly();
		$connector = new WindowsPrintConnector("FooPrinter");
		$connector -> finalize();
	}

	public function testConstructSamba() {
		$this -> windowsOnly();
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

	/**
	 * Unimplemented methods for linux
	 */
	private function windowsOnly() {
		if(PHP_OS != "WINNT") {
			$this -> setExpectedException('Exception');
		}
	}
}

