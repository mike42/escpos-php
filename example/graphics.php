<?php
/* Print-outs using the newer graphics print command */

require_once(dirname(__FILE__) . "/../Escpos.php");
$printer = new Escpos();
$tux = new EscposImage("images/tux.png");

$printer -> graphics($tux);
$printer -> text("Regular Tux.\n");
$printer -> feed();

$printer -> graphics($tux, Escpos::IMG_DOUBLE_WIDTH);
$printer -> text("Wide Tux.\n");
$printer -> feed();

$printer -> graphics($tux, Escpos::IMG_DOUBLE_HEIGHT);
$printer -> text("Tall Tux.\n");
$printer -> feed();

$printer -> graphics($tux, Escpos::IMG_DOUBLE_WIDTH | Escpos::IMG_DOUBLE_HEIGHT);
$printer -> text("Large Tux in correct proportion.\n");

$printer -> cut();

$printer -> close();
?>
