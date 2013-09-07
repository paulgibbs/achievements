<?php
/**
 * Extension for WP-PostRatings
 *
 * This file extends Achievements to support actions from WP-PostRatings
 *
 * @package Achievements
 * @subpackage ExtensionWPPostRatings
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from WP-PostRatings.
 *
 * @since Achievements (3.4)
 */
function dpa_init_wppostratings_extension() {
	achievements()->extensions->wp_postratings = new DPA_WP_PostRatings_Extension;

	// Tell the world that the WP-PostRatings extension is ready
	do_action( 'dpa_init_wppostratings_extension' );
}
add_action( 'dpa_ready', 'dpa_init_wppostratings_extension' );

/**
 * Extension to add WP-PostRatings support to Achievements
 *
 * @since Achievements (3.4)
 */
class DPA_WP_PostRatings_Extension extends DPA_Extension {
	/**
	 * Constructor
	 *
	 * Sets up extension properties. See class phpdoc for details.
	 *
	 * @since Achievements (3.4)
	 */
	public function __construct() {

		$this->actions = array(
			'rate_post' => __( 'The user rates a post.', 'dpa' ),
		);

		$this->contributors = array(
			array(
				'name'         => 'Lester Chan',
				'gravatar_url' => 'http://gravatar.com/avatar/8fdd1c4a03682246e45b8b15cd08b854',
				'profile_url'  => 'http://profiles.wordpress.org/gamerz/',
			)
		);

		$this->description     = __( "WP-PostRatings adds an AJAX rating system for your WordPress blogs&#8217 posts and pages.", 'dpa' );
		$this->id              = 'wp-postratings';
		$this->image_url       = trailingslashit( achievements()->includes_url ) . 'admin/images/wp-postratings.jpg';
		$this->name            = __( 'WP-PostRatings', 'dpa' );
		$this->rss_url         = 'http://lesterchan.net/wordpress/feed/';
		$this->small_image_url = trailingslashit( achievements()->includes_url ) . 'admin/images/wp-postratings-small.jpg';
		$this->version         = 1;
		$this->wporg_url       = 'http://wordpress.org/plugins/wp-postratings/';
	}
}