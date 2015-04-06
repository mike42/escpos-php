<?php
/* Change to the correct path if you copy this example! */
require_once(dirname(__FILE__) . "/../../Escpos.php");

/**
 * TODO: A brief explanation of how to set up a USB receipt printer
 * so that ESCPOS/PHP can see it.
 */
try {
	// Enter the share name for your USB printer here
	$connector = new WindowsPrintConnector("Epson");

	/* Print a "Hello world" receipt" */
	$printer = new Escpos($connector);
	$printer -> text("Hello World!\n");
	$printer -> cut();
	
	/* Close printer */
	$printer -> close();
} catch(Exception $e) {
	echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
}

