<?php
require_once(dirname(__FILE__) . "/../Escpos.php");
require_once(dirname(__FILE__) . "/../src/RTLBuffer.php");

// Instantiate printer as normal
$printer = new Escpos();

// Different character tables for each printer
$epsonEncodings = array( // Epson character tables used for testing
		'CP437' => 0,
		'CP1256' => 50,
		'CP864' => 37);
// $eposEncodings = array( // E-pos TEP 200M character tables
// 		'CP437' => 0,
// 		'CP1256' => 34,
// 		'CP864' => 63);
$buf = new RTLBuffer($printer, $epsonEncodings, array('CP1256'));

// Call one output() statement per line.
$buf -> output("ص.ب. ١١٠٦ ر.ب. ٣١١ صحار، سلطنة عمان");
$printer -> feed(2);
$printer -> cut();
$printer -> close();
