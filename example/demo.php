<?php
/**
 * This is a demo script for the functions of the PHP ESC/POS print driver,
 * Escpos.php.
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
	Escpos::MODE_FONT_B,
	Escpos::MODE_EMPHASIZED,
	Escpos::MODE_DOUBLE_HEIGHT,
	Escpos::MODE_DOUBLE_WIDTH,
	Escpos::MODE_UNDERLINE);
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
	$printer -> cut(Escpos::CUT_PARTIAL);
	$printer -> cut(Escpos::CUT_FULL);
}
$printer -> cut();

/* Emphasis */
for($i = 0; $i < 2; $i++) {
	$printer -> setEmphasis($i == 1);
	$printer -> text("The quick brown fox jumps over the lazy dog\n");
}
$printer -> setEmphasis(false); // Reset
$printer -> cut();

/* Double-strike (looks basically the same as emphasis) */
for($i = 0; $i < 2; $i++) {
	$printer -> setDoubleStrike($i == 1);
	$printer -> text("The quick brown fox jumps over the lazy dog\n");
}
$printer -> setDoubleStrike(false);
$printer -> cut();

/* Fonts (many printers do not have a 'Font C') */
$fonts = array(
	Escpos::FONT_A,
	Escpos::FONT_B,
	Escpos::FONT_C);
for($i = 0; $i < count($fonts); $i++) {
	$printer -> setFont($fonts[$i]);
	$printer -> text("The quick brown fox jumps over the lazy dog\n");
}
$printer -> setFont(); // Reset
$printer -> cut();

/* Justification */
$justification = array(
	Escpos::JUSTIFY_LEFT,
	Escpos::JUSTIFY_CENTER,
	Escpos::JUSTIFY_RIGHT);
for($i = 0; $i < count($justification); $i++) {
	$printer -> setJustification($justification[$i]);
	$printer -> text("A man a plan a canal panama\n");
}
$printer -> setJustification(); // Reset
$printer -> cut();

/* Barcodes */
$barcodes = array(
	Escpos::BARCODE_UPCA,
	Escpos::BARCODE_UPCE,
	Escpos::BARCODE_JAN13,
	Escpos::BARCODE_JAN8,
	Escpos::BARCODE_CODE39,
	Escpos::BARCODE_ITF,
	Escpos::BARCODE_CODABAR);
$printer -> setBarcodeHeight(80);
for($i = 0; $i < count($barcodes); $i++) {
	$printer -> text("Barcode $i " . "\n");
	$printer -> barcode("9876", $barcodes[$i]);
	$printer -> feed();
}
$printer -> cut();

/* Graphics */
try {
	$logo = new EscposImage("images/escpos-php.png");
	$imgModes = array(
		Escpos::IMG_DEFAULT,
		Escpos::IMG_DOUBLE_WIDTH,
		Escpos::IMG_DOUBLE_HEIGHT,
		Escpos::IMG_DOUBLE_WIDTH | 	Escpos::IMG_DOUBLE_HEIGHT
	);
	foreach($imgModes as $mode) {
		$printer -> graphics($logo, $mode);
	}
} catch(Exception $e) {
	/* Images not supported on your PHP, or image file not found */
	$printer -> text($e -> getMessage() . "\n");
}
$printer -> cut();

/* Bit image */
try {
	$logo = new EscposImage("images/escpos-php.png");
	$imgModes = array(
		Escpos::IMG_DEFAULT,
		Escpos::IMG_DOUBLE_WIDTH,
		Escpos::IMG_DOUBLE_HEIGHT,
		Escpos::IMG_DOUBLE_WIDTH | 	Escpos::IMG_DOUBLE_HEIGHT
	);
	foreach($imgModes as $mode) {
		$printer -> bitImage($logo, $mode);
	}
} catch(Exception $e) {
	/* Images not supported on your PHP, or image file not found */
	$printer -> text($e -> getMessage() . "\n");
}
$printer -> cut();

/* Pulse */
$printer -> pulse();

/* Always close the printer! On some PrintConnectors, no actual
 * data is sent until the printer is closed. */
$printer -> close();
?>
