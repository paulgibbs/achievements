<?php
/**
 * Main Achievements Admin Class
 *
 * @package Achievements
 * @subpackage Admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DPA_Admin' ) ) :
/**
 * Loads Achievements admin area thing
 *
 * @since 3.0
 */
class DPA_Admin {
	// Paths

	/**
	 * Path to the Achievements admin directory
	 */
	public $admin_dir = '';

	/**
	 * URL to the Achievements admin directory
	 */
	public $admin_url = '';

	/**
	 * URL to the Achievements image directory
	 */
	public $images_url = '';


	/**
	 * The main Achievements admin loader
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Set up the admin hooks, actions and filters
	 *
	 * @since 3.0
	 */
	private function setup_actions() {
		// General Actions

		// Add menu item to settings menu
		add_action( 'dpa_admin_menu',              array( $this, 'admin_menus'             ) );

		// Add some general styling to the admin area
		//add_action( 'dpa_admin_head',              array( $this, 'admin_head'              ) );

		// Add notice if not using an Achievements theme
		//add_action( 'dpa_admin_notices',           array( $this, 'activation_notice'       ) );

		// Add settings
		add_action( 'dpa_register_admin_settings', array( $this, 'register_admin_settings' ) );

		// Add menu item to settings menu
		//add_action( 'dpa_activation',              array( $this, 'new_install'             ) );


		// Filters

		// Add link to settings page
		add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );


		// Network Admin

		// Add menu item to settings menu
		//add_action( 'network_admin_menu',  array( $this, 'network_admin_menus' ) );


		// Dependencies

		// Allow plugins to modify these actions
		do_action_ref_array( 'dpa_admin_loaded', array( &$this ) );
	}

	/**
	 * Include required files
	 *
	 * @since 3.0
	 */
	private function includes() {
		require( $this->admin_dir . 'admin-functions.php'   );
		require( $this->admin_dir . 'supported-plugins.php' );  // Supported plugins screen
	}

	/**
	 * Set admin globals
	 *
	 * @since 3.0
	 */
	private function setup_globals() {
		$this->admin_dir  = trailingslashit( achievements()->plugin_dir . 'admin'  ); // Admin path
		$this->admin_url  = trailingslashit( achievements()->plugin_url . 'admin'  ); // Admin URL
		$this->images_url = trailingslashit( $this->admin_url           . 'images' ); // Admin images URL
	}

	/**
	 * Add wp-admin menus
	 *
	 * @since 3.0
	 */
	public function admin_menus() {
		// "Supported Plugins" menu
		$hook = add_submenu_page(
			'edit.php?post_type=achievement',
			__( 'Achievements &mdash; Supported Plugins', 'dpa' ),
			__( 'Supported Plugins', 'dpa' ),
			achievements()->minimum_capability,
			'achievements-plugins',
			'dpa_supported_plugins'
		);

		// Hook into early actions to register custom CSS and JS
		add_action( "admin_print_styles-$hook",  array( $this, 'enqueue_styles'  ) );
		add_action( "admin_print_scripts-$hook", array( $this, 'enqueue_scripts' ) );

		// Hook into early actions to register contextual help and screen options
		add_action( "load-$hook",                array( $this, 'screen_options'  ) );
	}

	/**
	 * Hook into early actions to register contextual help and screen options
	 *
	 * @since 3.0
	 */
	public function screen_options() {
		// Only load up styles if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		// "Supported Plugins" screen
		if ( 'achievements-plugins' == $_GET['page'] )
			dpa_supported_plugins_on_load();
	}

	/**
	 * Enqueue CSS for our custom admin screens
	 *
	 * @since 3.0
	 */
	public function enqueue_styles() {
		// Only load up styles if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		// "Supported Plugins" screen
		if ( 'achievements-plugins' == $_GET['page'] )
			wp_enqueue_style( 'dpa_admin_css', trailingslashit( achievements()->plugin_url ) . 'css/supportedplugins.css', array(), '20120209' );
	}

	/**
	 * Enqueue JS for our custom admin screens
	 *
	 * @since 3.0
	 */
	public function enqueue_scripts() {
		// Only load up scripts if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		// "Supported Plugins" screen
		if ( 'achievements-plugins' == $_GET['page'] ) {
			wp_enqueue_script( 'dpa_socialite',   trailingslashit( achievements()->plugin_url ) . 'js/socialite-min.js',          array(), '20120413', true );
			wp_enqueue_script( 'tablesorter_js',  trailingslashit( achievements()->plugin_url ) . 'js/jquery-tablesorter-min.js', array( 'jquery' ), '20120413', true );
			wp_enqueue_script( 'dpa_sp_admin_js', trailingslashit( achievements()->plugin_url ) . 'js/supportedplugins-min.js',   array( 'jquery', 'dpa_socialite', 'dashboard', 'postbox' ), '20120413', true );

			// Add thickbox for the 'not installed' links on the List view
			add_thickbox();
		}
	}

	/**
	 * Register the settings
	 *
	 * @since 3.0
	 */
	public static function register_admin_settings() {
		// Only do stuff if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		// Fire an action for Achievements plugins to register their custom settings
		do_action( 'dpa_register_admin_settings' );
	}

	/**
	 * Admin area activation notice
	 *
	 * Shows a nag message in admin area about the theme not supporting Achievements
	 *
	 * @since 3.0
	 */
	public function activation_notice() {
		// @todo - something fun
	}

	/**
	 * Add Settings link to plugins area
	 *
	 * @param array $links Links array in which we would prepend our link
	 * @param string $file Current plugin basename
	 * @return array Processed links
	 * @since 3.0
	 */
	public static function add_settings_link( $links, $file ) {
		if ( plugin_basename( achievements()->file ) == $file ) {
			$settings_link = '<a href="' . esc_attr( admin_url( 'options-general.php?page=achievements' ) ) . '">' . __( 'Settings', 'dpa' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Is the current screen part of Achievements? e.g. a post type screen.
	 *
	 * @return bool True if this is an Achievements admin screen
	 * @since 3.0
	 */
	public static function is_admin_screen() {
		$result = false;

		if ( ! empty( $_GET['post_type'] ) && 'achievement' == $_GET['post_type'] )
			$result = true;

		return true;
	}
}
endif; // class_exists check

/**
 * Set up Achievements' Admin
 *
 * @since 3.0
 */
function dpa_admin() {
	achievements()->admin = new DPA_Admin();
}