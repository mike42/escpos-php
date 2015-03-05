ESC/POS print driver for PHP
============================
This project implements a subset of Epson's ESC/POS protocol for thermal receipt printers. It allows you to print receipts with basic formatting, cutting, and barcode printing on a compatible printer.

It is intended for use with networked printers, but you can also use the library to save print jobs to a file.

Basic usage
-----------
The library should be initialised with a file pointer to the printer. For a networked printer, this can be opened via [fsockopen()](http://www.php.net/manual/en/function.fsockopen.php). For a local printer, use [fopen()](http://www.php.net/manual/en/function.fopen.php) to open `/dev/lp0` (or a serial interface, etc). If no file pointer is specified, then standard output is used.

A "hello world" receipt can be printed easily (Call this `hello-world.php`):
```php
<?php
require_once(dirname(__FILE__) . "/Escpos.php");
$printer = new Escpos();
$printer -> text("Hello World!\n");
$printer -> cut();
```
This would be executed as:
```
php hello-world.php | nc 10.x.x.x. 9100
```
The same output can be passed directly to a socket:
```php
<?php
require_once(dirname(__FILE__) . "/Escpos.php");
$fp = fsockopen("10.x.x.x", 9100);
$printer = new Escpos($fp);
$printer -> text("Hello World!\n");
$printer -> cut();
fclose($fp);
```

A complete receipt can be found in the code of [Auth](https://github.com/mike42/Auth) in [ReceiptPrinter.php](https://github.com/mike42/Auth/blob/master/lib/misc/ReceiptPrinter.php). It includes justification, boldness, and a barcode.

Available methods
-----------------

### __construct($fp)
Construct new print object.

Parameters:
- `resource $fp`: File pointer to print to. Will open `php://stdout` if none is specified.

### text($str)
Add text to the buffer. Text should either be followed by a line-break, or `feed()` should be called after this.

Parameters:
- `string $str`: The string to print.

### feed($lines)
Print and feed line / Print and feed n lines.

Parameters:
- `int $lines`: Number of lines to feed

### selectPrintMode($mode)
Select print mode(s).

Parameters:
- `int $mode`: The mode to use. Default is `Escpos::MODE_FONT_A`, with no special formatting. This has a similar effect to running `initialize()`.

Several MODE_* constants can be OR'd together passed to this function's `$mode` argument. The valid modes are:
- `MODE_FONT_A`
- `MODE_FONT_B`
- `MODE_EMPHASIZED`
- `MODE_DOUBLE_HEIGHT`
- `MODE_DOUBLE_WIDTH`
- `MODE_UNDERLINE`
	
### setUnderline($underline)
Set underline for printed text.

Parameters:
- `int $underline`: Either `true`/`false`, or one of `Escpos::UNDERLINE_NONE`, `Escpos::UNDERLINE_SINGLE` or `Escpos::UNDERLINE_DOUBLE`. Defaults to `Escpos::UNDERLINE_SINGLE`.

### initialize()
Initialize printer. This resets formatting back to the defaults.

### setEmphasis($on)
Turn emphasized mode on/off.

Parameters:
- `boolean $on`: true for emphasis, false for no emphasis.

### setDoubleStrike($on)
Turn double-strike mode on/off.

Parameters:
- `boolean $on`: true for double strike, false for no double strike.

### setFont($font)
Select font. Most printers have two fonts (Fonts A and B), and some have a third (Font C).

Parameters:
- `int $font`: The font to use. Must be either `Escpos::FONT_A`, `Escpos::FONT_B`, or `Escpos::FONT_C`.

### setJustification($justification)
Select justification.

Parameters:
- `int $justification`: One of `Escpos::JUSTIFY_LEFT`, `Escpos::JUSTIFY_CENTER`, or `Escpos::JUSTIFY_RIGHT`.

### feedReverse($lines)
Print and reverse feed n lines.

Parameters:
- `int $lines`: number of lines to feed. If not specified, 1 line will be fed.

### cut($mode, $lines)
Cut the paper.

Parameters:
- `int $mode`: Cut mode, either `Escpos::CUT_FULL` or `Escpos::CUT_PARTIAL`. If not specified, `Escpos::CUT_FULL` will be used.
- `int $lines`: Number of lines to feed before cutting. If not specified, 3 will be used.

### setBarcodeHeight($height)
Set barcode height.
 
Parameters:
- `int $height`: Height in dots. If not specified, 8 will be used.

### barcode($content, $type)
Print a barcode.

Parameters:
- `string $content`: The information to encode.
- `int $type`: The barcode standard to output. If not specified, `Escpos::BARCODE_CODE39` will be used.

Currently supported barcode standards are (depending on your printer):
- `BARCODE_UPCA`
- `BARCODE_UPCE`
- `BARCODE_JAN13`
- `BARCODE_JAN8`
- `BARCODE_CODE39`
- `BARCODE_ITF`
- `BARCODE_CODABAR`

Note that some barcode standards can only encode numbers, so attempting to print non-numeric codes with them may result in strange behaviour.

### pulse($pin, $on_mm, $off_ms)
Generate a pulse, for opening a cash drawer if one is connected. The default settings (0, 120, 240) should open an Epson drawer.

Parameters:
- `int $pin`: 0 or 1, for pin 2 or pin 5 kick-out connector respectively.
- `int $on_ms`: pulse ON time, in milliseconds.
- `int $off_ms`: pulse OFF time, in milliseconds.

Further notes
-------------
Posts I've written up for people who are learning how to use receipt printers:
* [What is ESC/POS, and how do I use it?](http://mike.bitrevision.com/blog/what-is-escpos-and-how-do-i-use-it), which documents the output of test.php.
* [Setting up an Epson receipt printer](http://mike.bitrevision.com/blog/2014-20-26-setting-up-an-epson-receipt-printer)

Vendor documentation
--------------------
Epson notes that not all of its printers support all ESC/POS features, and includes a table in their documentation:
* [FAQ about ESC/POS from Epson](http://content.epson.de/fileadmin/content/files/RSD/downloads/escpos.pdf)

Note that many printers produced by other vendors use the same standard, and may or may not be compatible.
