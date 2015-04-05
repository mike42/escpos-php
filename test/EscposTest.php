<?php
require_once(dirname(__FILE__) . "/../Escpos.php");

class EscposTest extends PHPUnit_Framework_TestCase {
	protected $printer;

	protected $outputFn;
	protected $outputFp;

	protected function setup() {
		$this -> outputFn = null;
		$this -> outputFp = null;
	}

	protected function setupTest() {
		/* Print to nowhere- for testing which inputs are accepted */
		$this -> printer = new Escpos(fopen("/dev/null", "wb"));
	}

	protected function setupOutputTest($name) {
		/* Print to a file - for checking output strings */
		$this -> outputFn = "test-$name";
		$this -> outputFp = fopen($this -> outputFn, "wb");
		$this -> printer = new Escpos($this -> outputFp);
	}

	protected function checkOutput($expected = null) {
		/* Check those output strings */
		fclose($this -> outputFp);
		$outp = file_get_contents($this -> outputFn);
		unlink($this -> outputFn);
		if($expected === null) {
			echo "\n".$this -> outputFn . ": Output was:\n\"" . $this -> friendlyBinary($outp) . "\"\n";
		}
		$this -> outputFn = null;
		$this -> outputFp = null;
		$this -> assertEquals($expected, $outp);
	}

	protected function tearDown() {
		/* Remove test files when a case doesn't finish properly */
		if($this -> outputFn != null) {
			unlink($this -> outputFn);
		}
	}

	private function friendlyBinary($in) {
		/* Print out binary data with PHP \x00 escape codes,
			for builting test cases. */
		$chars = str_split($in);
		foreach($chars as $i => $c) {
			$code = ord($c);
			if($code < 32 || $code > 126) {
				$chars[$i] = "\\x" . bin2hex($c);
			}
		}
		return implode($chars);
	}

    public function testInitializeOutput() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> checkOutput("\x1b\x40");
    }

    public function testTextStringOutput() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> text("The quick brown fox jumps over the lazy dog\n");
		$this -> checkOutput("\x1b@The quick brown fox jumps over the lazy dog\n");
    }


    public function testTextDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> text();
		$this -> checkOutput("\x1b@");
    }

    public function testTextString() {
		$this -> setupTest();
		$this -> printer -> text("String");
		$this -> printer -> text(123);
		$this -> printer -> text();
		$this -> printer -> text(null);
		$this -> printer -> text(1.2);
		$this -> printer -> text(new FooBar("FooBar"));
    }

    public function testTextObject() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> text(new DateTime());
	}

    public function testFeedDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> feed();
		$this -> checkOutput("\x1b@\x0a");
    }

    public function testFeed3Lines() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> feed(3);
		$this -> checkOutput("\x1b@\x1bd\x03");
    }

    public function testFeedZero() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> feed(0);
    }

    public function testFeedNonInteger() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> feed("ab");
    }

    public function testFeedTooLarge() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> feed(256);
	}

	/* Print mode */
    public function testSelectPrintModeDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> selectPrintMode();
		$this -> checkOutput("\x1b@\x1b!\x00");
    }

	public function testSelectPrintModeAcceptedValues() {
		/* This iterates over a bunch of numbers, figures out which
           ones contain invalid flags, and checks that the driver
           rejects those, but accepts the good inputs */
		$this -> setupTest();
		for($i = -1; $i <= 256; $i++) {
			$invalid = ($i < 0) || ($i > 255) || (($i & 2) == 2) || (($i & 4) == 4) || (($i & 64) == 64);
			$failed = false;
			try {
				$this -> printer -> selectPrintMode($i);
			} catch(Exception $e) {
				$failed = true;
			}
			$this -> assertEquals($failed, $invalid);
		}
    }

	/* Underline */
    public function testSetUnderlineDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setUnderline();
		$this -> checkOutput("\x1b@\x1b-\x01");
    }

    public function testSetUnderlineOff() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setUnderline(Escpos::UNDERLINE_NONE);
		$this -> checkOutput("\x1b@\x1b-\x00");
    }

    public function testSetUnderlineOn() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setUnderline(Escpos::UNDERLINE_SINGLE);
		$this -> checkOutput("\x1b@\x1b-\x01");
    }

    public function testSetUnderlineDbl() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setUnderline(Escpos::UNDERLINE_DOUBLE);
		$this -> checkOutput("\x1b@\x1b-\x02");
    }

    public function testSetUnderlineAcceptedValues() {
		$this -> setupTest();
		$this -> printer -> setUnderline(0);
		$this -> printer -> setUnderline(1);
		$this -> printer -> setUnderline(2);
		/* These map to 0 & 1 for interchangeability with setEmphasis */
		$this -> printer -> setUnderline(true);
		$this -> printer -> setUnderline(false);
	}

    public function testSetUnderlineTooLarge() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setUnderline(3);
	}

    public function testSetUnderlineNegative() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setUnderline(-1);
	}

    public function testSetUnderlineNonInteger() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setUnderline("Hello");
	}

	/* Emphasis */
    public function testSetEmphasisDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setEmphasis();
		$this -> checkOutput("\x1b@\x1bE\x01");
    }

    public function testSetEmphasisOn() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setEmphasis(true);
		$this -> checkOutput("\x1b@\x1bE\x01");
    }

    public function testSetEmphasisOff() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setEmphasis(false);
		$this -> checkOutput("\x1b@\x1bE\x00");
    }

    public function testSetEmphasisNonBoolean() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setEmphasis(7);
	}

	/* Double strike */
    public function testSetDoubleStrikeDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setDoubleStrike();
		$this -> checkOutput("\x1b@\x1bG\x01");
    }

    public function testSetDoubleStrikeOn() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setDoubleStrike(true);
		$this -> checkOutput("\x1b@\x1bG\x01");
    }

    public function testSetDoubleStrikeOff() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setDoubleStrike(false);
		$this -> checkOutput("\x1b@\x1bG\x00");
    }

    public function testSetDoubleStrikeNonBoolean() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setDoubleStrike(4);
	}

	/* Font */
	public function testSetFontDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setFont();
		$this -> checkOutput("\x1b@\x1bM\x00");
	}

	public function testSetFontAcceptedValues() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setFont(Escpos::FONT_A);
		$this -> printer -> setFont(Escpos::FONT_B);
		$this -> printer -> setFont(Escpos::FONT_C);
		$this -> checkOutput("\x1b@\x1bM\x00\x1bM\x01\x1bM\x02");
	}

	public function testSetFontNegative() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setFont(-1);
	}


	public function testSetFontTooLarge() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setFont(3);
	}

	public function testSetFontNonInteger() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setFont('hello');
	}

	/* Justification */
	public function testSetJustificationDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setJustification();
		$this -> checkOutput("\x1b@\x1ba\x00");
	}

	public function testSetJustificationLeft() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setJustification(Escpos::JUSTIFY_LEFT);
		$this -> checkOutput("\x1b@\x1ba\x00");
	}

	public function testSetJustificationRight() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setJustification(Escpos::JUSTIFY_RIGHT);
		$this -> checkOutput("\x1b@\x1ba\x02");
	}

	public function testSetJustificationCenter() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setJustification(Escpos::JUSTIFY_CENTER);
		$this -> checkOutput("\x1b@\x1ba\x01");
	}

	public function testSetJustificationNegative() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setJustification(-1);
	}


	public function testSetJustificationTooLarge() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setFont(3);
	}

	public function testSetJustificationNonInteger() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setJustification('hello');
	}

	/* Reverse feed */
	public function testFeedReverseDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> feedReverse();
		$this -> checkOutput("\x1b@\x1be\x01");
	}

	public function testFeedReverse3() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> feedReverse(3);
		$this -> checkOutput("\x1b@\x1be\x03");
	}

	public function testFeedReverseNegative() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> feedReverse(-1);
	}

	public function testFeedReverseTooLarge() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> feedReverse(256);
	}

	public function testFeedReverseNonInteger() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> feedReverse('hello');
	}

	/* Cut */
	public function testCutDefault() {
		// TODO check what the accepted range of values should be for $line
		// cut($mode = self::CUT_FULL, $lines = 3)
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> cut();
		$this -> checkOutput("\x1b@\x1dVA\x03");
	}

	/* Set barcode height */
	public function testSetBarcodeHeightDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setBarcodeHeight();
		$this -> checkOutput("\x1b@\x1dh\x08");
	}

	public function testBarcodeHeight10() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setBarcodeHeight(10);
		$this -> checkOutput("\x1b@\x1dh\x0a");
	}

	public function testSetBarcodeHeightNegative() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setBarcodeHeight(-1);
	}

	public function testSetBarcodeHeightTooLarge() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setBarcodeHeight(256);
	}

	public function tesSetBarcodeHeightNonInteger() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setBarcodeHeight('hello');
	}

	/* Barcode */
	public function testBarcodeCode39() {
		// TODO construct more detailed tests for each barcode type
		// barcode($content, $type = self::BARCODE_CODE39)
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> barcode("1234");
		$this -> checkOutput("\x1b@\x1dk\x041234\x00");		
	}

	/* Pulse */
	function testPulseDefault() {
		// TODO add tests for range of acceptable values
		// pulse($pin = 0, $on_ms = 120, $off_ms = 240)
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> pulse();
		$this -> checkOutput("\x1b@\x1bp0<x");
	}
	
	/* Set reverse */
    public function testSetReverseColorsDefault() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setReverseColors();
		$this -> checkOutput("\x1b@\x1dB\x01");
    }

    public function testSetReverseColorsOn() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setReverseColors(true);
		$this -> checkOutput("\x1b@\x1dB\x01");
    }

    public function testSetReverseColorsOff() {
		$this -> setupOutputTest(__FUNCTION__);
		$this -> printer -> setReverseColors(false);
		$this -> checkOutput("\x1b@\x1dB\x00");
    }

    public function testSetReverseColorsNonBoolean() {
		$this -> setupTest();
		$this -> setExpectedException('InvalidArgumentException');
		$this -> printer -> setReverseColors(7);
	}

	/* Bit image print */
	public function testBitImageBlack() {
		$this -> setupOutputTest(__FUNCTION__);
		$img = new EscposImage(dirname(__FILE__)."/resources/canvas_black.png");
		$this -> printer -> bitImage($img);
		$this -> checkOutput("\x1b@\x1dv0\x00\x01\x00\x01\x00\x80");
	}

	public function testBitImageWhite() {
		$this -> setupOutputTest(__FUNCTION__);
		$img = new EscposImage(dirname(__FILE__)."/resources/canvas_white.png");
		$this -> printer -> bitImage($img);
		$this -> checkOutput("\x1b@\x1dv0\x00\x01\x00\x01\x00\x00");
	}
	
	public function testBitImageBoth() {
		$this -> setupOutputTest(__FUNCTION__);
		$img = new EscposImage(dirname(__FILE__)."/resources/black_white.png");
		$this -> printer -> bitImage($img);
		$this -> checkOutput("\x1b@\x1dv0\x00\x01\x00\x02\x00\xc0\x00");
	}
	
	public function testBitImageTransparent() {
		$this -> setupOutputTest(__FUNCTION__);
		$img = new EscposImage(dirname(__FILE__)."/resources/black_transparent.png");
		$this -> printer -> bitImage($img);
		$this -> checkOutput("\x1b@\x1dv0\x00\x01\x00\x02\x00\xc0\x00");
	}
	
	/* Graphics print */
	public function testGraphicsWhite() {
		$this -> setupOutputTest(__FUNCTION__);
		$img = new EscposImage(dirname(__FILE__)."/resources/canvas_white.png");
		$this -> printer -> graphics($img);
		$this -> checkOutput("\x1b@\x1d(L\x0b\x000p0\x01\x011\x01\x00\x01\x00\x00\x1d(L\x02\x0002");
	}
	
	public function testGraphicsBlack() {
		$this -> setupOutputTest(__FUNCTION__);
		$img = new EscposImage(dirname(__FILE__)."/resources/canvas_black.png");
		$this -> printer -> graphics($img);
		$this -> checkOutput("\x1b@\x1d(L\x0b\x000p0\x01\x011\x01\x00\x01\x00\x80\x1d(L\x02\x0002");
	}
	
		
	public function testGraphicsBoth() {
		$this -> setupOutputTest(__FUNCTION__);
		$img = new EscposImage(dirname(__FILE__)."/resources/black_white.png");
		$this -> printer -> graphics($img);
		$this -> checkOutput("\x1b@\x1d(L\x0c\x000p0\x01\x011\x02\x00\x02\x00\xc0\x00\x1d(L\x02\x0002");
	}
	
	public function testGraphicsTransparent() {
		$this -> setupOutputTest(__FUNCTION__);
		$img = new EscposImage(dirname(__FILE__)."/resources/black_transparent.png");
		$this -> printer -> graphics($img);
		$this -> checkOutput("\x1b@\x1d(L\x0c\x000p0\x01\x011\x02\x00\x02\x00\xc0\x00\x1d(L\x02\x0002");
	}
	
}

/*
 * For testing that string-castable objects are handled
 */
class FooBar {
	private $foo;
	public function __construct($foo) {
		$this -> foo = $foo;
	}
	
	public function __toString() {
		return $this -> foo;
	}
}
?>
