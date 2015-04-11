<?php
require_once(dirname(__FILE__) . "/../Escpos.php");
$printer = new Escpos();
compactCharTable($printer);
$printer -> close();

/*
 * Note: The remainder of this  script was created for demonstrating
 * an i18n idea, which is not yet production-ready.
 */
exit(0);
$tables = array(
	Escpos::CP_437,
	Escpos::CP_720,
	Escpos::CP_864,
	Escpos::WCP_1256);
$printer -> selectPrintMode(Escpos::MODE_DOUBLE_WIDTH | Escpos::MODE_DOUBLE_HEIGHT);
foreach($tables as $table) {
	$printer -> selectCharacterTable($table);
	$printer -> text($table . "\n");
	compactCharTable($printer, 8);
}
$printer -> cut();
$printer -> close();

function compactCharTable($printer, $start = 2) {
	/* Output a compact character table */
	$chars = str_repeat(' ', 256);
	for($i = 0; $i < 255; $i++) {
		$chars[$i] = ($i > 32 && $i != 127) ? chr($i) : ' ';
	}

	$printer -> setEmphasis(true);
	$printer -> textRaw("  0123456789ABCDEF\n");
	$printer -> setEmphasis(false);
	for($y = $start; $y < 16; $y++) {
		$printer -> setEmphasis(true);
		$printer -> textRaw(strtoupper(dechex($y)) . " ");
		$printer -> setEmphasis(false);
		$printer -> textRaw(substr($chars,$y * 16, 16) . "\n");
	}
}
