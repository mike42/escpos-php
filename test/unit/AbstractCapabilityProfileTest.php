<?php
/**
 * Test that all sub-classes of AbstractCapabilityProfile
 * are creating data in the right format.
 */
class EscposCapabilityProfileTest extends PHPUnit_Framework_TestCase {
	private $profiles;
	private $checklist;
	
	function setup() {
		$this -> profiles = array('DefaultCapabilityProfile', 'EposTepCapabilityProfile', 'SimpleCapabilityProfile', 'StarCapabilityProfile');
		$this -> checklist = array();
		foreach($this -> profiles as $profile) {
			$this-> checklist[] = $profile::getInstance();
		}
	}	
	
	function testSupportedCodePages() {
		foreach($this -> checklist as $obj) {
			$check = $obj -> getSupportedCodePages();
			$this -> assertTrue(is_array($check) && isset($check[0]) && $check[0] == 'CP437');
		}
	}
	
	function testSupportsBitImage() {
		foreach($this -> checklist as $obj) {
			$check = $obj -> getSupportsBitImage();
			$this -> assertTrue(is_bool($check));
		}
	}
	
	function testSupportsGraphics() {
		foreach($this -> checklist as $obj) {
			$check = $obj -> getSupportsGraphics();
			$this -> assertTrue(is_bool($check));
		}
	}
	
	function testSupportsQrCode() {
		foreach($this -> checklist as $obj) {
			$check = $obj -> getSupportsQrCode();
			$this -> assertTrue(is_bool($check));
		}
	}
}
?>