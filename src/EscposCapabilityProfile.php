<?php
/**
 * Not all Escpos printers are the same. Supported functionality and model-specific data is specified here, so that the printer knows which commands to send.
 * 
 * Epson printers are 
 * 
 * Profile for a model or family of printer.
 */
interface EscposCapabilityProfile {
	// TODO font count
	
	/**
	 * Return a map of code page numbers to names for this printer. Names
	 * should match iconv code page names where possible (non-matching names will not be used).
	 */
	function getSupportedCodePages();

	/**
	 * True for bitImage support, false for no bitImage support.
	 */
	function getSupportsBitImage();

	/**
	 * True for graphics support, false for no graphics support.
	 */
	function getSupportsGraphics();

	/**
	 * True if the printer renders its own QR codes, false to send an image.
	 */
	function getSupportsQrCode();
}