<?php

/**
 * Functions of Achievements' theme compatibility layer
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DPA_Default' ) ) :

/**
 * Loads Achievements' theme compatibility functions
 *
 * This is not a real theme by WordPress standards and is instead used as the
 * fallback for any WordPress theme that does not have Achievements templates in it.
 *
 * To make your custom theme Achievements compatible and to customise the templates,
 * you can copy these files into your theme without needing to merge anything
 * together; we'll safely handle the rest.
 *
 * See @link DPA_Theme_Compat() for more.
 *
 * @since Achievements (3.0)
 */
class DPA_Default extends DPA_Theme_Compat {

	/**
	 * Constructor
	 *
	 * @since Achievements (3.0)
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
		$this->setup_filters();
	}

	/**
	 * Sets up global variables
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_globals() {
		$this->id      = 'default';
		$this->name    = _x( 'v3.0 - 3.5.1', 'Version number ranges for this plugin', 'achievements' );
		$this->version = dpa_get_version();
		$this->dir     = trailingslashit( achievements()->plugin_dir . 'templates' );
		$this->url     = trailingslashit( achievements()->plugin_url . 'templates' );
	}

	/**
	 * Hook this theme compatibility template pack into various actions
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_actions() {
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'dpa_footer',          array( $this, 'print_notification_templates' ) );

		// Deprecated notifications functionality (3.5)
		if ( dpa_deprecated_notification_template_exists() ) {
			add_action( 'dpa_enqueue_scripts', 'dpa_deprecated_enqueue_notification_styles_and_scripts' );
			add_action( 'dpa_footer',          'dpa_deprecated_print_notifications'                     );
		}

		do_action_ref_array( 'dpa_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Hook this theme compatibility template pack into various filters
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_filters() {
		add_filter( 'heartbeat_received',  array( __CLASS__, 'notifications_heartbeat_response' ), 10, 2 );

		do_action_ref_array( 'dpa_theme_compat_filters', array( &$this ) );
	}

	/**
	 * Load the theme CSS
	 *
	 * @since Achievements (3.0)
	 */
	public function enqueue_styles() {
		$rtl  = is_rtl() ? '-rtl' : '';
		$file = "css/achievements{$rtl}.css";

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'dpa-child-achievements';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'dpa-parent-achievements';

		// Achievements theme compatibility
		} else {
			$location = trailingslashit( dpa_get_theme_compat_url() );
			$handle   = 'dpa-default-achievements';
		}

		// Enqueue the stylesheet
		wp_enqueue_style( $handle, $location . $file, array(), dpa_get_theme_compat_version(), 'screen' );
	}

	/**
	 * Enqueue the required Javascript files
	 *
	 * @since Achievements (3.0)
	 */
	public function enqueue_scripts() {

		// If user's not active or is inside the WordPress Admin, bail out.
		if ( ! dpa_is_user_active() || is_admin() || is_404() || is_preview() )
			return;

		// Only load heartbeat JS if not using the 3.5-deprecated notifications
		if ( dpa_deprecated_notification_template_exists() )
			return;

		$file = 'js/achievements.js';

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'dpa-child-javascript';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'dpa-parent-javascript';

		// Achievements theme compatibility
		} else {
			$location = trailingslashit( dpa_get_theme_compat_url() );
			$handle   = 'dpa-default-javascript';
		}

		wp_enqueue_script( $handle, $location . $file, array( 'heartbeat', 'underscore', 'wp-util' ), dpa_get_theme_compat_version(), 'screen', true );
	}

	/**
	 * The PHP side of Achievements' live notifications system using WordPress 3.6's heartbeat API; we grab the image,
	 * post ID, permalink, and title of all achievements that have recently been unlocked, and send that back using
	 * WordPress' heartbeat_recieved filter.
	 *
	 * The heartbeat JS makes periodic AJAX connections back to WordPress. WordPress sees those requests, and fires the
	 * heartbeat_recieved filter. The filter allows plugins to change the server's response before it's sent back to
	 * the originating user's browser.
	 *
	 * @param array $response The data we want to send back to user whose heart beat.
	 * @param array $data An array of $_POST data received from the originating AJAX request.
	 * @return array The data we want to send back to user.
	 * @since Achievements (3.5)
	 */
	public static function notifications_heartbeat_response( $response, $data ) {
		// Bail if user is not active, or $data isn't in the expected format
		if ( ! dpa_is_user_active() || ! isset( $data['achievements'] ) || ! is_array( $data['achievements'] ) )
			return $response;

		$ids = array_keys( dpa_get_user_notifications() );
		if ( empty( $ids ) )
			return $response;

		// If multisite and running network-wide, switch_to_blog to the data store site
		if ( is_multisite() && dpa_is_running_networkwide() )
			switch_to_blog( DPA_DATA_STORE );

		$achievements = dpa_get_achievements( array(
			'no_found_rows' => true,
			'nopaging'      => true,
			'post__in'      => $ids,
			'post_status'   => 'any',
		) );

		$new_response = array();
		foreach ( $achievements as $achievement ) {
			/**
			 * Check that the post status is published or privately published. We need to check this here to work
			 * around WP_Query not constructing the query correctly with private post statuses.
			 */
			if ( ! in_array( $achievement->post_status, array( 'publish', 'private' ) ) )
				continue;

			$item              = array();
			$item['ID']        = $achievement->ID;
			$item['title']     = esc_html( apply_filters( 'dpa_get_achievement_title', $achievement->post_title, $achievement->ID ) );
			$item['permalink'] = esc_url_raw( home_url( '/?p=' . $achievement->ID ) );

			// Thumbnail is optional and may not be set
			$thumbnail = get_post_thumbnail_id( $achievement->ID );
			if ( ! empty( $thumbnail ) ) {
		
				$thumbnail = wp_get_attachment_image_src( $thumbnail, 'medium' );
				if ( $thumbnail ) {
					$item['image_url']   = esc_url_raw( $thumbnail[0] );
					$item['image_width'] = (int) $thumbnail[1];
				}
			}

			// Achievements 3.5+ supports showing multiple unlock notifications at the same time
			$new_response[] = $item;
		}

		// If multisite and running network-wide, undo the switch_to_blog
		if ( is_multisite() && dpa_is_running_networkwide() )
			restore_current_blog();

		// Clear all pending notifications
		dpa_update_user_notifications();

		$new_response = array_merge( $response, array( 'achievements' => $new_response ) );
		return apply_filters( 'dpa_theme_compat_notifications_heartbeat_response', $new_response, $ids, $response, $data );
	}

	/**
	 * Output the notification JS templates.
	 * 
	 * These will be used with underscore.js' _.template() method. It compiles these JS templates into functions
	 * that can be evaluated for rendering. Useful for rendering complicated bits of HTML from JSON data sources,
	 * which is exactly what we're going to do.
	 *
	 * @since Achievements (3.5)
	 */
	public static function print_notification_templates() {

		// If user's not active or is inside the WordPress Admin, bail out.
		if ( ! dpa_is_user_active() || is_admin() || is_404() )
			return;

		echo achievements()->shortcodes->display_notifications_template();
	}
}  // class_exists
endif;

/**
 * Does the old-style notification template file exist in the current theme?
 *
 * The template was removed in version 3.5 and was replaced with the heartbeat-powered "live notifications" system.
 * This function has no filters on purpose because it's meant to only run once, as quickly as possible.
 *
 * @return bool
 * @since Achievements (3.5)
 */
function dpa_deprecated_notification_template_exists() {
	static $retval = null;

	if ( $retval !== null )
		return $retval;

	$template_locations = dpa_add_template_locations( array( '/feedback-achievement-unlocked.php' ) );
	$retval             = false;

	foreach ( $template_locations as $template_name ) {

		// Try to find the deprecated template. Check child theme first.
		if ( file_exists( get_stylesheet_directory() . "/$template_name" ) ) {
			$retval = true;
			break;

		// Check parent theme last.
		} elseif ( file_exists( get_template_directory() . "/$template_name" ) ) {
			$retval = true;
			break;
		}
	}

	return $retval;
}

/**
 * Backwards compatibility with pre-3.5; enqueue the old-style CSS/JS for notifications.
 *
 * These scripts were removed from the default theme compatiility pack in version 3.5 with
 * the introduction of the heartbeat-powered "live notifications" system.
 *
 * @since Achievements (3.5)
 */
function dpa_deprecated_enqueue_notification_styles_and_scripts() {

	// If user's not active or is inside the WordPress Admin, bail out.
	if ( ! dpa_is_user_active() || is_admin() || is_404() )
		return;

	/**
	 * CSS
	 */
	$rtl  = is_rtl() ? '-rtl' : '';
	$file = "css/notifications{$rtl}.css";

	// Check child theme
	if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
		$location = trailingslashit( get_stylesheet_directory_uri() );

	// Check parent theme
	} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
		$location = trailingslashit( get_template_directory_uri() );

	// Achievements theme compatibility
	} else {
		$location = trailingslashit( dpa_get_theme_compat_url() );
	}

	wp_enqueue_style( 'dpa-default-notifications', $location . $file, array(), dpa_get_theme_compat_version(), 'screen' );


	/**
	 * JS
	 */
	$file = "js/notifications.js";

	// Check child theme
	if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
		$location = trailingslashit( get_stylesheet_directory_uri() );

	// Check parent theme
	} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
		$location = trailingslashit( get_template_directory_uri() );

	// Achievements theme compatibility
	} else {
		$location = trailingslashit( dpa_get_theme_compat_url() );
	}

	wp_enqueue_style( 'dpa-default-notifications-javascript', $location . $file, array( 'jquery' ), dpa_get_theme_compat_version(), true );
}

/**
 * Backwards compatibility with pre-3.5: print the old-style notifications for the current user to the page footer.
 * 
 * Notifications were overhauled in version 3.5 and were replaced with the heartbeat-powered "live notifications" system.
 * This function used to be called "dpa_print_notifications".
 *
 * @deprecated Achievements (3.5)
 * @since Achievements (3.0)
 */
function dpa_deprecated_print_notifications() {

	// If user's not active or is inside the WordPress Admin, bail out.
	if ( ! dpa_is_user_active() || is_admin() || is_404() || ! dpa_user_has_notifications() )
		return;

	// Get current notifications
	$notifications = dpa_get_user_notifications();
	if ( empty( $notifications ) )
		return;

	echo achievements()->shortcodes->display_feedback_achievement_unlocked();
}
