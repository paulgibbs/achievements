<?php
/**
 * Extension for BuddyStream
 *
 * This file extends Achievements to support actions from BuddyStream
 *
 * @package Achievements
 * @subpackage ExtensionBuddyStream
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from BuddyStream.
 *
 * @since 3.0
 */
function dpa_init_buddystream_extension() {
	achievements()->extensions->buddystream = new DPA_BuddyStream_Extension;
}
add_action( 'dpa_ready', 'dpa_init_buddystream_extension' );

class DPA_BuddyStream_Extension extends DPA_Extension {
	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_actions() {
		return array(
			'buddystream_facebook_activated' => __( 'The user connects their Facebook account to BuddyStream.', 'dpa' ),
			'buddystream_flickr_activated'   => __( 'The user connects their Flickr account to BuddyStream.', 'dpa' ),
			'buddystream_lastfm_activated'   => __( 'The user connects their Last.fm account to BuddyStream.', 'dpa' ),
			'buddystream_twitter_activated'  => __( 'The user connects their Twitter account to BuddyStream.', 'dpa' ),
			'buddystream_youtube_activated'  => __( 'The user connects their YouTube account to BuddyStream.', 'dpa' ),
		);
	}

	/**
	 * Returns nested array of key/value pairs for each contributor to this plugin (name, gravatar URL, profile URL).
	 *
	 * @return array
	 * @since 3.0
	 * @todo WordPress has far too many people who deserve to be listed here. Maybe link to wp-admin/credits.php?
	 */
	public function get_contributors() {
			return array(
				array(
					'name'         => 'Peter Hofman',
					'gravatar_url' => 'http://www.gravatar.com/avatar/fa62da3fa8b3997be04448e7280dad29',
					'profile_url'  => 'http://profiles.wordpress.org/blackphantom/',
				)
			);
	}


	/**
	 * Plugin description
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_description() {
		return __( 'BuddyStream is a BuddyPress plugin that will synchronize all of your favorite Social Networks to the BuddyPress activity stream.', 'dpa' );
	}

	/**
	 * Absolute URL to plugin image.
	 *
	 * @return string
	 * @since 3.0
	 * @todo Add BuddyStream logo image
	 */
	public function get_image_url() {
		return 'http://placekitten.com/772/250';
	}

	/**
	 * Plugin name
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_name() {
		return __( 'BuddyStream', 'dpa' );
	}

	/**
	 * Absolute URL to a news RSS feed for this plugin. This may be your own website.
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_rss_url() {
		return 'http://buddystream.net/blog/feed/';
	}

	/**
	 * Plugin identifier
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_id() {
		return 'BuddyStream';
	}

	/**
	 * Version number of your extension
	 *
	 * @return int
	 * @since 3.0
	 */
	public function get_version() {
		return 1;
	}

	/**
	 * Absolute URL to your plugin on WordPress.org
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_wporg_url() {
		return 'http://wordpress.org/extend/plugins/buddystream/';
	}
}