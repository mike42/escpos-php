<?php
require_once(dirname(__FILE__) . "/../Escpos.php");
$printer = new Escpos();

/* Output a compact character table */
$chars = str_repeat(' ', 256);
for($i = 0; $i < 255; $i++) {
	$chars[$i] = ($i > 32 && $i != 127) ? chr($i) : ' ';
}
$printer -> selectPrintMode(Escpos::MODE_DOUBLE_WIDTH | Escpos::MODE_DOUBLE_HEIGHT);
$printer -> setEmphasis(true);
$printer -> text("  0123456789ABCDEF\n");
$printer -> setEmphasis(false);
for($y = 2; $y < 16; $y++) {
	$printer -> setEmphasis(true);
	$printer -> text(strtoupper(dechex($y)) . " ");
	$printer -> setEmphasis(false);
	$printer -> text(substr($chars,$y * 16, 16) . "\n");
}

$printer -> feed();
$printer -> cut();

