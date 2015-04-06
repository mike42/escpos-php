<?php
class FilePrintConnector implements PrintConnector {
	protected $fp;
	
	/**
	 * Construct new connector, given a filename
	 * 
	 * @param string $filename
	 */
	public function __construct($filename) {
		$this -> fp = fopen($filename, "wb+");
		if($this -> fp === false) {
			throw new Exception("Cannot initialise FilePrintConnector.");
		}
	}
	
	/**
	 * Write data to the file
	 * 
	 * @param string $data
	 */
	public function write($data) {
		fwrite($this -> fp, $data);
	}
	
	/**
	 * Close file pointer
	 */
	public function finalize() {
		fclose($this -> fp);
		$this -> fp = false;
	}
	
	public function __destruct() {
		if($this -> fp !== false) {
			trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
		}
	}
}
