<?php
/**
 * "The door to your right leads to the Source and the salvation of Zion".
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage loader
 */

/**
 * Load the gettext translation for the plugin.
 *
 * @since 3.0
 */
function dpa_load_textdomain() {
	$locale        = apply_filters( 'dpa_locale', get_locale() );
	$mofile        = sprintf( 'dpa-%s.mo', $locale );
	$mofile_global = WP_LANG_DIR . '/' . $mofile;

	if ( file_exists( $mofile_global ) )
		load_textdomain( 'dpa', $mofile_global );
	else
		load_plugin_textdomain( 'dpa', false, '/achievements/languages/' );
}

/**
 * Register post type
 *
 * @since 3.0
 */
function dpa_register_post_types() {
	$achievements_labels = array(
		'add_new'            => _x( 'Add New', 'achievement',          'dpa' ),
		'add_new_item'       => __( 'Add New Achievement',             'dpa' ),
		'all_items'          => __( 'All Achievements',                'dpa' ),
		'edit'               => _x( 'Edit',    'achievement',          'dpa' ),
		'edit_item'          => __( 'Edit Achievement',                'dpa' ),
		'name'               => __( 'Achievements',                    'dpa' ),
		'new_item'           => __( 'New Achievement',                 'dpa' ),
		'not_found'          => __( 'No achievements found.',          'dpa' ),
		'not_found_in_trash' => __( 'No achievements found in Trash.', 'dpa' ),
		'search_items'       => __( 'Search Achievements',             'dpa' ),
		'singular_name'      => __( 'Achievement',                     'dpa' ),
		'view'               => __( 'View Achievement',                'dpa' ),
		'view_item'          => __( 'View Achievement',                'dpa' ),
	);

	// Achievements
	$achievements  = apply_filters( 'dpa_register_post_type_achievements', array(
		'can_export'           => true,
		'capability_type'      => array( 'achievement', 'achievements' ),
		'description'          => _x( 'Achievements types (e.g. new post, new site, new user, etc)', 'Achievement post type description', 'dpa' ),
		'exclude_from_search'  => true,
		'has_archive'          => false,
		'hierarchical'         => false,
		'labels'               => $achievements_labels,
		'public'               => true,
		'query_var'            => true,
		'rewrite'              => false,
		'show_in_menu'         => true,
		'show_in_nav_menus'    => true,
		'show_ui'              => true,
		'supports'             => array( 'editor', 'revisions', 'title', 'thumbnail' ),
	) );

	register_post_type( 'dpa_achievements', $achievements );
}

/**
 * Register custom taxonomies
 *
 * The 'dpa_action' taxonomy is used by the achievement custom post type to
 * associate an action with the achievement.
 *
 * @since 3.0
 */
function dpa_register_taxonomies() {
	$action_labels = array(
		'name'          => _x( 'Events', 'event taxonomy general name', 'dpa' ),
		'singular_name' => _x( 'Event', 'event taxonomy singular name', 'dpa' ),
		'search_items'  => __( 'Search Events',                         'dpa' ),
		'popular_items' => __( 'Popular Events',                        'dpa' ),
		'all_items'     => __( 'All',                                   'dpa' ),
		'edit_item'     => __( 'Edit Event',                            'dpa' ),
		'update_item'   => __( 'Update Event',                          'dpa' ),
		'add_new_item'  => __( 'Add New Event',                         'dpa' ),
		'new_item_name' => __( 'New Event Name',                        'dpa' )
	);

	$action = apply_filters( 'dpa_register_taxonomy_action', array(
		'hierarchical'          => true,
		'labels'                => $action_labels,
		'public'                => false,
		'query_var'             => false,
		'rewrite'               => false,
		'show_tagcloud'         => false,
		'show_ui'               => false,
		'update_count_callback' => '_update_post_term_count'
	) );
	register_taxonomy( 'dpa_action', 'dpa_achievements', $action );
}
?>