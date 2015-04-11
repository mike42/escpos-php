<?php
class WindowsPrintConnector implements PrintConnector {
	private $buffer;
	
	private $printerName;

	private $hostname;

	private $isLocal;

	private $isWindows;
	
	const REGEX_LOCAL = "/^(LPT\d|COM\d)$/";
	
	const REGEX_PRINTERNAME = "/^(\w+)(\s\w*)*$/";

	const REGEX_SMB = "/^smb:\/\/(\w*)/";
	
	public function __construct($dest) {
		$this -> isWindows = (PHP_OS == "WINNT");
		$this -> isLocal = false;
		$this -> buffer = null;
		if(preg_match(self::REGEX_LOCAL, $dest)) {
			// Straight to LPT1, COM1 or other local port. Allowed only if we are actually on windows.
			if(!$this -> isWindows) {
				throw new BadMethodCallException("WindowsPrintConnector can only be used to print to a local printer ('".$dest."') on a Windows computer.");
			}
			$this -> isLocal = true;
			$this -> hostname = null;
			$this -> printerName = $dest;
		} else if(preg_match(self::REGEX_SMB, $dest)) {
			// Connect to samba share. smb://host/printer
			$part = parse_url($dest);
			$this -> hostname = $part['host'];
			$this -> printerName = ltrim($part['path'], '/');
		} else if(preg_match(self::REGEX_PRINTERNAME, $dest)) {
			// Just got a printer name. Assume it's on the current computer.
			$hostname = gethostname();
			if(!$hostname) {
				$hostname = "localhost";
			}
			$this -> hostname = $hostname;
			$this -> printerName = $dest;
		} else {
			throw new BadMethodCallException("Printer '" . $dest . "' is not valid. Use local port (LPT1, COM1, etc) or smb://computer/printer notation.");
		}
		$this -> buffer = array();
	}
	
	public function write($data) {
		$this -> buffer[] = $data;
	}
	
	public function finalize() {
		$data = implode($this -> buffer);
		$this -> buffer = null;
		if($this -> isWindows) {
			/* Windows-friendly printing of all sorts */
			if(!$this -> isLocal) {
				/* Networked printing */
				$filename = tempnam(sys_get_temp_dir(), "escpos");
				file_put_contents($filename, $data);
				$device = "\\\\" . $this -> hostname . "\\" . $this -> printerName;
				copy($filename, $device);
				unlink($filename);
			} else {
				/* Drop data straight on the printer */
				file_put_contents($filename,  $this -> printerName);
			}
		} else {
			throw new Exception("Linux printing over Samba not implemented");
			// smbspool (Linux).
			$cmd = "";
		}
	}
	
	public function __destruct() {
		if($this -> buffer !== null) {
			trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
		}
	}
}
