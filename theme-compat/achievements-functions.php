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
 * @since 3.0
 */
class DPA_Default extends DPA_Theme_Compat {

	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Sets up global variables
	 *
	 * @since 3.0
	 */
	private function setup_globals() {
		$this->id      = 'default';
		$this->name    = __( 'Achievements Default', 'dpa' );
		$this->version = dpa_get_version();
		$this->dir     = trailingslashit( achievements()->plugin_dir . 'theme-compat' );
		$this->url     = trailingslashit( achievements()->plugin_url . 'theme-compat' );
	}

	/**
	 * Sets up the theme hooks
	 *
	 * @since 3.0
	 */
	private function setup_actions() {
		// Scripts
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_styles'  ) ); // Enqueue theme CSS
		add_action( 'dpa_enqueue_scripts', array( $this, 'enqueue_scripts' ) ); // Enqueue theme JS

		// Override
		do_action_ref_array( 'dpa_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Load the theme CSS
	 *
	 * @since 3.0
	 * @todo LTR CSS
	 */
	public function enqueue_styles() {
		$file = 'css/achievements.css';

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
			$location = trailingslashit( $this->url );
			$handle   = 'dpa-default-achievements';
		}

		// Enqueue the stylesheet
		wp_enqueue_style( $handle, $location . $file, array(), $this->version, 'screen' );
	}

	/**
	 * Enqueue the required Javascript files
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		$notifications = dpa_get_user_notifications();

		// Only include noty JS if current user has notifications
		if ( is_user_logged_in() && ! empty( $notifications ) ) {
			wp_enqueue_script( 'noty',       $this->url . 'js/jquery.noty-min.js',        array( 'jquery' ), '2.0.3', true );
			wp_enqueue_script( 'noty-theme', $this->url . 'js/jquery.noty.theme-min.js',  array( 'jquery' ), '2.0.3', true );
		}

		// Include core JS
		wp_enqueue_script( 'achievements-js', $this->url . 'js/achievements-min.js', array( 'jquery' ), $this->version, true );
	}
}  // class_exists

new DPA_Default();
endif;
