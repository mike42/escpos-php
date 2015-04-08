<?php
/**
 * escpos-php, a Thermal receipt printer library, for use with
 * ESC/POS compatible printers.
 *
 * Copyright (c) 2014-2015 Michael Billington <michael.billington@gmail.com>,
 * 	incorporating modifications by:
 *  - Roni Saha <roni.cse@gmail.com>
 *  - Gergely Radics <gerifield@ustream.tv>
 *  - Warren Doyle <w.doyle@fuelled.co>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 * Connector for sending print jobs to
 * - local ports on windows (COM1, LPT1, etc)
 * - shared (SMB) printers from any platform (\\server\foo)
 * For USB printers or other ports, the trick is to share the printer with a generic text driver, then access it locally.
 */
class WindowsPrintConnector implements PrintConnector {
	/**
	 * @var array Accumulated lines of output for later use.
	 */
	private $buffer;

	/**
	 * @var string The hostname of the target machine, or null if this is a local connection.
	 */
	private $hostname;

	/**
	 * @var boolean True if a port is being used directly (must be Windows), false if network shares will be used.
	 */
	private $isLocal;

	/**
	 * @var boolean True if this script is running on Windows, false otherwise.
	 */
	private $isWindows;

	/**
	 * @var string The name of the target printer (eg "Foo Printer") or port ("COM1", "LPT1").
	 */
	private $printerName;

	/**
	 * @var string Valid local ports.
	 */
	const REGEX_LOCAL = "/^(LPT\d|COM\d)$/";

	/**
	 * @var string Valid printer name.
	 */
	const REGEX_PRINTERNAME = "/^(\w+)(\s\w*)*$/";

	/**
	 * @var string Valid smb:// URI containing hostname & printer name only.
	 */
	const REGEX_SMB = "/^smb:\/\/(\w*)/";

	/**
	 * @param string $dest
	 * @throws BadMethodCallException
	 */
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

	public function __destruct() {
		if($this -> buffer !== null) {
			trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
		}
	}

	public function finalize() {
		$data = implode($this -> buffer);
		$this -> buffer = null;
		if($this -> isWindows) {
			/* Build windows-friendly print command */
			if(!$this -> isLocal) {
				$device = "\\\\" . $this -> hostname . "\\" . $this -> printerName;
			} else {
				$device = $this -> printerName;
			}
			$filename = tempnam(sys_get_temp_dir(), "escpos");
			$cmd = sprintf("print /D:%s %s",
				escapeshellarg($device),
				escapeshellarg($filename));
			
			/* Write print job and run command */
			file_put_contents($filename, $data);
 			ob_start();
 			passthru($cmd, $retval);
 			$outp = ob_get_contents();
 			ob_end_clean();
			if($retval != 0 || strpos($outp, $device) !== false) {
				trigger_error("Failed to print. Command '$cmd' returned $retval: $outp", E_USER_NOTICE);
			}
			unlink($filename);
		} else {
			/* Build linux-friendly print command */
			// smbspool (Linux).
			throw new Exception("Linux printing over Samba not yet implemented");
			$cmd = "";
		}
	}

	public function write($data) {
		$this -> buffer[] = $data;
	}
}
