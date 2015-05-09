<?php
class StarCapabilityProfile extends DefaultCapabilityProfile {
	function getSupportedCodePages() {
		return null;
	}

	function getSupportsStartCommands() {
		/* Allows Escpos.php to substitute emulated ESC/POS commands with native ones for this printer. */ 
		return true;
	}
}