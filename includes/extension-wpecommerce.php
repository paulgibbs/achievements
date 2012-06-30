<?php
/**
 * Extension for WP e-Commerce
 *
 * This file extends Achievements to support actions from WP e-Commerce
 *
 * @package Achievements
 * @subpackage ExtensionWPeCommerce
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from WP e-Commerce.
 *
 * @since 3.0
 */
function dpa_init_wpecommerce_extension() {
	achievements()->extensions->wp_ecommerce = new DPA_WPeCommerce_Extension;
}
add_action( 'dpa_ready', 'dpa_init_wpecommerce_extension' );

/**
 * Extension to add WordPress e-Commerce support to Achievements
 *
 * @since 3.0
 */
class DPA_WPeCommerce_Extension extends DPA_Extension {
	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_actions() {
		return array(
			'wpsc_activate_subscription' => __( 'The user sets up a PayPal Subscription', 'dpa' ),
			'wpsc_confirm_checkout'      => __( 'The user completes checkout', 'dpa' ),
 		);
	}

	/**
	 * Returns nested array of key/value pairs for each contributor to this plugin (name, gravatar URL, profile URL).
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_contributors() {
		return array(
			array(
				'name'         => 'Dan Milward',
				'gravatar_url' => 'http://www.gravatar.com/avatar/5ba89a2ce585864ce73cafa7e79d114c',
				'profile_url'  => 'http://profiles.wordpress.org/mufasa/',
			),
			array(
				'name'         => 'mychelle',
				'gravatar_url' => 'http://www.gravatar.com/avatar/da623a80bd7d7ded418c528a689520a3',
				'profile_url'  => 'http://profiles.wordpress.org/mychelle/',
			),
			array(
				'name'         => 'Gary Cao',
				'gravatar_url' => 'http://www.gravatar.com/avatar/aea5ee57d1e882ad17e95c99265784d1',
				'profile_url'  => 'http://profiles.wordpress.org/garyc40/',
			),
			array(
				'name'         => 'Justin Sainton',
				'gravatar_url' => 'http://www.gravatar.com/avatar/02fbf19ad633e203e3bc571b80ca3f66',
				'profile_url'  => 'http://profiles.wordpress.org/justinsainton/',
			),
		);
	}


	/**
	 * Plugin description
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_description() {
		return __( 'WP e-Commerce is a free WordPress Shopping Cart Plugin that lets customers buy your products, services and digital downloads online.', 'dpa' );
	}

	/**
	 * Absolute URL to plugin image.
	 *
	 * @return string
	 * @since 3.0
	 * @todo Add WP e-Commerce logo image
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
		return __( 'WP e-Commerce', 'dpa' );
	}

	/**
	 * Absolute URL to a news RSS feed for this plugin. This may be your own website.
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_rss_url() {
		return 'http://getshopped.org/blog/feed/';
	}

	/**
	 * Plugin identifier
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_id() {
		return 'WPeCommerce';
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
		return 'http://wordpress.org/extend/plugins/wp-e-commerce/';
	}
}