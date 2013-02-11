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
 * To make your custom theme Achievements compatible and customise the templates,
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
	}

	/**
	 * Sets up global variables
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_globals() {
		$this->id      = 'default';
		$this->name    = __( 'Achievements Default', 'dpa' );
		$this->version = dpa_get_version();
		$this->dir     = trailingslashit( achievements()->plugin_dir . 'templates' );
		$this->url     = trailingslashit( achievements()->plugin_url . 'templates' );
	}

	/**
	 * Sets up the theme hooks
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_actions() {
		// Template pack
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_styles'  ) );
		//add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Notifications
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_notifications_style'  ) );
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_notifications_script' ) );

		do_action_ref_array( 'dpa_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Load the theme CSS
	 *
	 * @since Achievements (3.0)
	 * @todo LTR CSS
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
	 * Load the CSS for notifications
	 *
	 * @param bool $skip_notifications_check Optional (false). If true, always enqueue styles.
	 * @since Achievements (3.0)
	 * @todo LTR CSS
	 */
	public function enqueue_notifications_style( $skip_notifications_check = false ) {

		// If user's not active or is inside the WordPress Admin, bail out.
		if ( ! dpa_is_user_active() || is_admin() || is_404() || ( ! $skip_notifications_check && ! dpa_user_has_notifications() ) )
			return;

		$rtl  = is_rtl() ? '-rtl' : '';
		$file = "css/notifications{$rtl}.css";

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'dpa-child-notifications';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'dpa-parent-notifications';

		// Achievements theme compatibility
		} else {
			$location = trailingslashit( dpa_get_theme_compat_url() );
			$handle   = 'dpa-default-notifications';
		}

		// Enqueue the stylesheet
		wp_enqueue_style( $handle, $location . $file, array(), dpa_get_theme_compat_version(), 'screen' );
	}

	/**
	 * Load the JS for notifications
	 *
	 * @param bool $skip_notifications_check Optional (false). If true, always enqueue styles.
	 * @since Achievements (3.1)
	 */
	public function enqueue_notifications_script( $skip_notifications_check = false ) {

		// If user's not active or is inside the WordPress Admin, bail out.
		if ( ! dpa_is_user_active() || is_admin() || is_404() || ( ! $skip_notifications_check && ! dpa_user_has_notifications() ) )
			return;

		$file = 'js/notifications.js';

		// Check child theme
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file ) ) {
			$location = trailingslashit( get_stylesheet_directory_uri() );
			$handle   = 'dpa-child-notifications-javascript';

		// Check parent theme
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $file ) ) {
			$location = trailingslashit( get_template_directory_uri() );
			$handle   = 'dpa-parent-notifications-javascript';

		// Achievements theme compatibility
		} else {
			$location = trailingslashit( dpa_get_theme_compat_url() );
			$handle   = 'dpa-default-notifications-javascript';
		}

		// Enqueue the stylesheet
		wp_enqueue_script( $handle, $location . $file, array( 'jquery' ), dpa_get_theme_compat_version(), 'screen', true );
	}

	/**
	 * Enqueue the required Javascript files
	 *
	 * @since Achievements (3.0)
	 */
	public function enqueue_scripts() {

		// Only load on Achievements pages
		if ( ! is_achievements() )
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

		// Enqueue the stylesheet
		wp_enqueue_script( $handle, $location . $file, array( 'jquery' ), dpa_get_theme_compat_version(), 'screen', true );
	}
}  // class_exists
endif;
