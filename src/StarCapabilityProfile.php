<?php
class StarCapabilityProfile extends DefaultCapabilityProfile {
	function getSupportedCodePages() {
		// TODO populate this from the docs
		return array(0 => CodePage::CP437);
	}

	function getSupportsStarCommands() {
		/* Allows Escpos.php to substitute emulated ESC/POS commands with native ones for this printer. */ 
		return true;
	}
}