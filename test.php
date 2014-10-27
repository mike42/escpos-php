<?php

require_once(dirname(__FILE__) . "/escpos.php");

$printer = new escpos();

if(false) {
	/* Initialize */
	$printer -> initialize();

	/* Text */
	$printer -> text("Hello world");
	$printer -> cut();

	/* Line feeds */
	$printer -> text("ABC");
	$printer -> feed(7);
	$printer -> text("DEF");
	$printer -> feed_reverse(3);
	$printer -> text("GHI");
	$printer -> feed();
	$printer -> cut();

	/* Font modes */
	$modes = array(
		escpos::MODE_FONT_A,
		escpos::MODE_FONT_B,
		escpos::MODE_EMPHASIZED,
		escpos::MODE_DOUBLE_HEIGHT,
		escpos::MODE_DOUBLE_WIDTH,
		escpos::MODE_UNDERLINE);
	for($i = 0; $i < 2 ** count($modes); $i++) {
		$bits = str_pad(decbin($i), count($modes), "0", STR_PAD_LEFT);
		$mode = 0;
		for($j = 0; $j < strlen($bits); $j++) {
			if(substr($bits, $j, 1) == "1") {
				$mode |= $modes[$j];
			}
		}
		$printer -> select_print_mode($mode);
		$printer -> text("ABCDEFGHIJKLMabcdefghijklmn\n");
	}
	$printer -> select_print_mode(); // Reset
	$printer -> cut();

	/* Underline */
	for($i = 0; $i < 3; $i++) {
		$printer -> set_underline($i);
		$printer -> text("The quick brown fox jumps over the lazy dog\n");
	}
	$printer -> set_underline(0); // Reset
	$printer -> cut();

	/* Cuts */
	for($i = 0; $i < 5; $i++) {
		$printer -> cut(escpos::CUT_PARTIAL);
		$printer -> cut(escpos::CUT_FULL);
	}
	$printer -> cut();

	/* Emphasis */
	for($i = 0; $i < 2; $i++) {
		$printer -> set_emphasis($i == 1);
		$printer -> text("The quick brown fox jumps over the lazy dog\n");
	}
	$printer -> set_emphasis(); // Reset
	$printer -> cut();

	/* Double-strike (looks basically the same as emphasis) */
	for($i = 0; $i < 2; $i++) {
		$printer -> set_double_strike($i == 1);
		$printer -> text("The quick brown fox jumps over the lazy dog\n");
	}
	$printer -> set_double_strike();
	$printer -> cut();

	/* Fonts (many printers do not have a 'Font C') */
	$fonts = array(
		escpos::FONT_A,
		escpos::FONT_B,
		escpos::FONT_C);
	for($i = 0; $i < count($fonts); $i++) {
		$printer -> set_font($fonts[$i]);
		$printer -> text("The quick brown fox jumps over the lazy dog\n");
	}
	$printer -> set_font(); // Reset
	$printer -> cut();

	/* Justification */
	$justification = array(
		escpos::JUSTIFY_LEFT,
		escpos::JUSTIFY_CENTER,
		escpos::JUSTIFY_RIGHT);
	for($i = 0; $i < count($justification); $i++) {
		$printer -> set_justification($justification[$i]);
		$printer -> text("A man a plan a canal panama\n");
	}
	$printer -> set_justification(); // Reset
	$printer -> cut();
	

}


// TODO: barcodes


die();

?>

	/**
	 * Set barcode height
	 * 
	 * @param int $height Height in dots
	 */
	function set_barcode_height($height = 8) {
		fwrite($this -> fp, self::GS . "h" . chr($height));
	}
	
	/**
	 * Print a barcode
	 * 
	 * @param string $content
	 * @param int $type
	 */
	function barcode($content, $type = self::BARCODE_CODE39) {
		fwrite($this -> fp, self::GS . "k" . chr($type) . $content . self::NUL);
	}
