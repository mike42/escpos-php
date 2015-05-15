<?php
class StarCapabilityProfile extends DefaultCapabilityProfile {
	function getSupportedCodePages() {
		// TODO populate this from the docs
		// 77 hotfix for star printer - latvian lang
		return array(0 => CodePage::CP437, 77 => 77);
	}

	function getSupportsStarCommands() {
		/* Allows Escpos.php to substitute emulated ESC/POS commands with native ones for this printer. */ 
		return true;
	}
}
