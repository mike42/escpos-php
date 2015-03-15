<?php
/* Change to the correct path if you copy this example! */
require_once(dirname(__FILE__) . "/../../Escpos.php");

/* Most printers are open on port 9100, so you just need to know the IP 
 * address of your receipt printer, and then fsockopen() it on that port.
 */
$fp = fsockopen("10.x.x.x", 9100);

/* Print a "Hello world" receipt" */
$printer = new Escpos($fp);
$printer -> text("Hello World!\n");
$printer -> cut();
fclose($fp);

