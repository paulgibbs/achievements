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

if ( ! class_exists( 'Achievements' ) ) :
/**
 * Main Achievements class
 *
 * Note to plugin and theme authors:
 * Do not directly reference these class properties in your code. They are subject
 * to change at any time. Most of them have reference functions in the includes.
 *
 * @since 3.0
 */
class Achievements {
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

	/**
	 * Achievement Progress post type
	 */
	public $achievement_progress_post_type = '';


	// Taxonomies

	/**
	 * EVent taxonomy ID
	 */
	public $event_tax_id = '';


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


	// Queries

	/**
	 * @var WP_Query For achievements
	 */
	public $achievement_query = null;


	// Errors

	/**
	 * Used to log and display errors
	 *
	 * @var WP_Error
	 */
	public $errors = null;

	/**
	 * Options (overrides values from get_option)
	 * @var array
	 */
	public $options = array();


	// Singleton

	/**
	 * @var Achievements The one true Achievements
	 */
	private static $instance;

	/**
	 * Main Achievements instance
	 *
	 * Insures that only one instance of Achievements exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @return Achievements The one true Achievements
	 * @see achievements()
	 * @since 3.0
	 * @staticvar Achievements $instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Achievements;
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	// Magic Methods

	/**
	 * A dummy constructor to prevent Achievements from being loaded more than once.
	 *
	 * @since 3.0
	 */
	private function __construct() {}

	/**
	 * A dummy magic method to prevent Achievements from being cloned
	 *
	 * @since 3.0
	 */
	public function __clone() { wp_die( __( 'Cheatin&#8217; huh?', 'dpa' ) ); }

	/**
	 * A dummy magic method to prevent Achievements from being unserialised
	 *
	 * @since 3.0
	 */
	public function __wakeup() { wp_die( __( 'Cheatin&#8217; huh?', 'dpa' ) ); }

	/**
	 * Set up global variables
	 *
	 * @since 3.0
	 */
	private function setup_globals() {
		// If multisite and running network-wide, we switch_to_blog to this blog to store/fetch achievement data
		if ( ! defined( 'DPA_DATA_STORE' ) )
			define( 'DPA_DATA_STORE', 1 );

		// Achievements root directory
		$this->file       = __FILE__;
		$this->basename   = 'achievements/achievements.php';  //plugin_basename( $this->file );  @todo Doesn't work in environments with symlink folder
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url ( $this->file );

		// Themes
		$this->themes_dir = $this->plugin_dir . 'themes';
		$this->themes_url = $this->plugin_url . 'themes';

		// Languages
		$this->lang_dir = $this->plugin_dir . 'languages';

		// Post type/taxonomy identifiers
		$this->achievement_post_type          = apply_filters( 'dpa_achievement_post_type',          'dpa_achievement' );
		$this->achievement_progress_post_type = apply_filters( 'dpa_achievement_progress_post_type', 'dpa_progress'    );
		$this->event_tax_id                   = apply_filters( 'dpa_event_tax_id',                   'dpa_actions'     );

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
		require( $this->plugin_dir . 'includes/dpa-core-actions.php'    ); // All actions
		require( $this->plugin_dir . 'includes/dpa-core-filters.php'    ); // All filters
		require( $this->plugin_dir . 'includes/dpa-core-functions.php'  ); // Common functions
		require( $this->plugin_dir . 'includes/dpa-core-options.php'    ); // Configuration options
		require( $this->plugin_dir . 'includes/dpa-core-caps.php'       ); // Roles and capabilities
		require( $this->plugin_dir . 'includes/dpa-core-classes.php'    ); // Common classes
		//require( $this->plugin_dir . 'includes/dpa-core-widgets.php'    ); // Widgets
		//require( $this->plugin_dir . 'includes/dpa-core-shortcodes.php' ); // Shortcodes for use with pages and posts
		require( $this->plugin_dir . 'includes/dpa-core-update.php'     ); // Database updater


		/**
		 * Components
		 */
		require( $this->plugin_dir . 'includes/dpa-common-functions.php' ); // Common functions
		require( $this->plugin_dir . 'includes/dpa-common-template.php'  ); // Common template tags

		require( $this->plugin_dir . 'includes/dpa-user-functions.php'   ); // User functions

		require( $this->plugin_dir . 'includes/dpa-achievements-functions.php' ); // Implements the main logic (achievement event monitoring, etc)
		require( $this->plugin_dir . 'includes/dpa-achievements-template.php'  ); // Achievement post type template tags

		// Admin
		if ( is_admin() ) {
			require( $this->plugin_dir . 'admin/dpa-admin.php'         );
			require( $this->plugin_dir . 'admin/dpa-admin-actions.php' );
		}
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

		// All Achievements actions are setup (includes dpa-core-actions.php)
		do_action_ref_array( 'dpa_after_setup_actions', array( &$this ) );
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
		$locale = get_locale();                                     // Default locale
		$locale = apply_filters( 'plugin_locale', $locale, 'dpa' ); // Traditional WordPress plugin locale filter
		$locale = apply_filters( 'dpa_locale',    $locale );        // Achievements-specific locale filter
		$mofile = sprintf( 'dpa-%s.mo', $locale );                  // Get .mo file name

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/achievements/' . $mofile;

		// Look in local /wp-content/plugins/achievements/languages/ folder
		if ( file_exists( $mofile_local ) ) {
			return load_textdomain( 'dpa', $mofile_local );

		// Look in global /wp-content/languages/achievements/ folder
		} elseif ( file_exists( $mofile_global ) ) {
			return load_textdomain( 'dpa', $mofile_global );
		}

		// Nothing found
		return false;
	}

	/**
	 * Set up the post types for: achievement, achievement_progress
	 *
	 * @since 3.0
	 */
	public function register_post_types() {
		$cpt = $labels = $rewrite = $supports = array();

		// CPT labels
		$labels['achievement'] = array(
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
		$rewrite['achievement'] = array(
			'feeds'      => false,
			'pages'      => false,
			'slug'       => dpa_get_achievement_slug(),
			'with_front' => false,
		);
		$rewrite['achievement_progress'] = array(
			'feeds' => false,
			'pages' => false,
		);

		// CPT supports
		$supports['achievement'] = array(
			'editor',
			'revisions',
			'thumbnail',
			'title',
		);
		$supports['achievement_progress'] = array(
			'author',
		);

		// CPT filter
		$cpt['achievement'] = apply_filters( 'dpa_register_post_type_achievement', array(
			'can_export'          => true,
			'capabilities'        => dpa_get_achievement_caps(),
			'capability_type'     => array( 'achievement', 'achievements' ),
			'description'         => _x( 'Achievements types (e.g. new post, new site, new user)', 'Achievement post type description', 'dpa' ),
			'exclude_from_search' => false,
			//'has_archive'         => dpa_get_achievement_slug(),
			'hierarchical'        => true,
			'labels'              => $labels['achievement'],
			'public'              => true,
			'publicly_queryable'  => true,
			'query_var'           => true,
			'rewrite'             => $rewrite['achievement'],
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_ui'             => true,
			'supports'            => $supports['achievement'],
		) );
		$cpt['achievement_progress'] = apply_filters( 'dpa_register_post_type_achievement_progress', array(
			'can_export'          => true,
			'capabilities'        => dpa_get_achievement_progress_caps(),
			'capability_type'     => array( 'achievement_progress', 'achievement_progresses' ),
			'description'         => _x( 'Achievement Progress (e.g. unlocked achievements for a user, progress on an achievement for a user)', 'Achievement Progress post type description', 'dpa' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'public'              => false,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => $rewrite['achievement_progress'],
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_ui'             => false,
			'supports'            => $supports['achievement_progress'],
		) );

		// Register Achievement post type
		register_post_type( dpa_get_achievement_post_type(), $cpt['achievement'] );

		// Register Achievement Progress post type
		register_post_type( dpa_get_achievement_progress_post_type(), $cpt['achievement_progress'] );
	}

	/**
	 * Register the achievement event taxonomy
	 *
	 * @since 3.0
	 */
	public function register_taxonomies() {
		$action = array();

		// Event tax labels
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
			'hierarchical'          => true, // Better UI
			'labels'                => $action['labels'],
			'public'                => false,
			'show_tagcloud'         => false,
			'show_ui'               => true,
			'update_count_callback' => '_update_post_term_count',
		) );

		// Register the achievement event taxonomy
		register_taxonomy(
			dpa_get_event_tax_id(),          // The event taxonomy id
			dpa_get_achievement_post_type(), // The achievement post type
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

/**
 * The main function responsible for returning the one true Achievements instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @return Achievements The one true Achievements instance
 */
function achievements() {
	return Achievements::instance();
}

// This makes it go up to 11
Achievements();

endif; // class_exists check

?>