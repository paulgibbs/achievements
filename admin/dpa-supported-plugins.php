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
	if ( ! empty( $_COOKIE['dpa_sp_view'] ) && in_array( trim( $_COOKIE['dpa_sp_view'] ), array( 'detail', 'list', 'grid', ) ) )
	 	$view = trim( $_COOKIE['dpa_sp_view'] );
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
	if ( ! empty( $_COOKIE['dpa_sp_view'] ) && in_array( trim( $_COOKIE['dpa_sp_view'] ), array( 'detail', 'list', 'grid', ) ) )
	 	$view = trim( $_COOKIE['dpa_sp_view'] );
	else
		$view = 'grid';

	// See if a cookie has been set to remember the zoom level.
	if ( ! empty( $_COOKIE['dpa_sp_zoom'] ) ) {
		$zoom = (int) $_COOKIE['dpa_sp_zoom'];
		$zoom = max( 4,  $zoom );  // Min value is 4
		$zoom = min( 10, $zoom );  // Max value is 10

		// If the cookie has a null value, set zoom to the default
		if ( ! $zoom )
			$zoom = 6;

	} else {
		$zoom = 6;
	}
	?>
	<form name="dpa-toolbar" method="post" enctype="multipart/form-data">

		<div id="dpa-toolbar-wrapper">
			<input type="search" results="5" name="dpa-toolbar-search" id="dpa-toolbar-search" autofocus />
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
	$plugins = dpa_get_supported_plugins();	
	uasort( $plugins, create_function( '$a, $b', 'return strnatcasecmp($a->name, $b->name);' ) );
?>

	<table class="widefat">
		<caption class="screen-reader-text"><?php _e( 'This table lists all of the plugins that Achievements has built-in support for. For each plugin, it shows a banner, its WordPress.org plugin rating, who contributed to its development, and whether your site has the plugin installed or not.', 'dpa' ); ?></caption>
		<thead>
			<tr>
				<th scope="col"></th>
				<th scope="col"><?php _e( 'Plugin', 'dpa' ); ?></th>
				<th scope="col"><?php _e( 'Rating', 'dpa' ); ?></th>
				<th scope="col"><?php _e( 'Status', 'dpa' ); ?></th>
				<th scope="col"><?php _e( 'Contributors', 'dpa' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th></th>
				<th><?php _e( 'Plugin', 'dpa' ); ?></th>
				<th><?php _e( 'Rating', 'dpa' ); ?></th>
				<th><?php _e( 'Status', 'dpa' ); ?></th>
				<th><?php _e( 'Contributors', 'dpa' ); ?></th>
			</tr>
		</tfoot>

		<tbody>

			<?php foreach ( $plugins as $plugin ) : ?>
				<tr>
					<td class="plugin">
						<?php
						$image_url   = esc_url( $plugin->image->large );
						$plugin_name = convert_chars( wptexturize( wp_kses_data( $plugin->name ) ) );
 						printf( '<img src="%1$s" alt="%2$s" title="%3$s" />', esc_attr( $image_url ), esc_attr( $plugin_name ), esc_attr( $plugin_name ) );
						?>
					</td>

					<td class="name"><?php echo convert_chars( wptexturize( wp_kses_data( $plugin->name ) ) ); ?></td>
					<td class="rating"><?php echo convert_chars( wptexturize( wp_kses_data( $plugin->rating ) ) ); ?></td>
					<td>
						<?php
						// Is plugin installed?
						if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) ) {
							_e( '<span class="installed">Ready</span', 'dpa' );

						} else {
							// If current user can install plugins, link directly to the install screen
							if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) )
								printf( __( '<a class="thickbox" href="%1$s">Not installed</a>', 'dpa' ), esc_attr( $plugin->install_url ) );
							else
								_e( 'Not installed', 'dpa' );
						}
						?>
					</td>

					<td class="contributors">
						<?php
						foreach ( $plugin->contributors as $name => $gravatar_url ) {
							// Sanitise plugin info as it may have been fetched from wporg
							$gravatar_url = esc_url( $gravatar_url );
							$profile_url  = esc_url( 'http://profiles.wordpress.org/users/' . $name . '/profile/public/' );
							$name         = convert_chars( wptexturize( wp_kses_data( $name ) ) );

							printf( '<a href="%1$s"><img src="%2$s" alt="%3$s" title="%4$s" /></a>', esc_attr( $profile_url ), esc_attr( $gravatar_url ), esc_attr( $name ), esc_attr( $name ) );
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>

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
	// See if a cookie has been set to remember the zoom level.
	if ( ! empty( $_COOKIE['dpa_sp_zoom'] ) ) {
		$zoom = (int) $_COOKIE['dpa_sp_zoom'];
		$zoom = max( 4,  $zoom );  // Min value is 4
		$zoom = min( 10, $zoom );  // Max value is 10

		// If the cookie has a null value, set zoom to the default
		if ( ! $zoom )
			$zoom = 6;

	} else {
		$zoom = 6;
	}

	// Calculate the initial width of the image based on zoom value.
	$plugins = dpa_get_supported_plugins();
	$style   = ( ( $zoom / 10 ) * 772 ) . 'px';

	foreach ( $plugins as $plugin ) {
		printf( '<a href="#"><img class="plugin" src="%1$s" alt="%2$s" style="width: %3$s" /></a>', esc_attr( $plugin->image->large ), esc_attr( $plugin->name ), esc_attr( $style ) );
	}
}
?>