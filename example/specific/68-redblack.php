<?php
/*
 * Example of two-color printing, tested on an epson TM-U220 with two-color ribbon installed.
 */
require_once (dirname ( __FILE__ ) . "/../../Escpos.php");

$connector = new FilePrintConnector("/dev/usb/lp0");
try {
    $printer = new Escpos($connector);
    $printer -> text("Hello World!\n");
    $printer -> setColor(Escpos::COLOR_2);
    $printer -> text("Red?!\n");
    $printer -> setColor(Escpos::COLOR_1);
    $printer -> text("Default color again?!\n");
    $printer -> cut();
} finally {
    /* Always close the printer! */
    $printer -> close();
}
