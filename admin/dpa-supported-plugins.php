<?php
/**
 * "Supported plugins" admin screens
 *
 * @package Achievements
 * @subpackage Administration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function dpa_supported_plugins() {
	// Third-party plugins supported in core
	$plugins = array(
		array( __( 'BuddyPress', 'dpa' ), plugins_url( 'achievements/images/buddypress.png' ), __( 'Social networking in a box. Build a social network for your company, school, sports team or niche community.', 'dpa' ) ),
		array( __( 'bbPress', 'dpa' ), plugins_url( 'achievements/images/bbpress.png' ), __( 'bbPress is forum software with a twist from the creators of WordPress.', 'dpa' ) ),
		array( __( 'WP e-Commerce', 'dpa' ), plugins_url( 'achievements/images/wpecommerce.jpg' ), __( 'WP e-Commerce is a free WordPress Shopping Cart Plugin that lets customers buy your products, services and digital downloads online.', 'dpa' ) ),
	);
?>

	<div class="wrap">
		<?php screen_icon( 'options-general' ); ?>
		<h2><?php _e( 'Supported Plugins', 'dpa' ); ?></h2>

		<div id="poststuff">
			<div id="post-body">
				<div id="post-body-content">
				</div><!-- #post-body-content -->
			</div><!-- #post-body -->

		</div><!-- #poststuff -->
	</div><!-- .wrap -->

<?php }
?>