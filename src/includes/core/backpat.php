<?php
/**
 * Backwards compatibility
 *
 * Whenever a theme compat. template is deprecated in Achievements, often we need
 * to make sure that it continues to work for sites who have customised that template
 * with previous versions of Achievements.
 * 
 * This file contains functions that help with that.
 *
 * @package Achievements
 * @subpackage BackwardsCompatibilty
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Does the old-style notification template file exist in the current theme?
 *
 * The template was removed in version 3.5 and was replaced with the heartbeat-powered "live notifications" system.
 * This function has no filters on purpose because it's meant to only run once, as quickly as possible.
 *
 * @return bool
 * @since Achievements (3.5)
 */
function dpa_deprecated_notification_template_exists() {
	static $retval = null;

	if ( $retval !== null )
		return $retval;

	$template_name = '/feedback-achievement-unlocked.php';

	// Try to find the deprecated template. Check child theme first.
	if ( file_exists( get_stylesheet_directory() . $template_name ) )
		$retval = true;

	// Check parent theme last.
	elseif ( file_exists( get_template_directory() . $template_name ) )
		$retval = true;

	else
		$retval = false;

	return $retval;
}