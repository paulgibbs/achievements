<?php
/**
 * "Supported plugins" admin screen
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

					<?php foreach ( $plugins as $plugin ) : ?>
						<div class="plugin-title">
							<div class="vignette"></div>
							<img alt="<?php echo esc_attr( $plugin[0] ); ?>" src="<?php echo esc_attr( $plugin[1] ); ?>" />
							<h2><?php echo $plugin[0]; ?></h2>
						</div>
						<div class="plugin-description">
							<p class="shortdesc"><?php echo $plugin[2]; ?></p>
							<p class="achievements-button"><a href="#">Install Now</a></p>
						</div>
					<?php endforeach; ?>

				</div><!-- #post-body-content -->
			</div><!-- #post-body -->

		</div><!-- #poststuff -->
	</div><!-- .wrap -->

<?php }
?>