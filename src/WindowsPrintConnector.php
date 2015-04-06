<?php
class WindowsPrintConnector implements PrintConnector {
	private $buffer;
	
	private $dest;
	
	public function __construct($dest) {
		// Maybe accept URI in the format:
		// smb://server[:port]/printer
		// smb://workgroup/server[:port]/printer
		// smb://username:password@server[:port]/printer
		// smb://username:password@workgroup/server[:port]/printer
		// TODO check whether you are actually on Windows, only allow full network address if not.
		// TODO match regex, fall back on \\\\%COMPUTERNAME%\\$dest (if Windows).
		// TODO require permission to execute commands.
		
		$this -> dest = $dest;
		$this -> buffer = array();
	}
	
	public function write($data) {
		$this -> buffer[] = $data;
	}
	
	public function finalize() {
		$this -> buffer = null;
		throw new Exception("Windows printing not implemented");
		// TODO save implode($this -> buffer) to temp file
		// TODO send the job to the printer with print /D:$dest $file (Windows), smbspool (Linux).
	}
	
	public function __destruct() {
		if($this -> buffer !== null) {
			trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
		}
	}
}
