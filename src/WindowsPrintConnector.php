<?php
class WindowsPrintConnector implements PrintConnector {
	private $buffer;
	
	private $dest;
	
	public function __construct($dest) {
		$this -> dest = $dest;
		$this -> buffer = array();
	}
	
	public function write($data) {
		$this -> buffer[] = $data;
	}
	
	public function finalize() {
		throw new Exception("Not implemented");
		//implode($this -> buffer);
		
		//$this -> buffer = null;
	}
	
	public function __destruct() {
		if($this -> buffer !== null) {
			trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
		}
	}
}