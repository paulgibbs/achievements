<?php
/**
 * Batman begins
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage loader
 *
 * Plugin structure is based on bbPress and BuddyPress, because they're awesome. Borrowed with love.
 */

/*
Plugin Name: Achievements
Plugin URI: http://achievementsapp.wordpress.com/
Description: Achievements gives your BuddyPress community fresh impetus by promoting and rewarding social interaction with challenges, badges and points.
Version: 3
Requires at least: WP 3.3, BuddyPress 1.6
Tested up to: WP 3.3, BuddyPress 1.6
License: General Public License version 3
Author: Paul Gibbs
Author URI: http://byotos.com/
Network: true
Domain Path: /languages/
Text Domain: dpa

"Achievements"
Copyright (C) 2009-12 Paul Gibbs

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Achievements class
 *
 * Note to plugin and theme authors:
 * Do not directly reference these class properties in your code. They are subject
 * to change at any time. Most of them have reference functions in the includes.
 *
 * @since 3.0
 */
class achievements {
	// Versions

	/**
	 * Achievements version
	 */
	public $version = 3.0;

	/**
	 * Achievements DB version
	 */
	public $db_version = 300;


	// Post types

	/**
	 * Achievement post type ID
	 */
	public $achievement_post_type = '';


	// Taxonomies

	/**
	 * Action taxonomy ID
	 */
	public $action_tax_id = '';


	/**
	 * Theme to use for theme compatibility
	 */
	public $theme_compat = '';


	// Paths

	/**
	 * Basename of this plugin's directory
	 */
	public $basename = '';

	/**
	 * The full path and filename of this file (achievements.php)
	 */
	public $file = '';

	/**
	 * Absolute path to this plugin's directory
	 */
	public $plugin_dir = '';

	/**
	 * Absolute path to this plugin's themes directory
	 */
	public $themes_dir = '';

	/**
	 * Absolute path to this plugin's language directory
	 */
	public $lang_dir = '';


	// URLs

	/**
	 * URL to this plugin's directory
	 */
	public $plugin_url = '';

	/**
	 * URL to this plugin's themes directory
	 */
	public $themes_url = '';


	// Users

	/**
	 * Current user
	 *
	 * @var WP_User
	 */
	public $current_user = null;


	// Errors

	/**
	 * Used to log and display errors
	 *
	 * @var WP_Error
	 */
	public $errors = null;

	/**
	 * Options (overrides values from get_option)
	 */
	public $options = array();


	/**
	 * Constructor. Gets the ball rolling.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Set up global variables
	 *
	 * @since 3.0
	 */
	private function setup_globals() {
		// Achievements root directory
		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url ( $this->file );

		// Themes
		$this->themes_dir = $this->plugin_dir . 'themes';
		$this->themes_url = $this->plugin_url . 'themes';

		// Languages
		$this->lang_dir = $this->plugin_dir . 'languages';

		// Post type/taxonomy identifiers
		$this->achievement_post_type = apply_filters( 'dpa_achievement_post_type', 'dpa_achievements' );
		$this->action_tax_id         = apply_filters( 'dpa_action_tax_id', 'dp_actions' );

		// Errors
		$this->errors = new WP_Error();

		// Add to global cache groups
		wp_cache_add_global_groups( 'achievements' );
	}

	/**
	 * Include required files
	 *
	 * @since 3.0
	 */
	private function includes() {
		// Core
		require( $this->plugin_dir . 'includes/dpa-core-hooks.php'   );  // All filters and actions
		require( $this->plugin_dir . 'includes/dpa-core-options.php' );  // Configuration Options
		require( $this->plugin_dir . 'includes/dpa-core-caps.php'    );  // Roles and capabilities
		require( $this->plugin_dir . 'includes/dpa-core-classes.php' );  // Common classes
		require( $this->plugin_dir . 'includes/dpa-core-update.php'  );  // Database updater

		// Components
		require( $this->plugin_dir . 'includes/dpa-common-functions.php' ); // Common functions

		// Admin
		if ( is_admin() )
			require( $this->plugin_dir . 'admin/dpa-admin.php' );
	}

	/**
	 * Set up the default hooks and actions
	 *
	 * @since 3.0
	 */
	private function setup_actions() {
		// Plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, 'dpa_activation'   );
		add_action( 'deactivate_' . $this->basename, 'dpa_deactivation' );

		// If Achievements is being deactivated, don't add any more actions
		if ( dpa_is_deactivation( $this->basename ) )
			return;

		// Add the core actions
		$actions = array(
			'load_textdomain',      // Load textdomain
			'register_post_types',  // Register post types (dpa_achievements)
			'register_taxonomies',  // Register taxonomies (dpa_actions)
			'setup_current_user',   // Set up currently logged in user
		);

		foreach( $actions as $class_action )
			add_action( 'dpa_' . $class_action, array( $this, $class_action ), 5 );
	}

	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the plugin first, and then the WordPress languages folder.
	 *
	 * If you're creating custom translation files, use the WordPress language folder.
	 *
	 * @since 3.0
	 */
	public function load_textdomain() {
		// Allow locale to be filtered
		$locale = apply_filters( 'dpa_locale', get_locale() );

		// Get mo file name
		$mofile = sprintf( 'dpa-%s.mo', $locale );

		// Set up paths to current locale file
		$mofile_local  = $this->lang_dir . '/' . $mofile;
		$mofile_global = WP_LANG_DIR . '/achievements/' . $mofile;

		// Look in /wp-content/plugins/achievmeents/languages/
		if ( file_exists( $mofile_local ) )
			return load_textdomain( 'dpa', $mofile_local );

		// Look in /wp-content/languages/
		elseif ( file_exists( $mofile_global ) )
			return load_textdomain( 'dpa', $mofile_global );
	}

	/**
	 * Set up the post types for: achievement
	 *
	 * @since 3.0
	 */
	public function register_post_types() {
		$achievement = $cpt = array();

		// CPT labels
		$achievement['labels'] = array(
			'add_new'            => _x( 'Add New', 'achievement',          'dpa' ),
			'add_new_item'       => __( 'Add New Achievement',             'dpa' ),
			'all_items'          => __( 'All Achievements',                'dpa' ),
			'edit'               => _x( 'Edit',    'achievement',          'dpa' ),
			'edit_item'          => __( 'Edit Achievement',                'dpa' ),
			'menu_name'          => __( 'Achievements',                    'dpa' ),
			'name'               => __( 'Achievements',                    'dpa' ),
			'new_item'           => __( 'New Achievement',                 'dpa' ),
			'not_found'          => __( 'No achievements found.',          'dpa' ),
			'not_found_in_trash' => __( 'No achievements found in Trash.', 'dpa' ),
			'search_items'       => __( 'Search Achievements',             'dpa' ),
			'singular_name'      => __( 'Achievement',                     'dpa' ),
			'view'               => __( 'View Achievement',                'dpa' ),
			'view_item'          => __( 'View Achievement',                'dpa' ),
		);

		// CPT rewrite
		/*$achievement['rewrite'] = array(
			'slug'       => $this->achievement_slug,
			'with_front' => false,
		);*/

		// CPT supports
		$achievement['supports'] = array(
			'editor',
			'revisions',
			'thumbnail',
			'title',
		);

		// CPT filter
		$cpt['achievement'] = apply_filters( 'dpa_register_post_types_achievement', array(
			'can_export'          => true,
			'capabilities'        => dpa_get_achievement_caps(),
			'capability_type'     => array( 'achievement', 'achievements' ),
			'description'         => _x( 'Achievements types (e.g. new post, new site, new user)', 'Achievement post type description', 'dpa' ),
			'exclude_from_search' => true,
			//'has_archive'         => $this->root_slug,
			'hierarchical'        => true,
			'labels'              => $achievement['labels'],
			'public'              => true,
			'query_var'           => true,
			//'rewrite'             => $achievement['rewrite'],
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'supports'            => $achievement['supports'],
		) );

		// Register Achievement post type
		register_post_type( $this->achievement_post_type, $cpt['achievement'] );
	}

	/**
	 * Register the topic tag taxonomy
	 *
	 * @since 3.0
	 */
	public function register_taxonomies() {
		$action = array();

		// Action tax labels
		$action['labels'] = array(
			'add_new_item'  => __( 'Add New Event',                         'dpa' ),
			'all_items'     => __( 'All',                                   'dpa' ),
			'edit_item'     => __( 'Edit Event',                            'dpa' ),
			'name'          => _x( 'Events', 'event taxonomy general name', 'dpa' ),
			'new_item_name' => __( 'New Event Name',                        'dpa' ),
			'popular_items' => __( 'Popular Events',                        'dpa' ),
			'search_items'  => __( 'Search Events',                         'dpa' ),
			'singular_name' => _x( 'Event', 'event taxonomy singular name', 'dpa' ),
			'update_item'   => __( 'Update Event',                          'dpa' ),
			'view_item'     => __( 'View Event',                            'dpa' ),
		);

		// Action filter
		$action_tax = apply_filters( 'dpa_register_taxonomies_action', array(
			'capabilities'          => dpa_get_action_caps(),
			'hierarchical'          => false,
			'labels'                => $action['labels'],
			'public'                => false,
			'show_tagcloud'         => true,
			'show_ui'               => true,
			'update_count_callback' => '_update_post_term_count',
		) );

		// Register the achievement action taxonomy
		register_taxonomy(
			$this->action_tax_id,          // The topic tag id
			$this->achievement_post_type,  // The topic post type
			$action_tax
		);
	}

	/**
	 * Set up the currently logged in user.
	 *
	 * Do not to call this before the 'init' action has started.
	 *
	 * @since 3.0
	 */
	public function setup_current_user() {
		$this->current_user = &wp_get_current_user();
	}
}

$GLOBALS['achievements'] = new achievements();
?>