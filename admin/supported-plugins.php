<?php
/**
 * "Supported plugins" admin screens
 *
 * @package Achievements
 * @subpackage AdminSupportedPlugins
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Set up the Supported Plugins admin page before any output is sent. Register contextual help and screen options for this page.
 *
 * @since 3.0
 */
function dpa_supported_plugins_on_load() {
	// Help panel - overview text
	get_current_screen()->add_help_tab( array(
		'id'      => 'dpa-supported-plugins-overview',
		'title'   => __( 'Overview', 'dpa' ),
		'content' =>
			'<p>' . __( 'Learn about and discover the plugins that are supported by Achievements. This screen allows you to customise your view in three main ways; Detail view, List view, and Grid view. A powerful search box and filter gives you even more controls to see exactly what you want to.', 'dpa' ) . '</p>'
	) );

	// Help panel - views text
	get_current_screen()->add_help_tab( array(
		'id'      => 'dpa-supported-plugins-views',
		'title'   => __( 'Views', 'dpa' ),
		'content' =>
			'<p>' . __( "<strong>Grid view</strong> displays high-quality artwork of each plugin, showing you at a glance the plugins supported by Achievements.", 'dpa' ) . '</p>' .
			'<p>' . __( "<strong>List view</strong> drills down into each plugin, showing you its WordPress.org community rating, its authors, and whether you already have the plugin installed.", 'dpa' ) . '</p>' .
			'<p>' . __( "<strong>Detail view</strong> goes even further, showing you exactly which features of the plugin are supported, and the latest news from the authors.", 'dpa' ) . '</p>'
	) );

	// Help panel - sidebar links
	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'dpa' ) . '</strong></p>' .
		'<p>' . __( '<a href="http://buddypress.org/community/groups/achievements/forum/">Support Forums</a>', 'dpa' ) . '</p>'
	);

	// Detail view - metaboxes
	$plugins = dpa_get_supported_plugins();
	add_meta_box( 'dpa-supported-plugins-info', __( 'Plugin Information', 'dpa' ), 'dpa_supported_plugins_mb_info', 'dpa_achievement_page_achievements-plugins', 'side', 'core', array( $plugins ) );
}

/**
 * Supported Plugins admin screen
 *
 * @since 3.0
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

	// See if a cookie has been set to remember which filter the user was on last. Defaults to 'all'.
	if ( ! empty( $_COOKIE['dpa_sp_filter'] ) && in_array( trim( $_COOKIE['dpa_sp_filter'] ), array( 'all', '0', '1', ) ) )
	 	$filter = trim( $_COOKIE['dpa_sp_filter'] );
	else
		$filter = 'all';
	?>
	<form class="dpa-toolbar" enctype="multipart/form-data" id="dpa-toolbar" method="post"  name="dpa-toolbar">

		<?php // Required to remember the state of the metaboxes on the Detail view ?>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

		<div id="dpa-toolbar-wrapper">
			<input type="search" results="5" name="dpa-toolbar-search" id="dpa-toolbar-search" placeholder="<?php esc_attr_e( 'Search for a plugin...', 'dpa' ); ?>" />

			<select class="<?php if ( ! $GLOBALS['is_gecko'] ) echo 'dpa-ff-hack'; ?>" name="dpa-toolbar-filter" id="dpa-toolbar-filter">
				<option value="all" <?php selected( $filter, 'all' ); ?>><?php esc_html_e( 'All Plugins', 'dpa' ); ?></option>
				<option value="0"   <?php selected( $filter, '0'   ); ?>><?php esc_html_e( 'Available Plugins', 'dpa' ); ?></option>
				<option value="1"   <?php selected( $filter, '1'   ); ?>><?php esc_html_e( 'Installed Plugins', 'dpa' ); ?></option>
			</select>

			<ul id="dpa-toolbar-views">
				<li class="<?php if ( 'grid' == $view ) echo 'current'; ?>"><a class="grid" title="<?php esc_attr_e( 'Grid view', 'dpa' ); ?>" href="#"></a></li>
				<li class="<?php if ( 'list' == $view ) echo 'current'; ?>"><a class="list" title="<?php esc_attr_e( 'List view', 'dpa' ); ?>" href="#"></a></li>
				<li class="<?php if ( 'detail' == $view ) echo 'current'; ?>"><a class="detail" title="<?php esc_attr_e( 'Detail view', 'dpa' ); ?>" href="#"></a></li>
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

	<div id="dpa-info-column">
		<?php dpa_supported_plugins_mb_switcher(); ?>

		<div class="metabox-holder">
				<?php do_meta_boxes( 'dpa_achievement_page_achievements-plugins', 'side', null ); ?>
		</div>
	</div>

	<div id="dpa-detail-contents">
		<?php foreach ( $plugins as $plugin ) :
			$class = $plugin->slug;

			// Record if this plugin is installed by setting the class
			if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) )
				$class .= ' installed';
			else
				$class .= ' notinstalled';
		?>

			<div class="<?php echo esc_attr( $class ); if ( $last_plugin == $plugin->slug ) echo ' current'; ?>">
				<div class="plugin-title">
					<h3><?php echo convert_chars( wptexturize( $plugin->name ) ); ?></h3>
					<a class="socialite twitter" href="http://twitter.com/share" data-text="<?php echo esc_attr( convert_chars( wptexturize( $plugin->name ) ) ); ?>" data-related="pgibbs" data-url="<?php echo esc_attr( $plugin->wporg_url ); ?>" target="_blank"><?php _e( 'Share on Twitter', 'dpa' ); ?></a>
					<a class="socialite googleplus" href="<?php echo esc_attr( esc_url( 'https://plus.google.com/share?url=' . urlencode( $plugin->wporg_url ) ) ); ?>" data-size="medium" data-href="<?php echo esc_attr( $plugin->wporg_url ); ?>" target="_blank"><?php _e( 'Share on Google', 'dpa' ); ?></a>
				</div><!-- .plugin-title -->

				<div class="plugin-rss">
					<h3><?php _e( 'Latest News', 'dpa' ); ?></h3>

					<?php
					// Fetch each plugin's RSS feed, and parse the updates.
					$rss = fetch_feed( esc_url( $plugin->rss_url ) );
					if ( ! is_wp_error( $rss ) ) {
						$content = '<ul>';
						$items   = $rss->get_items( 0, $rss->get_item_quantity( 5 ) );

						foreach ( $items as $item ) {
							// Prepare excerpt.
							$excerpt = strip_tags( html_entity_decode( $item->get_content(), ENT_QUOTES, get_option( 'blog_charset' ) ) );

							// Use BuddyPress' excerpt function if it exists.
							if ( function_exists( 'bp_create_excerpt' ) )
								$excerpt = bp_create_excerpt( $excerpt, 250, array( 'ending' => _x( '&hellip;', 'ellipsis character at end of post excerpt to show text has been truncated', 'dpa' ) ) );
							else
								$excerpt = wp_html_excerpt( $excerpt, 250 ) . _x( '&hellip;', 'ellipsis character at end of post excerpt to show text has been truncated', 'dpa' );

							// Skip posts with no words
							if ( empty( $excerpt ) )
								continue;

							// Prepare date, author, excerpt, title, url.
							$date    = strtotime( strip_tags( $item->get_date() ) );
							$date    = gmdate( get_option( 'date_format' ), $date );

							$author  = convert_chars( wptexturize( strip_tags( $item->get_author()->get_name() ) ) );
							$excerpt = convert_chars( wptexturize( wp_kses_data( $excerpt ) ) );
							$title   = convert_chars( wptexturize( strip_tags( $item->get_title() ) ) );
							$url     = strip_tags( $item->get_permalink() );

							// Build the output
							$content .= '<li>';
							$content .= sprintf( '<h4><a href="%1$s">%2$s</a></h4>', esc_attr( esc_url( $url ) ), esc_html( $title ) );

							// translators: "By AUTHOR, DATE".
							$content .= sprintf( __( '<p>By %1$s, %2$s</p>' ), $author, $date );

							$content .= sprintf( '<p>%1$s</p>', $excerpt );
							$content .= '</li>';
						}
						echo $content . '</ul>';

					} else {
						echo '<p>' . __( 'No news found.', 'dpa' ) . '</p>';
					}
					?>
				</div><!-- .plugin-rss -->
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
 * @since 3.0
 */
function dpa_supported_plugins_list() {
	$plugins = dpa_get_supported_plugins();

	// Sort list of plugins by rating
	if ( ! empty( $_GET['order'] ) && 'rating' == $_GET['order'] )
		uasort( $plugins, create_function( '$a, $b', 'return strnatcasecmp($a->rating, $b->rating);' ) );

	// Sort by plugin status (installed, not installed)
	elseif ( ! empty( $_GET['order'] ) && 'status' == $_GET['order'] )
		uasort( $plugins, create_function( '$a, $b', 'return strnatcasecmp($a->install_status["status"], $b->install_status["status"]);' ) );

	// Sort alphabetically
	else
		uasort( $plugins, create_function( '$a, $b', 'return strnatcasecmp($a->name, $b->name);' ) );

	// Build URL for non-javascript table sorting
	$redirect_to = remove_query_arg( array(), self_admin_url( 'edit.php?post_type=dpa_achievement&page=achievements-plugins' ) );
?>

	<table class="widefat">
		<caption class="screen-reader-text"><?php _e( 'This table lists all of the plugins that Achievements has built-in support for. For each plugin, it shows a banner, its WordPress.org plugin rating, who contributed to its development, and whether your site has the plugin installed or not.', 'dpa' ); ?></caption>
		<thead>
			<tr>
				<th scope="col"></th>
				<th scope="col"><?php _e( 'Plugin', 'dpa' ); ?></th>
				<th scope="col"><a href="<?php echo esc_attr( add_query_arg( 'order', 'rating', $redirect_to ) ); ?>"><?php _e( 'Rating', 'dpa' ); ?></a></th>
				<th scope="col"><a href="<?php echo esc_attr( add_query_arg( 'order', 'status', $redirect_to ) ); ?>"><?php _e( 'Status', 'dpa' ); ?></a></th>
				<th scope="col"><?php _e( 'Authors', 'dpa' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th></th>
				<th><?php _e( 'Plugin', 'dpa' ); ?></th>
				<th><a href="#a"><?php _e( 'Rating', 'dpa' ); ?></a></th>
				<th><a href="#"><?php _e( 'Status', 'dpa' ); ?></a></th>
				<th><?php _e( 'Authors', 'dpa' ); ?></th>
			</tr>
		</tfoot>

		<tbody>

			<?php foreach ( $plugins as $plugin ) :
				// Record if this plugin is installed by setting the class
				if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) )
					$class = 'installed';
				else
					$class = 'notinstalled';
			?>
				<tr class="<?php echo esc_attr( $class ); ?>">
					<td class="plugin">
						<?php
						$image_url   = esc_url( $plugin->image->large );
						$plugin_name = convert_chars( wptexturize( $plugin->name ) );
 						printf( '<img src="%1$s" alt="%2$s" title="%3$s" class="%4$s" />', esc_url( $image_url ), esc_attr( $plugin_name ), esc_attr( $plugin_name ), esc_attr( $plugin->slug ) );
						?>
					</td>

					<td class="name"><?php echo $plugin_name; ?></td>

					<td class="rating">
						<div class="star-holder" title="<?php printf( __( 'Rated %1$s out of 100 by the WordPress.org community', 'dpa' ), number_format_i18n( $plugin->rating ) ); ?>">
							<div class="star star-rating" style="width: <?php echo esc_attr( $plugin->rating ); ?>px"></div>
							<div class="star star5"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '5 stars', 'dpa' ); ?>" /></div>
							<div class="star star4"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '4 stars', 'dpa' ); ?>" /></div>
							<div class="star star3"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '3 stars', 'dpa' ); ?>" /></div>
							<div class="star star2"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '2 stars', 'dpa' ); ?>" /></div>
							<div class="star star1"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '1 star',  'dpa' ); ?>" /></div>
						</div>
					</td>

					<?php
					// Is plugin installed?
					if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) ) {
						echo '<td class="installed"><span class="installed">' . __( 'Ready', 'dpa' ) . '</span></td>';

					// It's not installed
					} else {
						echo '<td class="notinstalled">';

						// If current user can install plugins, link directly to the plugn install screen
						if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) )
							printf( '<a class="thickbox" href="%1$s">' . __( 'Not installed', 'dpa' ) . '</a>', esc_url( $plugin->install_url ) );
						else
							_e( 'Not installed', 'dpa' );

						echo '</td>';
					}
					?>

					<td class="contributors">
						<?php
						foreach ( $plugin->contributors as $name => $gravatar_url ) {
							// Sanitise plugin info as it may have been fetched from wporg
							$gravatar_url = esc_url( $gravatar_url );
							$profile_url  = esc_url( 'http://profiles.wordpress.org/users/' . urlencode( $name ) );
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
 * @since 3.0
 */
function dpa_supported_plugins_grid() {
	$plugins = dpa_get_supported_plugins();

	foreach ( $plugins as $plugin ) {
		// Record if this plugin is installed by setting the class
		if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) )
			$class = 'installed';
		else
			$class = 'notinstalled';

		printf( '<a href="#" class="%1$s"><img class="%2$s" src="%3$s" alt="%4$s" title="%4$s" /></a>', esc_attr( $class ), esc_attr( $plugin->slug ), esc_attr( $plugin->image->large ), esc_attr( $plugin->name ) );
	}
}

/**
 * The metabox for the "select a plugin" dropdown box on the Supported Plugins grid view.
 *
 * @since 3.0
 */
function dpa_supported_plugins_mb_switcher() {
	$last_plugin = '';

	// See if a cookie has been set to remember the last viewed plugin
	if ( ! empty( $_COOKIE['dpa_sp_lastplugin'] ) )
		$last_plugin = trim( $_COOKIE['dpa_sp_lastplugin'] );

	// Get supported plugins
	$plugins = dpa_get_supported_plugins();

	// Build dropdown box
	echo '<select id="dpa-details-plugins">';

	foreach ( $plugins as $plugin ) {
		$class = $plugin->slug;

		// Record if this plugin is installed by setting the class
		if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) )
			$class .= ' installed';
		else
			$class .= ' notinstalled';

		// Build option for the plugin
		echo '<option class="' . esc_attr( $class ) . '"' . selected( $last_plugin, $plugin->slug ) . '>' . convert_chars( wptexturize( $plugin->name ) ) . '</li>';
	}

	echo '</select>';
}

/**
 * The metabox for the "plugin info" dropdown box on the Supported Plugins grid view.
 *
 * @since 3.0
 */
function dpa_supported_plugins_mb_info( $null, $plugins ) {
	$plugin = $plugins['args'][0][0];
	$class  = 'temp';
?>

	<p><?php echo convert_chars( wptexturize( $plugin->description ) ); ?></p>
	<ul>
		<li class="status <?php echo esc_attr( $class ); ?>">
			<?php
				// Is plugin installed?
				if ( in_array( $plugin->install_status['status'], array( 'latest_installed', 'newer_installed', 'update_available', ) ) ) {
					_e( 'Status: Ready', 'dpa' );

				// It's not installed
				} else {

					// If current user can install plugins, link directly to the install screen
					if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) )
						printf( '%1$s <a class="thickbox" href="%2$s">%3$s</a>', __( 'Status:', 'dpa' ), esc_url( $plugin->install_url ), __( 'Not installed', 'dpa' ) );
					else
					_e( 'Status: Not installed', 'dpa' );
				}
			?>
		</li>

		<li class="links"><?php printf( '<a href="%1$s" target="_new">%2$s</a>', esc_url( $plugin->wporg_url ), __( 'More info', 'dpa' ) ); ?></li>

		<li class="authors">
			<?php
				foreach ( $plugin->contributors as $name => $gravatar_url ) {
					// Sanitise plugin info as it may have been fetched from wporg
					$gravatar_url = esc_url( $gravatar_url );
					$profile_url  = esc_url( 'http://profiles.wordpress.org/users/' . urlencode( $name ) );
					$name         = convert_chars( wptexturize( wp_kses_data( $name ) ) );
					printf( '<a href="%1$s"><img src="%2$s" alt="%3$s" title="%4$s" /></a>', esc_attr( $profile_url ), esc_attr( $gravatar_url ), esc_attr( $name ), esc_attr( $name ) );
				}
			?>
		</li>
	</ul>

<?php
}