<?php
final class DummyPrintConnector implements PrintConnector {
	private $buffer;
	
	public function __construct() {
		$this -> buffer = array();
	}
	
	public function write($data) {
		$this -> buffer[] = $data;
	}
	
	public function getData() {
		return implode($this -> buffer);
	}
	
	public function finalize() {
		$this -> buffer = null;
	}
	
	public function __destruct() {
		if($this -> buffer !== null) {
			trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
		}
	}
}
