<?php
/**
 * This capability profile matches many recent Epson-branded thermal receipt printers.
 * 
 * For non-Epson printers, try the SimpleCapabilityProfile.
 */
class DefaultCapabilityProfile implements EscposCapabilityProfile {
	function getSupportedCodePages() {
		return array('0' => 'CP437');
	}

	function getSupportsBitImage() {
		return true;
	}

	function getSupportsGraphics() {
		return true;
	}

	function getSupportsQrCode() {
		return true;
	}
}