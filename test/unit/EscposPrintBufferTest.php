<?php
/**
 * Example strings are pangrams using different character sets, and are
 * testing correct code-table switching.
 * 
 * When printed, they should appear the same as in this source file.
 * 
 * Many of these test strings are from:
 * - http://www.cl.cam.ac.uk/~mgk25/ucs/examples/quickbrown.txt
 * - http://clagnut.com/blog/2380/ (mirrored from the English Wikipedia)
 */
class EscposPrintBufferTest extends PHPUnit_Framework_TestCase {
	protected $buffer;
	protected $outputConnector;
	
	protected function setup() {
		$this -> outputConnector = new DummyPrintConnector();
		$printer = new Escpos($this -> outputConnector);
		$this -> buffer = new EscposPrintBuffer($printer, $this -> outputConnector);
	}
	
	protected function checkOutput($expected = null) {
		/* Check those output strings */
		$outp = $this -> outputConnector -> getData();
		if($expected === null) {
			echo "\nOutput was:\n\"" . friendlyBinary($outp) . "\"\n";
		}
		$this -> assertEquals($expected, $outp);
	}

	protected function tearDown() {
		$this -> outputConnector -> finalize();
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

	public function testRawTextNonprintable() {
		$this -> markTestIncomplete("Filtering out non-printable characters not yet implemented.");
		//$this -> buffer -> writeTextRaw("Test" . Escpos::ESC . "v1\n");
		$this -> checkOutput();
	}

	public function testDanish() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("Quizdeltagerne spiste jordbær med fløde, mens cirkusklovnen Wolther spillede på xylofon.");
		$this -> checkOutput();
	}

	public function testGerman() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("Falsches Üben von Xylophonmusik quält jeden größeren Zwerg.\n");
		$this -> checkOutput();
	}

	public function testGreek() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("Γαζέες καὶ μυρτιὲς δὲν θὰ βρῶ πιὰ στὸ χρυσαφὶ ξέφωτο.\n");
		$this -> checkOutput();
	}

	public function testEnglish() {
		$this -> buffer -> writeText("The quick brown fox jumps over the lazy dog.\n");
		$this -> checkOutput("\x1b@The quick brown fox jumps over the lazy dog.\n");
	}

	public function testSpanish() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("El pingüino Wenceslao hizo kilómetros bajo exhaustiva lluvia y frío, añoraba a su querido cachorro.\n");
		$this -> checkOutput();
	}

	public function testFrench() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("Le cœur déçu mais l'âme plutôt naïve, Louÿs rêva de crapaüter en canoë au delà des îles, près du mälström où brûlent les novæ.\n");
		$this -> checkOutput();
	}

	public function testIrishGaelic() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("D'fhuascail Íosa, Úrmhac na hÓighe Beannaithe, pór Éava agus Ádhaimh.\n");
		$this -> checkOutput();
	}

	public function testHungarian() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("Árvíztűrő tükörfúrógép.\n");
		$this -> checkOutput();
	}
	
	public function testIcelandic() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("Kæmi ný öxi hér ykist þjófum nú bæði víl og ádrepa.");
		$this -> checkOutput();
	}

	public function testJapaneseHiragana() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText(implode("\n", array("いろはにほへとちりぬるを",  " わかよたれそつねならむ", "うゐのおくやまけふこえて",  "あさきゆめみしゑひもせす")) . "\n");
		$this -> checkOutput();
	}

	public function testJapaneseKatakana() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText(implode("\n", "イロハニホヘト チリヌルヲ ワカヨタレソ ツネナラム", "ウヰノオクヤマ ケフコエテ アサキユメミシ ヱヒモセスン") . "\n");
		$this -> checkOutput();
	}

	public function testPolish() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("Pchnąć w tę łódź jeża lub ośm skrzyń fig.\n");
		$this -> checkOutput();
	}

	public function testRussian() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("В чащах юга жил бы цитрус? Да, но фальшивый экземпляр!\n");
		$this -> checkOutput();
	}

	public function testThai() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("นายสังฆภัณฑ์ เฮงพิทักษ์ฝั่ง ผู้เฒ่าซึ่งมีอาชีพเป็นฅนขายฃวด ถูกตำรวจปฏิบัติการจับฟ้องศาล ฐานลักนาฬิกาคุณหญิงฉัตรชฎา ฌานสมาธิ\n"); // Quotation from Wikipedia
		$this -> checkOutput();
	}

	public function testTurkish() {
		$this -> markTestIncomplete("Non-ASCII character sets not yet supported.");
		$this -> buffer -> writeText("Pijamalı hasta, yağız şoföre çabucak güvendi.\n");
		$this -> checkOutput();
	}
	
	public function testArabic() {
		$this -> markTestIncomplete("Right-to-left text not yet supported.");
		$this -> buffer -> writeText("صِف خَلقَ خَودِ كَمِثلِ الشَمسِ إِذ بَزَغَت — يَحظى الضَجيعُ بِها نَجلاءَ مِعطارِ" . "\n"); // Quotation from Wikipedia
		$this -> checkOutput();
	}
	
	public function testHebrew() {
		// RTL text is more complex than the above.
		$this -> markTestIncomplete("Right-to-left text not yet supported.");
		$this -> buffer -> writeText("דג סקרן שט בים מאוכזב ולפתע מצא לו חברה איך הקליטה" . "\n");
		$this -> checkOutput();
	}
}

