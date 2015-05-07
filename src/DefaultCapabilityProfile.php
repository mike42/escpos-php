<?php
/**
 * This capability profile matches many recent Epson-branded thermal receipt printers.
 * 
 * For non-Epson printers, try the SimpleCapabilityProfile.
 */
class DefaultCapabilityProfile extends AbstractCapabilityProfile {
	/**
	 * Return a map of code page numbers to names for this printer. Names
	 * should match iconv code page names where possible (non-matching names will not be used).
	 */
	function getSupportedCodePages() {
		return array('0' => 'CP437');
	}

	/**
	 * True for bitImage support, false for no bitImage support.
	 */
	function getSupportsBitImage() {
		return true;
	}

	/**
	 * True for graphics support, false for no graphics support.
	 */
	function getSupportsGraphics() {
		return true;
	}

	/**
	 * True if the printer renders its own QR codes, false to send an image.
	 */
	function getSupportsQrCode() {
		return true;
	}
}