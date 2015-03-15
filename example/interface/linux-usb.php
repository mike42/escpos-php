<?php
/* Change to the correct path if you copy this example! */
require_once(dirname(__FILE__) . "/../../Escpos.php");

/* On Linux, use the usblp module to make your printer available as a device
 * file. This is generally the default behaviour if you don't install any
 * vendor drivers.
 *
 * Troubleshooting: On Debian, you must be in the lp group to access this file.
 * dmesg to see what happens when you plug in your printer to make sure no
 * other drivers are unloading the module.
 */
//$fp = fopen("/dev/usb/lp0", "w+");
$fp = fopen("/dev/usb/lp1", "w+");
//$fp = fopen("/dev/usb/lp2", "w+");

/* Print a "Hello world" receipt" */
$printer = new Escpos($fp);
$printer -> text("Hello World!\n");
$printer -> cut();
fclose($fp);

