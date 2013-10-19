<?php
/**
 * Actions
 *
 * This file contains the filters that are used throughout Achievements. They are
 * consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 *
 * @package Achievements
 * @subpackage CoreActions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Attach Achievements to WordPress
 *
 * Achievements uses its own internal actions to help aid in additional plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress occur.
 */
add_action( 'plugins_loaded',         'dpa_loaded',                 10 );
add_action( 'init',                   'dpa_init',                   0  ); // Early for dpa_register 
add_action( 'parse_query',            'dpa_parse_query',            2  ); // Early for overrides
add_action( 'widgets_init',           'dpa_widgets_init',           10 );
add_action( 'generate_rewrite_rules', 'dpa_generate_rewrite_rules', 10 );
add_action( 'wp_enqueue_scripts',     'dpa_enqueue_scripts',        10 );
add_action( 'wp_head',                'dpa_head',                   10 );
add_action( 'wp_footer',              'dpa_footer',                 10 );
add_action( 'set_current_user',       'dpa_setup_current_user',     10 );
add_action( 'setup_theme',            'dpa_setup_theme',            10 );
add_action( 'after_setup_theme',      'dpa_after_setup_theme',      10 );
add_action( 'template_redirect',      'dpa_template_redirect',      10 );
add_action( 'admin_bar_menu',         'dpa_admin_bar_menu',         10 );

/**
 * dpa_loaded - Attached to 'plugins_loaded' above
 *
 * Attach various loader actions to the dpa_loaded action.
 */
add_action( 'dpa_loaded', 'dpa_constants',                 2  );
add_action( 'dpa_loaded', 'dpa_bootstrap_globals',         4  );
add_action( 'dpa_loaded', 'dpa_includes',                  6  );
add_action( 'dpa_loaded', 'dpa_setup_globals',             8  );
add_action( 'dpa_loaded', 'dpa_setup_option_filters',      10 );
add_action( 'dpa_loaded', 'dpa_setup_user_option_filters', 12 );
add_action( 'dpa_loaded', 'dpa_register_theme_packages',   14 );
add_action( 'dpa_loaded', 'dpa_load_textdomain',           16 );

/**
 * dpa_init - Attached to 'init' above
 *
 * Attach various initialisation actions to the init action.
 */
add_action( 'dpa_init', 'dpa_register', 0   );
add_action( 'dpa_init', 'dpa_ready',    999 );

/**
 * dpa_register - Attached to 'init' above on 0 priority
 *
 * Attach various initilisation actions early to the init action.
 * The load order helps to execute code at the correct time.
 */
add_action( 'dpa_register', 'dpa_register_post_types',    2  );
add_action( 'dpa_register', 'dpa_register_post_statuses', 4  );
add_action( 'dpa_register', 'dpa_register_taxonomies',    6  );
add_action( 'dpa_register', 'dpa_register_endpoints',     8  );
add_action( 'dpa_register', 'dpa_register_shortcodes',    10 );
add_action( 'dpa_register', 'dpa_register_image_sizes',   12 );

/**
 * dpa_ready - Attached to 'dpa_init' above
 *
 * Attach various initialisation actions to the dpa_init action.
 */
add_action( 'dpa_ready', 'dpa_maybe_update_extensions', 18 );
add_action( 'dpa_ready', 'dpa_register_events',         20 );

/**
 * Actions for BuddyPress support
 */
add_action( 'activated_plugin',   'dpa_check_buddypress_is_active' );
add_action( 'deactivated_plugin', 'dpa_check_buddypress_is_active' );
add_action( 'bp_include',         'dpa_bp_loaded'                  );

// add_theme_support for post thumbnails. Intentionally hooked late to after_setup_theme to allow theme's functions.php to run first.
add_action( 'after_setup_theme', 'dpa_add_post_thumbnail_support', 100 );

// Try to load the achievements-functions.php file from the active theme
add_action( 'dpa_after_setup_theme', 'dpa_load_theme_functions', 10 );

// Widgets
add_action( 'dpa_widgets_init', array( 'DPA_Redeem_Achievements_Widget',    'register_widget' ), 10 );
add_action( 'dpa_widgets_init', array( 'DPA_Featured_Achievement_Widget',   'register_widget' ), 10 );
add_action( 'dpa_widgets_init', array( 'DPA_Available_Achievements_Widget', 'register_widget' ), 10 );
add_action( 'dpa_widgets_init', array( 'DPA_Leaderboard_Widget',            'register_widget' ), 10 );

// Template - Head, foot, errors and messages
add_action( 'dpa_head',             'dpa_achievement_notices' );
add_action( 'dpa_template_notices', 'dpa_template_notices'    );

// User status
add_action( 'make_ham_user',  'dpa_make_ham_user'  );
add_action( 'make_spam_user', 'dpa_make_spam_user' );

// Achievement unlocked
add_action( 'dpa_unlock_achievement', 'dpa_send_points',              10, 3 );
add_action( 'dpa_unlock_achievement', 'dpa_send_notification',        10, 3 );
add_action( 'dpa_unlock_achievement', 'dpa_update_achievement_stats', 10, 3 );
add_action( 'dpa_unlock_achievement', 'dpa_update_user_stats',        10, 3 );

// Before delete achievement
add_action( 'delete_post', 'dpa_before_achievement_deleted' );

// POST handler
add_action( 'dpa_template_redirect', 'dpa_post_request', 10 );

// Theme-side POST requests
add_action( 'dpa_post_request', 'dpa_form_redeem_achievement', 10 );

// Activation redirect
add_action( 'dpa_activation', 'dpa_add_activation_redirect' );

// Deprecated functionality
// 3.5
if ( dpa_deprecated_notification_template_exists() ) {
	add_action( 'dpa_enqueue_scripts', 'dpa_deprecated_enqueue_notification_styles_and_scripts' );
	add_action( 'dpa_footer',          'dpa_deprecated_print_notifications'                     );
}
