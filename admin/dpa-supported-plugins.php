<?php
/**
 * "Supported plugins" admin screens
 *
 * @package Achievements
 * @subpackage Administration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Supported Plugins admin screen
 *
 * @since 1.0
 */
function dpa_supported_plugins() {
	// See if a cookie has been set to remember which view the user was on last. Defaults to 'grid'.
	if ( ! empty( $_COOKIE['dpa_sp_view'] ) && in_array( $_COOKIE['dpa_sp_view'], array( 'detail', 'list', 'grid', ) ) )
	 	$view = $_COOKIE['dpa_sp_view'];
	else
		$view = 'grid';
?>

	<div class="wrap">
		<?php screen_icon( 'options-general' ); ?>
		<h2><?php _e( 'Supported Plugins', 'dpa' ); ?></h2>

		<div id="poststuff">
			<div id="post-body">
				<div id="post-body-content">
					<?php dpa_supported_plugins_header(); ?>

					<div class="detail <?php if ( 'detail' == $view ) echo 'current'; ?>"><?php dpa_supported_plugins_detail(); ?></div>
					<div class="list <?php if ( 'list' == $view ) echo 'current'; ?>"><?php dpa_supported_plugins_list(); ?></div>
					<div class="grid <?php if ( 'grid' == $view ) echo 'current'; ?>"><?php dpa_supported_plugins_grid(); ?></div>
				</div>
			</div><!-- #post-body -->

		</div><!-- #poststuff -->
	</div><!-- .wrap -->

<?php
}

/**
 * Common toolbar header for supported plugins header screen
 *
 * @global achievements $achievements Main Achievements object
 * @since 1.0
 */
function dpa_supported_plugins_header() {
	global $achievements;

	?>
	<form name="dpa-toolbar" method="post" enctype="multipart/form-data">

		<div id="dpa-toolbar-wrapper">
			<input type="search" results="5" name="dpa-toolbar-search" id="dpa-toolbar-search" />
			<select class="<?php if ( ! $GLOBALS['is_gecko'] ) echo 'dpa-ff-hack'; ?>" name="dpa-toolbar-filter" id="dpa-toolbar-filter">
				<option value="all"><?php esc_html_e( 'All Plugins', 'dpa' ); ?></option>
				<option value="available"><?php esc_html_e( 'Available Plugins', 'dpa' ); ?></option>
				<option value="installed"><?php esc_html_e( 'Installed Plugins', 'dpa' ); ?></option>
			</select>

			<ul id="dpa-toolbar-views">
				<li><a class="grid" href="#"></a></li>
				<li><a class="list" href="#"></a></li>
				<li><a class="detail" href="#"></a></li>
				<li><p class="label"><?php _e( 'View', 'dpa' ); ?></p></li>
				<li class="dpa-toolbar-slider"><label for="dpa-toolbar-slider"><?php _e( 'Zoom', 'dpa' ); ?></label><input type="range" value="5" max="10" min="1" name="dpa-toolbar-slider" /></li>
			</ul>
		</div>

	</form>
	<?php
}

function dpa_supported_plugins_detail() {
}
function dpa_supported_plugins_list() {
}
function dpa_supported_plugins_grid() {
}
?>