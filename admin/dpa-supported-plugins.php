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
 * @since 1.0
 */
function dpa_supported_plugins_header() {
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
				<li class="label"><p><?php _e( 'View', 'dpa' ); ?></p></li>
				<li class="dpa-toolbar-slider <?php if ( 'grid' == $view ) echo 'current'; ?>"><label for="dpa-toolbar-slider"><?php _e( 'Zoom', 'dpa' ); ?></label>
					<div data-startvalue="<?php echo esc_attr( $zoom ); ?>" id="dpa-toolbar-slider"></div>
				</li>
			</ul>
		</div>

	</form>
	<?php
}

/**
 * Supported Plugins detail view
 *
 * Detail view consists of a large display of a specific plugin's details,
 * and an RSS feed from the author's site. There is a list box on the side
 * of the screen to choose between different plugins.
 *
 * @since 1.0
 */
function dpa_supported_plugins_detail() {
	$last_plugin = '';

	// See if a cookie has been set to remember the last viewed plugin
	if ( ! empty( $_COOKIE['dpa_sp_lastplugin'] ) )
		$last_plugin = trim( $_COOKIE['dpa_sp_lastplugin'] );

	// Get supported plugins
	$plugins = dpa_get_supported_plugins();
?>

	<ul>
		<?php foreach ( $plugins as $plugin ) : ?>
			<li class="<?php echo esc_attr( $plugin->slug ); if ( $last_plugin == $plugin->slug ) echo ' current'; ?>"><?php echo convert_chars( wptexturize( wp_kses_data( $plugin->name ) ) ); ?></li>
		<?php endforeach; ?>
	</ul>

	<div id="dpa-detail-contents">
		<?php foreach ( $plugins as $plugin ) : ?>

			<div class="<?php echo esc_attr( $plugin->slug ); if ( $last_plugin == $plugin->slug ) echo ' current'; ?>">
				<h3><?php echo convert_chars( wptexturize( wp_kses_data( $plugin->name ) ) ); ?></h3>

				<div class="description">
					<h4><?php _e( 'Plugin Info', 'dpa' ); ?></h4>
					<p><?php echo convert_chars( wptexturize( wp_kses_data( $plugin->description ) ) ); ?></p>

					<?php
					// Is plugin installed?
					if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) ) {
						_e( '<p class="installed">Status: Ready</span>', 'dpa' );

					} else {
						// If current user can install plugins, link directly to the install screen
						if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) )
							printf( __( '<p>Status: <a class="thickbox" href="%1$s">Install Plugin</a></p>', 'dpa' ), esc_attr( $plugin->install_url ) );
						else
							_e( '<p>Status: Not installed</p>', 'dpa' );
					}
					?>
				</div>

				<div class="supported-events">
					<h4><?php _e( 'Supported Events', 'dpa' ); ?></h4>
					<p>@TODO Display supported events.</p>
				</div>

				<div class="author">
					<h4><?php _e( 'News From The Author', 'dpa' ); ?></h4>

					<?php
					// Fetch each plugin's RSS feed, and parse the updates.
					$rss = fetch_feed( esc_url( $plugin->rss_url ) );
					if ( ! is_wp_error( $rss ) ) {
						$content = '<ul>';
						$items   = $rss->get_items( 0, $rss->get_item_quantity( 5 ) );

						foreach ( $items as $item ) {
							// Prepare excerpt
							$excerpt = wp_html_excerpt( $item->get_content(), 200 );
							
							// Skip posts with no words
							if ( empty( $excerpt ) )
								continue;
							else
								$excerpt .= _x( '&#8230;', 'ellipsis character at end of post excerpt to show text has been truncated', 'dpa' );

							// Prepare date
							$date  = esc_html( strip_tags( $item->get_date() ) );
							$date  = gmdate( get_option( 'date_format' ), strtotime( $date ) );

							// Prepare title and URL back to the post's site
							$title = convert_chars( wptexturize( wp_kses_data( stripslashes( $item->get_title() ) ) ) );
							$url   = $item->get_permalink();

							// Build the output
							$content .= '<li>';

							// Translators: Links to blog post. Text is "name of blog post - date".
							$content .= sprintf( __( '<h5><a href="%1$s">%2$s - %3$s</a></h5>', 'dpa' ), esc_url( $url ), esc_html( $title ), esc_html( $date ) );
							$content .= '<p>' . convert_chars( wptexturize( wp_kses_data( $excerpt ) ) ) . '</p>';
							$content .= sprintf( __( '<p><a href="%1$s">Read More</a></p>', 'dpa' ), esc_url( $url ) );

							$content .= '</li>';
						}
						echo $content . '</ul>';

					} else {
						echo '<p>' . __( 'No news found.', 'dpa' ) . '</p>';
					}
					?>
				</div>
			</div>

		<?php endforeach; ?>
	</div>

<?php
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
 						printf( '<img src="%1$s" alt="%2$s" title="%3$s" class="%4$s" />', esc_attr( $image_url ), esc_attr( $plugin_name ), esc_attr( $plugin_name ), esc_attr( $plugin->slug ) );
						?>
					</td>

					<td class="name"><?php echo $plugin_name; ?></td>
					<td class="rating"><?php echo convert_chars( wptexturize( wp_kses_data( $plugin->rating ) ) ); ?></td>
					<td>
						<?php
						// Is plugin installed?
						if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) ) {
							_e( '<span class="installed">Ready</span>', 'dpa' );

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
		printf( '<a href="#"><img class="%1$s" src="%2$s" alt="%3$s" style="width: %4$s" /></a>', esc_attr( $plugin->slug ), esc_attr( $plugin->image->large ), esc_attr( $plugin->name ), esc_attr( $style ) );
	}
}
?>