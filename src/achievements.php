<?php
/**
 * Batman begins
 *
 * Plugin structure is based on bbPress and BuddyPress, because they're awesome. Borrowed with love.
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage Loader
 */

/*
Plugin Name: Achievements
Plugin URI: http://achievementsapp.com/
Description: Achievements gamifies your WordPress site with challenges, badges, and points.
Version: 3.5.1
Requires at least: 3.8
Tested up to: 3.8.20
License: GPLv3
Author: Paul Gibbs
Author URI: http://byotos.com/
Domain Path: ../../languages/plugins/
Text Domain: dpa

"Achievements"
Copyright (C) 2009-13 Paul Gibbs

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

if ( ! class_exists( 'DPA_Achievements_Loader' ) ) :
/**
 * Main Achievements class
 *
 * @since Achievements (3.0)
 */
final class DPA_Achievements_Loader {
	/**
	 * Achievements uses many variables, several of which can be filtered to
	 * customise the way it operates. Most of these variables are stored in a
	 * private array that gets updated with the help of PHP magic methods.
	 *
	 * This is a precautionary measure to avoid potential errors produced by
	 * unanticipated direct manipulation of Achievements' run-time data.
	 *
	 * @see Achievements::setup_globals()
	 * @var array
	 */
	private $data;

	/**
	 * Other plugins append data here. Used to store information about the supported plugin
	 * and a list of its actions that you want to support.
	 *
	 * The items contained within this object need to derive from the {@link DPA_Extension} class.
	 *
	 * @var stdClass
	 */
	public $extensions;

	/**
	 * @var array Overloads get_option()
	 */
	public $options      = array();

	/**
	 * @var array Overloads get_user_meta()
	 */
	public $user_options = array();


	/**
	 * Main Achievements instance
	 *
	 * Insures that only one instance of Achievements exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @return DPA_Achievements_Loader The one true Achievements
	 * @see achievements()
	 * @since Achievements (3.0)
	 */
	public static function instance() {
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$instance = new DPA_Achievements_Loader;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions(); 
		}

		return $instance;
	}


	// Magic Methods

	/**
	 * A dummy constructor to prevent Achievements from being loaded more than once.
	 *
	 * @since Achievements (3.0)
	 */
	private function __construct() {}

	/**
	 * A dummy magic method to prevent Achievements from being cloned
	 *
	 * @since Achievements (3.0)
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'dpa' ), '3.0' ); }

	/**
	 * A dummy magic method to prevent Achievements from being unserialised
	 *
	 * @since Achievements (3.0)
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'dpa' ), '3.0' ); }

	/**
	 * Magic method for checking the existence of a certain custom field
	 *
	 * @param string $key
	 * @since Achievements (3.0)
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting Achievements variables
	 *
	 * @param string $key
	 * @since Achievements (3.0)
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting Achievements variables
	 *
	 * @param string $key
	 * @param mixed $value
	 * @since Achievements (3.0)
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }

	/**
	 * Magic method for unsetting Achievements variables
	 *
	 * @param string $key
	 * @since Achievements (3.0)
	 */
	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }


	// Private methods

	/**
	 * Set up global variables
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_globals() {
		// Versions
		$this->version    = '3.5.1';
		$this->db_version = 340;

		// Paths - plugin
		$this->file       = __FILE__;
		$this->basename   = apply_filters( 'dpa_basenname',       plugin_basename( $this->file ) );
		$this->plugin_dir = apply_filters( 'dpa_plugin_dir_path', plugin_dir_path( $this->file ) );
		$this->plugin_url = apply_filters( 'dpa_plugin_dir_url',  plugin_dir_url ( $this->file ) );

		// Paths - theme compatibility packs
		$this->themes_dir = apply_filters( 'dpa_themes_dir', trailingslashit( $this->plugin_dir . 'templates' ) );
		$this->themes_url = apply_filters( 'dpa_themes_url', trailingslashit( $this->plugin_url . 'templates' ) );

		// Paths - languages
		$this->lang_dir = apply_filters( 'dpa_lang_dir', trailingslashit( $this->plugin_dir . 'languages' ) );

		// Includes
		$this->includes_dir = apply_filters( 'dpa_includes_dir', trailingslashit( $this->plugin_dir . 'includes' ) );
		$this->includes_url = apply_filters( 'dpa_includes_url', trailingslashit( $this->plugin_url . 'includes' ) );

		// Post type/endpoint/taxonomy identifiers
		$this->achievement_post_type          = apply_filters( 'dpa_achievement_post_type',          'achievement'  );
		$this->achievement_progress_post_type = apply_filters( 'dpa_achievement_progress_post_type', 'dpa_progress' );
		$this->authors_endpoint               = apply_filters( 'dpa_authors_endpoint',               'achievements' );
		$this->event_tax_id                   = apply_filters( 'dpa_event_tax_id',                   'dpa_event'    );

		// Post status identifiers
		$this->locked_status_id   = apply_filters( 'dpa_locked_post_status',   'dpa_locked'   );
		$this->unlocked_status_id = apply_filters( 'dpa_unlocked_post_status', 'dpa_unlocked' );

		// Queries
		$this->current_achievement_id = 0;  // Current achievement ID

		$this->achievement_query = new WP_Query();     // Main achievement post type query
		$this->leaderboard_query = new ArrayObject();  // Leaderboard template loop
		$this->progress_query    = new WP_Query();     // Main dpa_progress post type query

		// Theme compat
		$this->theme_compat = new stdClass();  // Base theme compatibility class
		$this->filters      = new stdClass();  // Used when adding/removing filters

		// Other stuff
		$this->domain     = 'dpa';            // Unique identifier for retrieving translated strings
		$this->errors     = new WP_Error();   // Errors
		$this->extensions = new stdClass();   // Other plugins add data here

		// Deep integration with other plugins
		$this->integrate_into_buddypress = false;  // Use BuddyPress profiles for screens like "my achievements"

		/**
		 * If multisite and running network-wide, grab the options from the site options
		 * table and store in achievements()->options. dpa_setup_option_filters() sets
		 * up a pre_option filter which loads from achievements()->options if an option
		 * has been set there. This saves a lot of conditionals throughout the plugin.
		 */
		if ( is_multisite() && dpa_is_running_networkwide() ) {
			$options = dpa_get_default_options();
			foreach ( $options as $option_name => $option_value )
				achievements()->options[$option_name] = get_site_option( $option_name );
		}
	}

	/**
	 * Include required files
	 *
	 * @since Achievements (3.0)
	 */
	private function includes() {
		/**
		 * Classes
		 */
		require( $this->includes_dir . 'class-dpa-extension.php'     ); // Base class for adding support for other plugins
		require( $this->includes_dir . 'class-dpa-cpt-extension.php' ); // Base class for adding support for other plugins using post type actions
		require( $this->includes_dir . 'class-dpa-shortcodes.php'    );


		/**
		 * Core
		 */
		require( $this->includes_dir . 'core/dependency.php' );
		require( $this->includes_dir . 'core/functions.php'  );
		require( $this->includes_dir . 'core/cache.php'      );
		require( $this->includes_dir . 'core/options.php'    );
		require( $this->includes_dir . 'core/caps.php'       );
		require( $this->includes_dir . 'core/update.php'     );
		require( $this->includes_dir . 'core/backpat.php'    );


		/**
		 * Templates
		 */
		require( $this->includes_dir . 'core/template-functions.php'  );
		require( $this->includes_dir . 'core/template-loader.php'     );
		require( $this->includes_dir . 'core/theme-compatibility.php' );


		/**
		 * Plugin extensions
		 */
		//We need this so that is_active_plugin is registered when we check for active plugins
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		require( $this->includes_dir . 'extensions/bbpress.php'       );
		require( $this->includes_dir . 'extensions/buddypress.php'    );
		require( $this->includes_dir . 'extensions/buddystream.php'   );
		require( $this->includes_dir . 'extensions/inviteanyone.php'  );
		require( $this->includes_dir . 'extensions/scholarpress.php'  );
		require( $this->includes_dir . 'extensions/wordpress.php'     );
		require( $this->includes_dir . 'extensions/wpecommerce.php'   );
		require( $this->includes_dir . 'extensions/wppostratings.php' );

		require( $this->includes_dir . 'buddypress/functions.php'     );


		/**
		 * Components
		 */
		require( $this->includes_dir . 'common/functions.php'  );
		require( $this->includes_dir . 'common/template.php'   );

		require( $this->includes_dir . 'achievements/functions.php' );
		require( $this->includes_dir . 'achievements/template.php'  );

		require( $this->includes_dir . 'progress/functions.php' );
		require( $this->includes_dir . 'progress/template.php'  );

		require( $this->includes_dir . 'users/capabilities.php'  );
		require( $this->includes_dir . 'users/functions.php'     );
		require( $this->includes_dir . 'users/template.php'      );
		require( $this->includes_dir . 'users/options.php'       );
		require( $this->includes_dir . 'users/notifications.php' );


		/**
		 * Hooks
		 */
		require( $this->includes_dir . 'core/actions.php' );
		require( $this->includes_dir . 'core/filters.php' );


		/**
		 * Widgets
		 */
		require( $this->includes_dir . 'class-dpa-redeem-achievements-widget.php'    );
		require( $this->includes_dir . 'class-dpa-featured-achievement-widget.php'   );
		require( $this->includes_dir . 'class-dpa-available-achievements-widget.php' );
		require( $this->includes_dir . 'class-dpa-leaderboard-widget.php'            );


		/**
		 * Admin
		 */
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			require( $this->includes_dir . 'admin/admin.php'   );
			require( $this->includes_dir . 'admin/actions.php' );
		}


		/**
		 * WP-CLI
		 */
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require( $this->includes_dir . 'class-dpa-wpcli-achievements-command.php' );
			require( $this->includes_dir . 'class-dpa-wpcli-achievements-users-command.php' );
		}
	}

	/**
	 * Set up the default hooks and actions
	 *
	 * @since Achievements (3.0)
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
			'setup_theme',               // Set up the default theme compat
			'register_post_types',       // Register post types (achievement, dpa_progress)
			'register_post_statuses',    // Register post statuses (dpa_progress: locked, unlocked)
			'register_taxonomies',       // Register taxonomies (dpa_event)
			'register_shortcodes',       // Register shortcodes
			'register_theme_packages',   // Register bundled theme packages (templates)
			'load_textdomain',           // Load textdomain
			'constants',                 // Define constants
			'register_endpoints',        // Register endpoints (achievements)
			'admin_bar_menu',            // Register custom menu items (My Achievements)
			'register_image_sizes',      // Add custom image sizes
		);

		foreach( $actions as $class_action )
			add_action( 'dpa_' . $class_action, array( $this, $class_action ), 5 );

		// All Achievements actions are setup (includes core/actions.php)
		do_action_ref_array( 'dpa_after_setup_actions', array( &$this ) );
	}

	/**
	 * Define constants
	 *
	 * @since Achievements (3.0)
	 */
	public function constants() {
		// If multisite and running network-wide, we switch_to_blog to this site to store/fetch achievement data
		if ( ! defined( 'DPA_DATA_STORE' ) )
			define( 'DPA_DATA_STORE', 1 );
	}

	/**
	 * Load the translation file for current language. Checks the default WordPress languages folder.
	 *
	 * @since Achievements (3.0)
	 */
	public function load_textdomain() {

		// Try to load via load_plugin_textdomain() first, for future wordpress.org translation downloads
		if ( load_plugin_textdomain( $this->domain, false, 'achievements' ) )
			return;

		$locale = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Nothing found look in global /wp-content/languages/plugins/ folder
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

		load_textdomain( $this->domain, $mofile_global );
	}

	/**
	 * Register endpoints
	 *
	 * @since Achievements (3.0)
	 */
	public function register_endpoints() {

		// If we're integrating into BP user profiles, bail out.
		if ( dpa_integrate_into_buddypress() )
			return;

		// If the plugin's been activated network-wide, only register the endpoint on the DPA_DATA_STORE site
		if ( is_multisite() && dpa_is_running_networkwide() && get_current_blog_id() !== DPA_DATA_STORE )
			return;

		add_rewrite_endpoint( dpa_get_authors_endpoint(), EP_AUTHORS );  // /authors/paul/[achievements]
	}

	/**
	 * Set up the post types for: achievement, achievement_progress
	 *
	 * @since Achievements (3.0)
	 */
	public function register_post_types() {
		$cpt = $labels = $rewrite = $supports = array();

		/**
		 * If the plugin's been activated network-wide, only allow the normal access and behaviour on the DPA_DATA_STORE site.
		 * This prevents the admin controls showing up on the wrong site's wp-admin, as well as the overhead of unused rewrite rules.
		 *
		 * The problem with this is that the post type needs to be registered all on sites in a multisite all the time, otherwise
		 * achievements can't be awarded. See _update_blog_date_on_post_publish() which tries to create (in our case) a
		 * "dpa-progress" post.
		 *
		 * The solution to this is $post_type_is_public. If it's false, the post type is registered, but it's hidden from the admin,
		 * isn't publicly queryable, doesn't create rewrite rules, and so on. If it's set to true, the post type behaves as normal.
		 */
		$post_type_is_public = ( is_multisite() && dpa_is_running_networkwide() && get_current_blog_id() !== DPA_DATA_STORE ) ? false : true;

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
			'ep_mask'    => 0,                           // EP_ROOT - removes comment-page rewrite rules
			'feed'       => false,                       // Remove feed rewrite rules
			'feeds'      => false,                       // Remove feed rewrite rules (this is what the parameter ought to be)
			'pages'      => true,
			'slug'       => dpa_get_singular_root_slug(),
			'with_front' => false,
		);
		// CPT supports
		$supports['achievement'] = array(
			'editor',
			'excerpt',
			'revisions',
			'thumbnail',
			'title',
		);
		$supports['achievement_progress'] = array(
			'author',
		);

		// CPT filter
		$cpt['achievement'] = apply_filters( 'dpa_register_post_type_achievement', array(
			'capabilities'         => dpa_get_achievement_caps(),
			'capability_type'      => array( 'achievement', 'achievements' ),
			'delete_with_user'     => false,
			'description'          => _x( 'Achievements types (e.g. new post, new site, new user)', 'Achievement post type description', 'dpa' ),
			'has_archive'          => $post_type_is_public ? dpa_get_root_slug() : false,
			'labels'               => $labels['achievement'],
			'public'               => $post_type_is_public,
			'rewrite'              => $rewrite['achievement'],
			'register_meta_box_cb' => 'dpa_admin_setup_metaboxes',
			'show_in_menu'         => $post_type_is_public,
			'show_ui'              => dpa_current_user_can_see( dpa_get_achievement_post_type() ),
			'supports'             => $supports['achievement'],
			'taxonomies'           => array( 'category' ),
		) );
		$cpt['achievement_progress'] = apply_filters( 'dpa_register_post_type_achievement_progress', array(
			'capabilities'        => dpa_get_achievement_progress_caps(),
			'capability_type'     => array( 'achievement_progress', 'achievement_progresses' ),
			'delete_with_user'    => true,
			'description'         => _x( 'Achievement Progress (e.g. unlocked achievements for a user, progress on an achievement for a user)', 'Achievement Progress post type description', 'dpa' ),
			'public'              => false,
			'query_var'           => false,
			'rewrite'             => false,
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
	 * @since Achievements (3.0)
	 */
	public function register_post_statuses() {
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
				'public'                    => false,
				'exclude_from_search'       => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
			) )
		);
	}

	/**
	 * Register the achievement event taxonomy
	 *
	 * @since Achievements (3.0)
	 */
	public function register_taxonomies() {
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
			'hierarchical'          => false,
			'labels'                => $labels['event'],
			'public'                => false,
			'query_var'             => false,
			'rewrite'               => false,
			'show_in_nav_menus'     => false,
			'show_tagcloud'         => false,
			'show_ui'               => dpa_is_developer_mode(),
			'update_count_callback' => 'dpa_update_event_term_count',
		) );

		// Register the achievement event taxonomy
		register_taxonomy(
			dpa_get_event_tax_id(),          // The event taxonomy id
			dpa_get_achievement_post_type(), // The achievement post type
			$tax
		);
	}

	/**
	 * Register bundled theme packages
	 *
	 * Note that since we currently have complete control over the /templates/
	 * folders, it's fine to hardcode these here. If at a later date we need to
	 * automate this, an API will need to be built.
	 *
	 * @since Achievements (3.0)
	 */
	public function register_theme_packages() {
		dpa_register_theme_package( array(
			'id'      => 'default',
			'name'    => __( 'Achievements Default', 'dpa' ),
			'version' => dpa_get_version(),
			'dir'     => trailingslashit( $this->themes_dir . 'achievements' ),
			'url'     => trailingslashit( $this->themes_url . 'achievements' )
		) );
	}

	/**
	 * Register Achievements' shortcodes
	 *
	 * @since Achievements (3.0)
	 */
	public function register_shortcodes() {
		$this->shortcodes = new DPA_Shortcodes();
	}

	/**
	 * Register custom menu items
	 *
	 * @global WP_Admin_Bar $wp_admin_bar
	 * @since Achievements (3.0)
	 */
	public function admin_bar_menu() {
		global $wp_admin_bar;

		if ( ! dpa_is_user_active() )
			return;

		$wp_admin_bar->add_node( array(
			'href'   => dpa_get_user_avatar_link( 'type=url' ),
			'id'     => 'dpa_my_achievements',
			'parent' => 'user-actions',
			'title'  => _x( 'My Achievements', 'Menu item in the toolbar', 'dpa' ),
		) );
	}

	/**
	 * Setup the default Achievements theme compatibility location.
	 *
	 * @since Achievements (3.0)
	 */
	public function setup_theme() {
		// Bail if something already has this under control
		if ( ! empty( $this->theme_compat->theme ) )
			return;

		// Setup the theme package to use for compatibility
		dpa_setup_theme_compat( dpa_get_theme_package_id() );
	}

	/**
	 * Add custom image sizes for image cropping
	 *
	 * @since Achievements (3.3)
	 */
	public function register_image_sizes() {
		add_image_size( 'dpa-thumb', 32, 32 );
	}
}

/**
 * Checks if the plugin across entire network, rather than on a specific site (for multisite)
 *
 * Needs to be in scope for DPA_Achievements_Loader::setup_globals(), so it's not in core/options.php
 *
 * @return bool
 * @since Achievements (3.0)
 * @todo Review if is_plugin_active_for_network() not being available on Network Activation is a WP core bug.
 */
function dpa_is_running_networkwide() {
	$retval = false;

	if ( is_multisite() ) {
		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[achievements()->basename] ) )
			$retval = true;
	}

	return (bool) apply_filters( 'dpa_is_running_networkwide', $retval );
}

/**
 * Get the default site options and their values
 *
 * Needs to be in scope for DPA_Achievements_Loader::setup_globals(), so it's not in core/options.php
 *
 * @return array Option names and values
 * @since Achievements (3.0)
 */
function dpa_get_default_options() {
	$options = array(
		// DB version
		'_dpa_db_version' => achievements()->db_version,        // Initial DB version

		// Settings
		'_dpa_theme_package_id' => 'default',                   // The ID for the current theme package.

		// Achievement post type
		'_dpa_achievements_per_page'     => 15,                 // Achievements per page
		'_dpa_achievements_per_rss_page' => 25,                 // Achievements per RSS page
		'_dpa_root_slug'                 => 'achievements',     // Achievements archive slug
		'_dpa_singular_root_slug'        => 'achievement',      // Achievements singular item slug

		// Progress post type
		'_dpa_progresses_per_page'     => 15,                   // Progresses per page
		'_dpa_progresses_per_rss_page' => 25,                   // Progresses per RSS page

		// Extension support
		'_dpa_extension_versions' => array(),                   // Version numbers for the plugin extensions

		// Stats
		'_dpa_stats_last_achievement_id'      => 0,             // ID of the last unlocked achievement
		'_dpa_stats_last_achievement_user_id' => 0,             // ID of the user who unlocked the last achievement
	);

	return apply_filters( 'dpa_get_default_options', $options );
}

/**
 * The main function responsible for returning the one true Achievements instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @return DPA_Achievements_Loader The one true Achievements instance
 * @since Achievements (3.0)
 */
function achievements() {
	return DPA_Achievements_Loader::instance();
}

/**
 * Hook Achievements early onto the 'plugins_loaded' action.
 *
 * This gives all other plugins the chance to load before Achievements to get their
 * actions, filters, and overrides setup without Achievements being in the way.
 */
if ( defined( 'DPA_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'achievements', (int) DPA_LATE_LOAD );

// This makes it go up to 11
} else {
	achievements();
}

endif; // class_exists check