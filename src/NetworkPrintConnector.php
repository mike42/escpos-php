<?php
class NetworkPrintConnector extends FilePrintConnector {
	public function __construct($ip, $port = "9100") {
		$this -> fp = @fsockopen($ip, $port, $errno, $errstr);
		if($this -> fp === false) {
			throw new Exception("Cannot initialise NetworkPrintConnector: " . $errstr);
		}
	}
}
