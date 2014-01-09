<?php
require_once(dirname(__FILE__) . "/escpos.php");

$printer = new escpos();


$printer -> cut();