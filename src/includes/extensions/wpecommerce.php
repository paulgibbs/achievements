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
 * @since Achievements (3.0)
 */
function dpa_init_wpecommerce_extension() {
	achievements()->extensions->wp_ecommerce = new DPA_WP_e_Commerce_Extension;

	// Tell the world that the WP e-Commerce extension is ready
	do_action( 'dpa_init_wpecommerce_extension' );
}
add_action( 'dpa_ready', 'dpa_init_wpecommerce_extension' );

/**
 * Extension to add WordPress e-Commerce support to Achievements
 *
 * @since Achievements (3.0)
 */
class DPA_WP_e_Commerce_Extension extends DPA_Extension {
	/**
	 * Constructor
	 *
	 * Sets up extension properties. See class phpdoc for details.
	 *
	 * @since Achievements (3.0)
	 */
	public function __construct() {

		$this->actions = array(
			'wpsc_activate_subscription' => __( 'The user sets up a PayPal Subscription', 'dpa' ),
			'wpsc_payment_successful'    => __( 'The user completes checkout', 'dpa' ),
		);

		$this->contributors = array(
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

		$this->description     = __( 'WP e-Commerce is a free WordPress Shopping Cart Plugin that lets customers buy your products, services and digital downloads online.', 'dpa' );
		$this->id              = 'wp-e-commerce';
		$this->image_url       = trailingslashit( achievements()->includes_url ) . 'admin/images/wp-e-commerce.jpg';
		$this->name            = __( 'WP e-Commerce', 'dpa' );
		$this->rss_url         = 'http://getshopped.org/blog/feed/';
		$this->small_image_url = trailingslashit( achievements()->includes_url ) . 'admin/images/wp-e-commerce.jpg';
		$this->version         = 1;
		$this->wporg_url       = 'http://wordpress.org/plugins/wp-e-commerce/';
	}
}