<?php
/**
 * Achievement Progress post type template tags
 *
 * If you try to use an Progress post type template loops outside of the Achievement
 * post type template loop, you will need to implement your own swtich_to_blog and
 * wp_reset_postdata() handling if running in a multisite and in a
 * dpa_is_running_networkwide() configuration. Otherwise the data won't be fetched
 * from the appropriate site.
 *
 * @package Achievements
 * @subpackage ProgressTemplate
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Whether there are more achievements available in the loop
 *
 * @since 3.0
 * @return bool True if posts are in the loop
 */
function dpa_have_progress() {
	return achievements()->progress_query->have_posts();
}

/**
 * Iterate the post index in the loop. Retrieves the next post, sets up the post, sets the 'in the loop' property to true.
 *
 * @since 3.0
 */
function dpa_the_progress() {
	return achievements()->progress_query->the_post();
}