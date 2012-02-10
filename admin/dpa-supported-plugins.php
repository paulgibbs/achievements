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

	// See if a cookie has been set to remember which view the user was on last. Defaults to 'grid'.
	if ( ! empty( $_COOKIE['dpa_sp_view'] ) && in_array( $_COOKIE['dpa_sp_view'], array( 'detail', 'list', 'grid', ) ) )
	 	$view = $_COOKIE['dpa_sp_view'];
	else
		$view = 'grid';

	// See if a cookie has been set to remember the zoom level.
	if ( ! empty( $_COOKIE['dpa_sp_zoom'] ) ) {
		$zoom = (int) $_COOKIE['dpa_sp_zoom'];
		$zoom = max( 4,  $view );  // Min value is 4
		$zoom = min( 10, $view );  // Max value is 10

		// If the cookie has a null value, set zoom to the default
		if ( ! $zoom )
			$zoom = 6;

	} else {
		$zoom = 6;
	}
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
				<li><a class="grid <?php if ( 'grid' == $view ) echo 'current'; ?>" title="<?php esc_attr_e( 'Grid view', 'dpa' ); ?>" href="#"></a></li>
				<li><a class="list <?php if ( 'list' == $view ) echo 'current'; ?>" title="<?php esc_attr_e( 'List view', 'dpa' ); ?>" href="#"></a></li>
				<li><a class="detail <?php if ( 'detail' == $view ) echo 'current'; ?>" title="<?php esc_attr_e( 'Detail view', 'dpa' ); ?>" href="#"></a></li>
				<li><p class="label"><?php _e( 'View', 'dpa' ); ?></p></li>
				<li class="dpa-toolbar-slider <?php if ( 'grid' == $view ) echo 'current'; ?>"><label for="dpa-toolbar-slider"><?php _e( 'Zoom', 'dpa' ); ?></label><input type="range" value="<?php echo esc_attr( $zoom ); ?>" max="10" min="4" step="2" name="dpa-toolbar-slider" id="dpa-toolbar-slider" /></li>
			</ul>
		</div>

	</form>
	<?php
}

function dpa_supported_plugins_detail() {
	echo 'Detail view';
}

/**
 * Supported Plugins list view
 *
 * Lists view consists of a table, with one row to a plugin.
 *
 * @since 1.0
 */
function dpa_supported_plugins_list() {
?>

	<table class="widefat">
		<caption>Here we assign header information to cells by setting the scope attribute.</caption>
		<thead>
			<tr>
				<th scope="col">Name</th>
				<th scope="col">Side</th>
				<th scope="col">Role</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td>Darth Vader</td>
				<td>Dark</td>
				<td>Sith</td>
			</tr>
		</tfoot>

		<tbody>
			<tr>
				<td>Obi Wan Kenobi</td>
				<td>Light</td>
				<td>Jedi</td>
			</tr>
			<tr>
				<td>Greedo</td>
				<td>South</td>
				<td>Scumbag</td>
			</tr>
		</tbody>
	</table>

<?php
}

/**
 * Supported Plugins grid view
 *
 * Grid view consists of rows and columns of large logos of plugins.
 *
 * @since 1.0
 */
function dpa_supported_plugins_grid() {
	$plugins = dpa_get_supported_plugins();

	foreach ( $plugins as $plugin ) {
		printf( '<a href="#"><img class="plugin" src="%1$s" alt="%2$s" /></a>', esc_attr( $plugin->image->large ), esc_attr( $plugin->name ) );
	}
}
?>