<?php
/**
 * Batman begins
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage Loader
 *
 * Plugin structure is based on bbPress and BuddyPress, because they're awesome. Borrowed with love.
 */

/*
Plugin Name: Achievements
Plugin URI: http://achievementsapp.wordpress.com/
Description: Achievements gives your BuddyPress community fresh impetus by promoting and rewarding social interaction with challenges, badges and points.
Version: 3
Requires at least: WP 3.4
Tested up to: WP 3.4
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
 * @since 3.0
 */
final class Achievements {
	/**
	 * Achievements uses many variables, most of which can be filtered to customize
	 * the way that it works. To prevent unauthorized access these variables
	 * are stored in a private array that is magically updated using PHP 5.2+
	 * methods. This is to prevent third party plugins from tampering with
	 * essential information indirectly, which could cause issues later.
	 *
	 * @see Achievements::setup_globals()
	 * @var array
	 */
	private $data;

	/**
	 * Current user
	 *
	 * @var stdClass|WP_User Empty when not logged in; WP_User object when logged in. (By ref)
	 */
	public $current_user;

	/**
	 * Other plugins append data here. Used to store information about the supported plugin
	 * and a list of its actions that you want to support.
	 *
	 * The items contained within this object need to derive from the {@link DPA_Extension} class.
	 *
	 * @var stdClass
	 */
	public $extend;

	/**
	 * @var array Overloads get_option()
	 */
	public $options      = array(); 

	/**
	 * @var array Overloads get_user_meta()
	 */
	public $user_options = array();

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
	 * Magic method for checking the existence of a certain custom field
	 *
	 * @since 3.0
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting Achievements variables
	 *
	 * @since 3.0
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting Achievements variables
	 *
	 * @since 3.0
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }


	// Private methods

	/**
	 * Set up global variables
	 *
	 * @since 3.0
	 */
	private function setup_globals() {
		// Versions
		$this->version    = 3.0;  // Achievements version
		$this->db_version = 300;  // Achievements DB version

		// Paths - plugin
		$this->file       = __FILE__;
		$this->basename   = apply_filters( 'dpa_basenname',       plugin_basename( $this->file ) );
		$this->plugin_dir = apply_filters( 'dpa_plugin_dir_path', plugin_dir_path( $this->file ) );
		$this->plugin_url = apply_filters( 'dpa_plugin_dir_url',  plugin_dir_url ( $this->file ) );

		// Paths - themes
		$this->themes_dir = apply_filters( 'dpa_themes_dir',      trailingslashit( $this->plugin_dir . 'themes' ) );
		$this->themes_url = apply_filters( 'dpa_themes_url',      trailingslashit( $this->plugin_url . 'themes' ) );

		// Paths - languages
		$this->lang_dir   = apply_filters( 'dpa_lang_dir',        trailingslashit( $this->plugin_dir . 'languages' ) );


		// Post type/taxonomy/endpoints identifiers
		$this->achievement_post_type          = apply_filters( 'dpa_achievement_post_type',          'dpa_achievement' );
		$this->achievement_progress_post_type = apply_filters( 'dpa_achievement_progress_post_type', 'dpa_progress'    );
		$this->authors_endpoint               = apply_filters( 'dpa_authors_endpoint',               'achievements'    );
		$this->event_tax_id                   = apply_filters( 'dpa_event_tax_id',                   'dpa_event'       );

		// Post status identifiers
		$this->locked_status_id   = apply_filters( 'dpa_locked_post_status',   'dpa_locked'   );
		$this->unlocked_status_id = apply_filters( 'dpa_unlocked_post_status', 'dpa_unlocked' );

		// Queries
		$this->achievement_query = new stdClass;  // Main dpa_achievement query
		$this->progress_query    = new stdClass;  // Main dpa_progress query

		// Theme compat
		$this->theme_compat = new stdClass();  // Base theme compatibility class
		$this->filters      = new stdClass();  // Used when adding/removing filters

		// Users
		$this->current_user = new stdClass();  // Currently logged in user

		// Other stuff
		$this->errors             = new WP_Error();
		$this->extend             = new stdClass();                                               // Other plugins add data here
		$this->minimum_capability = apply_filters( 'dpa_minimum_capability', 'manage_options' );  // Required capability to access most admin screens

		// Add to global cache groups
		wp_cache_add_global_groups( 'achievements' );
	}

	/**
	 * Include required files
	 *
	 * @since 3.0
	 */
	private function includes() {
		/**
		 * Core
		 */
		require( $this->plugin_dir . 'includes/core-actions.php'    ); // All actions
		require( $this->plugin_dir . 'includes/core-filters.php'    ); // All filters
		require( $this->plugin_dir . 'includes/core-functions.php'  ); // Core functions
		require( $this->plugin_dir . 'includes/core-options.php'    ); // Configuration options
		require( $this->plugin_dir . 'includes/core-caps.php'       ); // Roles and capabilities
		//require( $this->plugin_dir . 'includes/core-widgets.php'    ); // Widgets
		//require( $this->plugin_dir . 'includes/core-shortcodes.php' ); // Shortcodes for use with pages and posts
		require( $this->plugin_dir . 'includes/core-update.php'     ); // Database updater


		/**
		 * Supported plugins
		 */
		require( $this->plugin_dir . 'includes/class-dpa-extension.php' ); // Base interface and class for adding support for other plugins

		/**
		 * Components
		 */
		require( $this->plugin_dir . 'includes/common-functions.php' ); // Common functions
		require( $this->plugin_dir . 'includes/common-template.php'  ); // Common template tags

		require( $this->plugin_dir . 'includes/user-functions.php' ); // User functions
		require( $this->plugin_dir . 'includes/user-options.php'   ); // User options

		require( $this->plugin_dir . 'includes/achievements-functions.php' ); // Implements the main logic for the achievement post type (achievement event monitoring, etc)
		require( $this->plugin_dir . 'includes/achievements-template.php'  ); // Achievement post type template tags

		require( $this->plugin_dir . 'includes/progress-functions.php' ); // Implements the Progress post type
		require( $this->plugin_dir . 'includes/progress-template.php'  ); // Progress post type template tags


		/**
		 * Admin
		 */
		if ( is_admin() ) {
			require( $this->plugin_dir . 'admin/admin.php'         );
			require( $this->plugin_dir . 'admin/admin-actions.php' );
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
			'constants',               // Define constants
			'load_textdomain',         // Load textdomain
			'register_post_types',     // Register post types (dpa_achievement, dpa_progress)
			'register_post_statuses',  // Register post statuses (dpa_progress: locked, unlocked)
			'register_taxonomies',     // Register taxonomies (dpa_event)
			'register_endpoints',      // Register endpoints (achievements)
			'setup_current_user',      // Set up currently logged in user
		);

		foreach( $actions as $class_action )
			add_action( 'dpa_' . $class_action, array( $this, $class_action ), 5 );

		// All Achievements actions are setup (includes core-actions.php)
		do_action_ref_array( 'dpa_after_setup_actions', array( &$this ) );
	}

	/**
	 * Define constants
	 *
	 * @since 3.0
	 */
	public function constants() {
		// If multisite and running network-wide, we switch_to_blog to this site to store/fetch achievement data
		if ( ! defined( 'DPA_DATA_STORE' ) )
			define( 'DPA_DATA_STORE', 1 );
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
	public static function register_post_types() {
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
			'delete_with_user'    => false,
			'description'         => _x( 'Achievements types (e.g. new post, new site, new user)', 'Achievement post type description', 'dpa' ),
			'exclude_from_search' => false,
			'has_archive'         => true,
			'hierarchical'        => true,
			'labels'              => $labels['achievement'],
			'public'              => true,
			'publicly_queryable'  => true,
			'query_var'           => true,
			'rewrite'             => $rewrite['achievement'],
			'show_in_admin_bar'   => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_ui'             => dpa_current_user_can_see( dpa_get_achievement_post_type() ),
			'supports'            => $supports['achievement'],
		) );
		$cpt['achievement_progress'] = apply_filters( 'dpa_register_post_type_achievement_progress', array(
			'can_export'          => false,
			'capabilities'        => dpa_get_achievement_progress_caps(),
			'capability_type'     => array( 'achievement_progress', 'achievement_progresses' ),
			'delete_with_user'    => true,
			'description'         => _x( 'Achievement Progress (e.g. unlocked achievements for a user, progress on an achievement for a user)', 'Achievement Progress post type description', 'dpa' ),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'public'              => false,
			'publicly_queryable'  => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_admin_bar'   => false,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_ui'             => false,
			'supports'            => $supports['achievement_progress'],
		) );

		// Register Achievement post type
		register_post_type( dpa_get_achievement_post_type(), $cpt['achievement'] );

		// Register Achievement Progress post type
		register_post_type( dpa_get_progress_post_type(), $cpt['achievement_progress'] );
	}

	/**
	 * Register the post statuses used by Achievements
	 *
	 * @since 3.0
	 */
	public static function register_post_statuses() {
		// Locked
		register_post_status(
			dpa_get_locked_status_id(),
			apply_filters( 'dpa_register_locked_post_status', array(
				'label'                     => _x( 'Locked', 'achievement', 'dpa' ),
				'label_count'               => _nx_noop( 'Locked <span class="count">(%s)</span>', 'Locked <span class="count">(%s)</span>', 'dpa' ),
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
			) )
		);

		// Unlocked
		register_post_status(
			dpa_get_unlocked_status_id(),
			apply_filters( 'dpa_register_unlocked_post_status', array(
				'label'                     => _x( 'Unlocked', 'achievement', 'dpa' ),
				'label_count'               => _nx_noop( 'Unlocked <span class="count">(%s)</span>', 'Unlocked <span class="count">(%s)</span>', 'dpa' ),
				'public'                    => true,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
			) )
		);
	}

	/**
	 * Register the achievement event taxonomy
	 *
	 * @since 3.0
	 */
	public static function register_taxonomies() {
		$labels = $tax = array();

		// Event tax labels
		$labels['event'] = array(
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
		$tax = apply_filters( 'dpa_register_taxonomies_action', array(
			'capabilities'          => dpa_get_event_caps(),
			'hierarchical'          => true,  // Better UI
			'labels'                => $labels['event'],
			'public'                => false,
			'query_var'             => false,
			'rewrite'               => false,
			'show_in_nav_menus'     => false,
			'show_tagcloud'         => false,
			'show_ui'               => true,  // @todo Temp for dev
			'update_count_callback' => '_update_post_term_count',
		) );

		// Register the achievement event taxonomy
		register_taxonomy(
			dpa_get_event_tax_id(),          // The event taxonomy id
			dpa_get_achievement_post_type(), // The achievement post type
			$tax
		);
	}

	/**
	 * Register endpoints
	 *
	 * @since 3.0
	 */
	public static function register_endpoints() {
		add_rewrite_endpoint( dpa_get_authors_endpoint(), EP_AUTHORS );  // /authors/paul/[achievements]
	}

	/**
	 * Set up the currently logged in user.
	 *
	 * Do not call this before the 'init' action has started.
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