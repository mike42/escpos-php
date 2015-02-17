<?php
/**
 * This is a demo script for the functions of the PHP ESC/POS print driver,
 * escpos.php.
 *
 * Most printers implement only a subset of the functionality of the driver, so
 * will not render this output correctly in all cases.
 *
 * @author Michael Billington <michael.billington@gmail.com>
 */
require_once(dirname(__FILE__) . "/../Escpos.php");
$printer = new Escpos();

/* Initialize */
$printer -> initialize();

/* Text */
$printer -> text("Hello world\n");
$printer -> cut();

/* Line feeds */
$printer -> text("ABC");
$printer -> feed(7);
$printer -> text("DEF");
$printer -> feedReverse(3);
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
	$printer -> selectPrintMode($mode);
	$printer -> text("ABCDEFGHIJabcdefghijk\n");
}
$printer -> selectPrintMode(); // Reset
$printer -> cut();

/* Underline */
for($i = 0; $i < 3; $i++) {
	$printer -> setUnderline($i);
	$printer -> text("The quick brown fox jumps over the lazy dog\n");
}
$printer -> setUnderline(0); // Reset
$printer -> cut();

/* Cuts */
for($i = 0; $i < 5; $i++) {
	$printer -> cut(escpos::CUT_PARTIAL);
	$printer -> cut(escpos::CUT_FULL);
}
$printer -> cut();

/* Emphasis */
for($i = 0; $i < 2; $i++) {
	$printer -> setEmphasis($i == 1);
	$printer -> text("The quick brown fox jumps over the lazy dog\n");
}
$printer -> setEmphasis(); // Reset
$printer -> cut();

/* Double-strike (looks basically the same as emphasis) */
for($i = 0; $i < 2; $i++) {
	$printer -> setDoubleStrike($i == 1);
	$printer -> text("The quick brown fox jumps over the lazy dog\n");
}
$printer -> setDoubleStrike();
$printer -> cut();

/* Fonts (many printers do not have a 'Font C') */
$fonts = array(
	escpos::FONT_A,
	escpos::FONT_B,
	escpos::FONT_C);
for($i = 0; $i < count($fonts); $i++) {
	$printer -> setFont($fonts[$i]);
	$printer -> text("The quick brown fox jumps over the lazy dog\n");
}
$printer -> setFont(); // Reset
$printer -> cut();

/* Justification */
$justification = array(
	escpos::JUSTIFY_LEFT,
	escpos::JUSTIFY_CENTER,
	escpos::JUSTIFY_RIGHT);
for($i = 0; $i < count($justification); $i++) {
	$printer -> setJustification($justification[$i]);
	$printer -> text("A man a plan a canal panama\n");
}
$printer -> setJustification(); // Reset
$printer -> cut();

/* Barcodes */
$barcodes = array(
	escpos::BARCODE_UPCA,
	escpos::BARCODE_UPCE,
	escpos::BARCODE_JAN13,
	escpos::BARCODE_JAN8,
	escpos::BARCODE_CODE39,
	escpos::BARCODE_ITF,
	escpos::BARCODE_CODABAR);
$printer -> setBarcodeHeight(80);
for($i = 0; $i < count($barcodes); $i++) {
	$printer -> text("Barcode $i " . "\n");
	$printer -> barcode("9876", $barcodes[$i]);
	$printer -> feed();
}
$printer -> cut();
$printer -> pulse();
?>
