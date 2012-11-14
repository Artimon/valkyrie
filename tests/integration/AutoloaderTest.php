<?php

class Valkyrie_AutoloaderTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException Valkyrie_Exception
	 */
	public function testAutoloadFail() {
		new Valkyrie_NonExistingClass();
	}

	public function testAutoloadSuccess() {
		new Valkyrie_FixtureClass();
	}
}
