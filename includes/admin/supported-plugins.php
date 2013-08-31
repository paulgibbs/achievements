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
 * @since Achievements (3.0)
 * @todo WordPress - find a way to hide "Plugin Information" and "Supported Features" from the screen option panel. Setting empty title doesn't render metabox.
 */
function dpa_supported_plugins_on_load() {

	// Call an action for plugins to hook in early
	do_action( 'dpa_supported_plugins_on_load' );

	// Help panel - overview text
	get_current_screen()->add_help_tab( array(
		'id'      => 'dpa-supported-plugins-overview',
		'title'   => __( 'Overview', 'dpa' ),
		'content' =>
			'<p>' . __( 'Learn about and discover the plugins which are supported by Achievements. This screen allows you to customize your view in three main ways; Detail view, List view, and Grid view. A powerful search box and filter gives you even more control to see exactly what you want.', 'dpa' ) . '</p>'
	) );

	// Help panel - views text
	get_current_screen()->add_help_tab( array(
		'id'      => 'dpa-supported-plugins-views',
		'title'   => __( 'Views', 'dpa' ),
		'content' =>
			'<p>' . __( "<strong>Grid view</strong> displays high-quality artwork of each plugin, showing you at a glance the plugins supported by Achievements.", 'dpa' ) . '</p>' .
			'<p>' . __( "<strong>List view</strong> drills down into each plugin, showing you its authors and whether you already have the plugin installed.", 'dpa' ) . '</p>' .
			'<p>' . __( "<strong>Detail view</strong> goes even further, showing you which features of the plugin are supported and the latest news about the plugin.", 'dpa' ) . '</p>'
	) );

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'dpa' ) . '</strong></p>' .
		'<p><a href="http://achievementsapp.com/" target="_blank">' . __( 'Achievements Website', 'dpa' ) . '</a></p>' .
		'<p><a href="http://wordpress.org/support/plugin/achievements/" target="_blank">' . __( 'Support Forums', 'dpa' ) . '</a></p>'
	);

	// Detail view - metaboxes
	add_meta_box( 'dpa-supported-plugins-info', __( 'Plugin Information', 'dpa' ), 'dpa_supported_plugins_mb_info', 'dpa_achievement_page_achievements-plugins', 'side', 'core' );
	add_meta_box( 'dpa-supported-plugins-features', __( 'Supported Features', 'dpa' ), 'dpa_supported_features_mb_info', 'dpa_achievement_page_achievements-plugins', 'side', 'core' );
}

/**
 * Returns the filter choice of the Supported Plugins Detail screen 
 *
 * @return string Name of filter (either 0, 1, or 'all')
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_get_filter() {
	$allowed_filters = array( '0', '1', 'all' );
	$filter          = ! empty( $_GET['filter'] ) && in_array( $_GET['filter'], $allowed_filters ) ? $_GET['filter'] : 'all';

	return apply_filters( 'dpa_supported_plugins_get_filter', $filter );
}

/**
 * Returns the name of the Supported Plugins screen view
 *
 * @return string Name of view (either detail, grid, or list)
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_get_view() {
	$allowed_views = array( 'detail', 'grid', 'list' );
	$view          = ! empty( $_GET['view'] ) && in_array( $_GET['view'], $allowed_views ) ? $_GET['view'] : 'grid';

	return apply_filters( 'dpa_supported_plugins_get_view', $view );
}

/**
 * Returns the name of the plugin displayed on the Supported Plugins detail view
 *
 * @return string Name of plugin
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_get_plugin() {
	$valid_extensions = array();

	// Get list of supported plugins
	$extensions = (array) achievements()->extensions;

	foreach ( $extensions as $extension ) {
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		$valid_extensions[] = sanitize_html_class( $extension->get_id() );
	}

	$view = 'buddypress';
	if ( ! empty( $_GET['plugin'] ) && in_array( $_GET['plugin'], $valid_extensions ) )
		$view = $_GET['plugin'];

	return apply_filters( 'dpa_supported_plugins_get_plugin', $view );
}

/**
 * Supported Plugins admin screen
 *
 * @since Achievements (3.0)
 */
function dpa_supported_plugins() {
	// Get current view of the Supported Plugins screen
	$view = dpa_supported_plugins_get_view();
?>
	<div class="wrap">

		<?php screen_icon( 'options-general' ); ?>
		<h2><?php _e( 'Supported Plugins', 'dpa' ); ?></h2>

		<div id="poststuff">
			<div id="post-body">
				<div id="post-body-content">
					<?php dpa_supported_plugins_header(); ?>

					<?php if ( 'detail' === $view ) : ?>
						<div class="detail"><?php dpa_supported_plugins_detail(); ?></div>

					<?php elseif ( 'grid' === $view ) : ?>
						<div class="grid"><?php dpa_supported_plugins_grid(); ?></div>

					<?php elseif ( 'list' === $view ) : ?>
						<div class="list"><?php dpa_supported_plugins_list(); ?></div>

					<?php endif; ?>
				</div>
			</div><!-- #post-body -->
		</div><!-- #poststuff -->

	</div><!-- .wrap -->
<?php
}

/**
 * Common toolbar header for supported plugins header screen
 *
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_header() {
	// Get current filter and view of the Supported Plugins screen
	$filter = dpa_supported_plugins_get_filter();
	$view   = dpa_supported_plugins_get_view();

	// Build URL
	$page_url = remove_query_arg( _dpa_supported_plugin_get_queryargs(), $_SERVER['REQUEST_URI'] );
	$page_url = add_query_arg( 'filter', $filter, $page_url );
?>
	<form class="dpa-toolbar" enctype="multipart/form-data" id="dpa-toolbar" method="post"  name="dpa-toolbar">

		<div id="dpa-toolbar-wrapper">
			<input type="search" autocomplete="off" results="5" name="dpa-toolbar-search" id="dpa-toolbar-search" spellcheck="false" placeholder="<?php esc_attr_e( 'Search for a plugin...', 'dpa' ); ?>" />

			<select class="<?php if ( ! $GLOBALS['is_gecko'] ) echo 'dpa-ff-hack'; ?>" name="dpa-toolbar-filter" id="dpa-toolbar-filter">
				<option value="all" <?php selected( $filter, 'all' ); ?>><?php esc_html_e( 'All Plugins', 'dpa' );       ?></option>
				<option value="0"   <?php selected( $filter, '0'   ); ?>><?php esc_html_e( 'Available Plugins', 'dpa' ); ?></option>
				<option value="1"   <?php selected( $filter, '1'   ); ?>><?php esc_html_e( 'Installed Plugins', 'dpa' ); ?></option>
			</select>

			<ul id="dpa-toolbar-views">
				<li class="grid <?php if ( 'grid' === $view ) echo 'current'; ?>"><a class="grid" title="<?php esc_attr_e( 'Grid view', 'dpa' ); ?>" href="<?php echo esc_url( add_query_arg( 'view', 'grid', $page_url ) ); ?>"></a></li>
				<li class="list <?php if ( 'list' === $view ) echo 'current'; ?>"><a class="list" title="<?php esc_attr_e( 'List view', 'dpa' ); ?>" href="<?php echo esc_url( add_query_arg( 'view', 'list', $page_url ) ); ?>"></a></li>
				<li class="detail <?php if ( 'detail' === $view ) echo 'current'; ?>"><a class="detail" title="<?php esc_attr_e( 'Detail view', 'dpa' ); ?>" href="<?php echo esc_url( add_query_arg( 'view', 'detail', $page_url ) ); ?>"></a></li>
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
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_detail() {
	// Get plugin to display
	$plugin = dpa_supported_plugins_get_plugin();

	// Get supported plugins
	$extensions = achievements()->extensions;
?>

	<div id="dpa-info-column">
		<?php dpa_supported_plugins_mb_switcher(); ?>

		<div class="metabox-holder">
				<?php do_meta_boxes( 'dpa_achievement_page_achievements-plugins', 'side', null ); ?>
		</div>
	</div>

	<div id="dpa-detail-contents">
		<?php foreach ( $extensions as $extension ) :
		// Extensions must inherit the DPA_Extension class
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		// Only show details for the current $plugin
		if ( $plugin !== $extension->get_id() )
			continue;

		// Record if the plugin is installed by setting the class
		$class = _dpa_is_plugin_installed( $plugin ) ? ' installed' : ' notinstalled';
		?>

			<div class="<?php echo esc_attr( $class ); ?>">
				<div class="plugin-title">
					<h3><?php echo convert_chars( wptexturize( $extension->get_name() ) ); ?></h3>
					<a class="socialite twitter" href="http://twitter.com/share" data-text="<?php echo esc_attr( convert_chars( wptexturize( $extension->get_name() ) ) ); ?>" data-related="pgibbs" data-url="<?php echo esc_attr( $extension->get_wporg_url() ); ?>" target="_blank"><?php _e( 'Share on Twitter', 'dpa' ); ?></a>
					<a class="socialite googleplus" href="<?php echo esc_attr( esc_url( 'https://plus.google.com/share?url=' . urlencode( $extension->get_wporg_url() ) ) ); ?>" data-size="medium" data-href="<?php echo esc_attr( $extension->get_wporg_url() ); ?>" target="_blank"><?php _e( 'Share on Google', 'dpa' ); ?></a>
				</div><!-- .plugin-title -->

				<div class="plugin-rss">
					<h3><?php _e( 'Latest News', 'dpa' ); ?></h3>

					<?php
					// Fetch each plugin's RSS feed, and parse the updates.
					$rss = fetch_feed( esc_url( $extension->get_rss_url() ) );
					if ( ! is_wp_error( $rss ) ) {
						$content = '<ul>';
						$items   = $rss->get_items( 0, $rss->get_item_quantity( 5 ) );

						foreach ( $items as $item ) {
							// Prepare excerpt.
							$excerpt = strip_tags( html_entity_decode( $item->get_content(), ENT_QUOTES, get_option( 'blog_charset' ) ) );
							$excerpt = wp_html_excerpt( $excerpt, 250 ) . _x( '&hellip;', 'ellipsis character at end of post excerpt to show text has been truncated', 'dpa' );

							// Skip posts with no words
							if ( empty( $excerpt ) )
								continue;

							// Prepare date, author, excerpt, title, url.
							$date    = strtotime( strip_tags( $item->get_date() ) );
							$date    = gmdate( get_option( 'date_format' ), $date );

							if ( null !== $item->get_author() )
								$author = convert_chars( wptexturize( strip_tags( $item->get_author()->get_name() ) ) );
							else
								$author = '';

							$excerpt = convert_chars( wptexturize( wp_kses_data( $excerpt ) ) );
							$title   = convert_chars( wptexturize( strip_tags( $item->get_title() ) ) );
							$url     = strip_tags( $item->get_permalink() );

							// Build the output
							$content .= '<li>';
							$content .= sprintf( '<h4><a href="%1$s">%2$s</a></h4>', esc_attr( esc_url( $url ) ), esc_html( $title ) );

							if ( ! empty( $author ) ) {
								// translators: "By AUTHOR, DATE".
								$content .= sprintf( __( '<p>By %1$s, %2$s</p>' ), $author, $date );
							}

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

		<?php
		break;
		endforeach; 
		?>
	</div>

<?php
}

/**
 * Supported Plugins list view
 *
 * Lists view consists of a table, with one row to a plugin.
 *
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_list() {
	// Get current filter and view of the Supported Plugins screen
	$filter = dpa_supported_plugins_get_filter();
	$view   = dpa_supported_plugins_get_view();

	// Build URL
	$page_url = remove_query_arg( _dpa_supported_plugin_get_queryargs(), $_SERVER['REQUEST_URI'] );
	$page_url = add_query_arg( 'filter', $filter, $page_url );

	// Get supported plugins
	$extensions = array();
	foreach ( (array) achievements()->extensions as $extension ) {
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		$extensions[] = $extension;
	}

	// Sort list of plugins by rating
	//if ( ! empty( $_GET['order'] ) && 'rating' === $_GET['order'] )
	//	uasort( $extensions, create_function( '$a, $b', 'return strnatcasecmp($a->rating, $b->rating);' ) );

	// Sort by plugin status (installed, not installed)
	// @todo Reimplement this for a future release
	//elseif ( ! empty( $_GET['order'] ) && 'status' === $_GET['order'] )
	//	uasort( $extensions, create_function( '$a, $b', 'return strnatcasecmp($a->install_status["status"], $b->install_status["status"]);' ) );

	// Sort alphabetically
	//else
	uasort( $extensions, create_function( '$a, $b', 'return strnatcasecmp($a->get_name(), $b->get_name());' ) );
	$extensions = (object) $extensions;

	// Build URL for non-javascript table sorting
	$redirect_to = remove_query_arg( _dpa_supported_plugin_get_queryargs(), self_admin_url( 'edit.php?post_type=achievement&page=achievements-plugins' ) );
	$redirect_to = add_query_arg( 'filter', $filter, $page_url );
	$redirect_to = add_query_arg( 'view',   'list',  $page_url );
?>

	<table class="widefat">
		<caption class="screen-reader-text"><?php _e( 'This table lists all of the plugins that Achievements has support for. For each plugin it shows a banner, who contributed to its development, and whether your site has the plugin installed or not.', 'dpa' ); ?></caption>
		<thead>
			<tr>
				<th scope="col"></th>
				<th scope="col"><?php _e( 'Plugin', 'dpa' ); ?></th>
				<!-- <th scope="col"><a href="<?php echo esc_attr( add_query_arg( 'order', 'rating', $redirect_to ) ); ?>"><?php _e( 'Rating', 'dpa' ); ?></a></th> -->
				<th scope="col"><a href="<?php echo esc_attr( add_query_arg( 'order', 'status', $redirect_to ) ); ?>"><?php _e( 'Status', 'dpa' ); ?></a></th>
				<th scope="col"><?php _e( 'Authors', 'dpa' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th></th>
				<th><?php _e( 'Plugin', 'dpa' ); ?></th>
				<!-- <th><a href="#a"><?php _e( 'Rating', 'dpa' ); ?></a></th> -->
				<th><a href="#"><?php _e( 'Status', 'dpa' ); ?></a></th>
				<th><?php _e( 'Authors', 'dpa' ); ?></th>
			</tr>
		</tfoot>

		<tbody>

			<?php foreach ( $extensions as $extension ) :
			// Is this plugin installed?
			$is_plugin_installed = _dpa_is_plugin_installed( $extension->get_id() );

			// Construct plugin's <img> tag
			$plugin_class     = sanitize_html_class( $extension->get_id() );
			$plugin_name      = convert_chars( wptexturize( $extension->get_name() ) );
			$plugin_image_url = sprintf( '<img src="%1$s" alt="%2$s" title="%3$s" class="%4$s" />',
				esc_url( $extension->get_small_image_url() ),
				esc_attr( $plugin_name ),
				esc_attr( $plugin_name ),
				esc_attr( $plugin_class )
			);

			// Build URL to the current plugin's detail screen
			$plugin_page_url = add_query_arg( array( 'plugin' => $plugin_class, 'view' => 'detail', ), $page_url );
			?>
				<tr class="<?php echo ( $is_plugin_installed ) ? ' installed' : ' notinstalled'; ?>">
					<td class="plugin">
						<a href="<?php echo esc_url( $plugin_page_url ); ?>"><?php echo $plugin_image_url; ?></a>
					</td>

					<td class="name">
						<?php echo $plugin_name; ?>
					</td>

					<?php /* <!-- <td class="rating">
						<div class="star-holder" title="<?php printf( __( 'Rated %1$s out of 100 by the WordPress.org community', 'dpa' ), number_format_i18n( $plugin->rating ) ); ?>">
							<div class="star star-rating" style="width: <?php echo esc_attr( $plugin->rating ); ?>px"></div>
							<div class="star star5"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '5 stars', 'dpa' ); ?>" /></div>
							<div class="star star4"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '4 stars', 'dpa' ); ?>" /></div>
							<div class="star star3"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '3 stars', 'dpa' ); ?>" /></div>
							<div class="star star2"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '2 stars', 'dpa' ); ?>" /></div>
							<div class="star star1"><img src="<?php echo admin_url( 'images/star.png?v=20120409' ); ?>" alt="<?php esc_attr_e( '1 star',  'dpa' ); ?>" /></div>
						</div>
					</td> --> */ ?>

					<?php
					// Is the plugin installed? Yes.
					if ( $is_plugin_installed ) {
						printf( '<td class="status installed"><span class="installed">%1$s</span></td>', _x( 'Ready', 'A plugin is installed', 'dpa' ) );

					// No, it isn't.
					} else {
						echo '<td class="status notinstalled">';

						// If current user can install plugins, link directly to the plugn install screen
						if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
							printf( '<a class="thickbox" href="%2$s">%1$s</a>',
								_x( 'Not installed', 'A plugin is not installed', 'dpa' ),

								// Build install plugin URL
								admin_url( add_query_arg(
									array(
										'tab'       => 'plugin-information',
										'plugin'    => $extension->get_id(),
										'TB_iframe' => 'true',
										'width'     => '640',
										'height'    => '500'
									),
									'plugin-install.php'
								) )
							);
						
						} else {
							_ex( 'Not installed', 'A plugin is not installed', 'dpa' );
						}

						echo '</td>';
					}
					?>

					<td class="contributors">
						<?php
						$contributors = $extension->get_contributors();
						foreach ( $contributors as $contributor ) {
							$name = convert_chars( wptexturize( $contributor['name'] ) );

							printf( '<a href="%1$s"><img src="%2$s" alt="%3$s" title="%4$s" /></a>',
								esc_attr( esc_url( $contributor['profile_url']  ) ),
								esc_attr( esc_url( add_query_arg( 's', '48', $contributor['gravatar_url'] ) ) ),
								esc_attr( $name ),
								esc_attr( $name )
							);
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
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_grid() {
	// Get current filter of the Supported Plugins screen
	$filter = dpa_supported_plugins_get_filter();

	// Get supported plugins
	$extensions = array();
	foreach ( (array) achievements()->extensions as $extension ) {
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		$extensions[] = $extension;
	}

	uasort( $extensions, create_function( '$a, $b', 'return strnatcasecmp($a->get_name(), $b->get_name());' ) );
	$extensions = (object) $extensions;

	echo '<div class="wrapper"><div>';
	foreach ( $extensions as $extension ) {
		// Build URL to link to Detail view
		$plugin_slug = sanitize_html_class( $extension->get_id() );	
		$page_url    = remove_query_arg( _dpa_supported_plugin_get_queryargs(), $_SERVER['REQUEST_URI'] );
		$page_url    = add_query_arg( array( 'filter' => $filter, 'plugin' => $plugin_slug, 'view' => 'detail', ), $page_url );

		// Record if the plugin is installed by setting the class
		$class = _dpa_is_plugin_installed( $extension->get_id() ) ? ' installed' : ' notinstalled';

		// Output plugin's grid item
		printf( '<a href="%5$s" class="%1$s"><img class="%2$s" src="%3$s" alt="%4$s" title="%4$s" /></a>', 
			esc_attr( $class ),
			esc_attr( sanitize_html_class( $extension->get_id() ) ),
			esc_attr( $extension->get_image_url() ),
			esc_attr( $extension->get_name() ),
			esc_url( $page_url )
		);
	}
	echo '</div></div>';
}

/**
 * The metabox for the "select a plugin" dropdown box on the Supported Plugins grid view.
 *
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_mb_switcher() {
	// Get current plugin
	$selected_plugin = dpa_supported_plugins_get_plugin();

	// Build dropdown box
	echo '<select id="dpa-details-plugins">';

	// Get supported plugins
	$extensions = array();
	foreach ( (array) achievements()->extensions as $extension ) {
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		$extensions[] = $extension;
	}

	uasort( $extensions, create_function( '$a, $b', 'return strnatcasecmp($a->get_name(), $b->get_name());' ) );
	$extensions = (object) $extensions;

	foreach ( $extensions as $extension ) {
		// Extensions must inherit the DPA_Extension class
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		// Record if the plugin is installed by setting the class
		$class       = _dpa_is_plugin_installed( $extension->get_id() ) ? ' installed' : ' notinstalled';
		$plugin_slug = sanitize_html_class( $extension->get_id() );

		// Build option for the plugin
		printf( '<option class="%1$s" data-plugin="%2$s" %3$s>%4$s</option>',
			esc_attr( $class ),
			$plugin_slug,
			selected( $selected_plugin, $plugin_slug ),
			esc_html( convert_chars( wptexturize( $extension->get_name() ) ) )
		);
	}

	echo '</select>';
}

/**
 * The Supported Features metabox lists details of the actions that this extension provides support for.
 *
 * @since Achievements (3.0)
 */
function dpa_supported_features_mb_info() {
	// Get current plugin
	$plugin = dpa_supported_plugins_get_plugin();

	// Get supported plugins
	$extensions = achievements()->extensions;

	foreach ( $extensions as $extension ) :
		// Extensions must inherit the DPA_Extension class
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		// Only show details for the current $plugin
		if ( $plugin !== $extension->get_id() )
			continue;
?>

		<dl>
			<?php foreach ( $extension->get_actions() as $action_name => $action_description ) : ?>
				<dt><?php echo convert_chars( wptexturize( $action_name ) ); ?></dt>
				<dd><?php echo convert_chars( wptexturize( $action_description ) ); ?></dd>
			<?php endforeach; ?>
		</dl>

<?php
	break;
	endforeach;
}

/**
 * The metabox for the "plugin info" dropdown box on the Supported Plugins Detail view.
 * Shows: installed/not install status, contributors, description, wporg link
 *
 * @since Achievements (3.0)
 */
function dpa_supported_plugins_mb_info() {
	// Get current plugin
	$plugin = dpa_supported_plugins_get_plugin();

	// Get supported plugins
	$extensions = achievements()->extensions;

	foreach ( $extensions as $extension ) :
		// Extensions must inherit the DPA_Extension class
		if ( ! is_a( $extension, 'DPA_Extension' ) )
			continue;

		// Only show details for the current $plugin
		if ( $plugin !== $extension->get_id() )
			continue;

		// Is this plugin installed?
		$is_plugin_installed = _dpa_is_plugin_installed( $extension->get_id() );
?>

	<p><?php echo convert_chars( wptexturize( $extension->get_description() ) ); ?></p>
	<ul>
		<li>
			<?php
			// Is plugin installed?
			if ( $is_plugin_installed ) {
				_ex( 'Status: Ready', 'A plugin is installed', 'dpa' );

			// It's not installed
			} else {

				// If current user can install plugins, link directly to the install screen
				if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
					printf( '<a class="thickbox" href="%2$s">%1$s</a>',
						_x( 'Install Plugin', 'A plugin is not installed', 'dpa' ),

						// Build install plugin URL
						admin_url( add_query_arg(
							array(
								'height'    => '500',
								'plugin'    => $extension->get_id(),
								'tab'       => 'plugin-information',
								'TB_iframe' => 'true',
								'width'     => '640',
							),
							'plugin-install.php'
						) )
					);

				} else {
					_ex( 'Status: Not installed', 'A plugin is not installed', 'dpa' );
				}
			}
			?>
		</li>

		<li class="links"><?php printf( '<a href="%1$s" target="_new">%2$s</a>', esc_url( $extension->get_wporg_url() ), __( 'More Info', 'dpa' ) ); ?></li>

		<li class="authors">
			<?php
			$contributors = $extension->get_contributors();
			foreach ( $contributors as $contributor ) {
				$name = convert_chars( wptexturize( $contributor['name'] ) );

				printf( '<a href="%1$s"><img src="%2$s" alt="%3$s" title="%4$s" /></a>',
					esc_attr( esc_url( $contributor['profile_url']  ) ),
					esc_attr( esc_url( add_query_arg( 's', '48', $contributor['gravatar_url'] ) ) ),
					esc_attr( $name ),
					esc_attr( $name )
				);
			}
			?>
		</li>
	</ul>

	<?php
	break;
	endforeach;
}

/**
 * Return the list of $_GET variable names that are used across the Supported Plugins screens.
 *
 * @access private
 * @return array
 * @since Achievements (3.0)
 */
function _dpa_supported_plugin_get_queryargs() {
	return array( 'filter', 'plugin', 'view', );
}

/**
 * Is the specified plugin installed?
 *
 * @access private
 * @param string $plugin Plugin directory slug
 * @return bool
 * @since Achievements (3.0)
 */
function _dpa_is_plugin_installed( $plugin ) {
	// Special case for the WordPress extension
	if ( 'wordpress' === $plugin )
			return true;

	// Can we find the plugin directory?
	$plugin = get_plugins( "/{$plugin}" );

	return ! empty( $plugin );
}