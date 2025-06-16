# escpos-php

PHP library for printing to ESC/POS-compatible thermal and impact printers.

Installation

Install via Composer:
composer require mike42/escpos-php

Make sure your printer is supported and that your system meets the necessary dependencies.

Usage

To use this library, install it with Composer and include the autoloader.

require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

$connector = new FilePrintConnector("/dev/usb/lp0");
$printer = new Printer($connector);

$printer->text("Hello World!\n");
$printer->cut();
$printer->close();

Features

- Print text, barcodes, and QR codes.
- Feed control and cut commands.
- Character code tables.
- Graphics and images support.
- Network, file, or USB printing.

Compatibility

This library supports many ESC/POS printers from brands like Epson, Bixolon, and Star. Not all printers support all commands — consult your printer’s manual.

Contributing

Pull requests are welcome! Please fork the repository and submit your PR against the main branch.

Before contributing, check the issues page for open feature requests or bugs.

License

MIT License. See LICENSE for details.

