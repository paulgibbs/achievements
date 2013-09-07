<?php
class DPA_UnitTestCase extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
	}

	function clean_up_global_scope() {
		parent::clean_up_global_scope();
	}

	function assertPreConditions() {
		parent::assertPreConditions();

		// Reinit some of the globals that might have been cleared by DPA_UnitTestCase::clean_up_global_scope().
	}
}
