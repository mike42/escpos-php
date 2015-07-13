<?php 
require_once(dirname(__FILE__) . "/../../Escpos.php");
$profile = DefaultCapabilityProfile::getInstance();
// This is a quick demo of currency symbol issues in #39.

/* Option 1: Native ESC/POS characters, depends on printer and is buggy. */
$connector = new FilePrintConnector("php://stdout");
$printer = new Escpos($connector, $profile);
$printer -> text("€ 9,95\n");
$printer -> text("£ 9.95\n");
$printer -> text("$ 9.95\n");
$printer -> text("¥ 9.95\n");
$printer -> text("€ 9.95\n");
$printer -> cut();
$printer -> close();

/* Option 2: Image-based output (formatting not available using this output). */
$buffer = new ImagePrintBuffer();
$connector = new FilePrintConnector("php://stdout");
$printer = new Escpos($connector, $profile);
$printer -> setPrintBuffer($buffer);
$printer -> text("€ 9,95\n");
$printer -> text("£ 9.95\n");
$printer -> text("$ 9.95\n");
$printer -> text("¥ 9.95\n");
$printer -> text("€ 9.95\n");
$printer -> cut();
$printer -> close();
