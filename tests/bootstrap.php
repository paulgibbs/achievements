<?php
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

function _load_achievements() {
	require dirname( dirname( __FILE__ ) ) . '/achievements.php';
}
tests_add_filter( 'muplugins_loaded', '_load_achievements' );

require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';

// Load the Achievements-specific testing tools
require dirname( __FILE__ ) . '/includes/testcase.php';
