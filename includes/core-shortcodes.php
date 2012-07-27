<?php

/**
 * Achievements shortcodes
 *
 * @package Achievements
 * @subpackage CoreShortcodes
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the Achievements shortcodes
 *
 * @since 3.0
 */
function dpa_register_shortcodes() {
	achievements()->shortcodes = new DPA_Shortcodes();
}
