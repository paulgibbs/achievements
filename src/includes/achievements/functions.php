<?php
/**
 * Achievement post type, endpoint, Event taxonomy, and other utility functions.
 *
 * @package Achievements
 * @subpackage AchievementsFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Retrieves a list of achievement posts matching criteria
 *
 * Most of the values that $args can accept are documented in {@link WP_Query}. The custom values added by Achievements are as follows:
 * 'ach_event' - string - Loads achievements for a specific event. Matches a slug from the dpa_event tax. Default is empty.
 *
 * If you try to use this function, you will need to implement your own switch_to_blog and wp_reset_postdata() handling if running in a multisite
 * and in a dpa_is_running_networkwide() configuration, otherwise the data won't be fetched from the appropriate site.
 *
 * @param array|string $args All the arguments supported by {@link WP_Query}, and some more.
 * @return array Posts
 * @since Achievements (3.0)
 */
function dpa_get_achievements( $args = array() ) {

	$defaults = array(
		// Standard WP_Query params
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,                             // Disable SQL_CALC_FOUND_ROWS (used for pagination queries)
		'order'               => 'ASC',                            // 'ASC', 'DESC
		'orderby'             => 'title',                          // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', rand'
		'post_type'           => dpa_get_achievement_post_type(),  // Only retrieve achievement posts
		'posts_per_page'      => -1,                               // Achievements per page

		// Achievements params
 		'ach_event'           => '',                               // Load achievements for a specific event
	);

	// Load achievements for a specific event
	if ( ! empty( $args['ach_event'] ) ) {

		$args['tax_query'] = array(
			array(
				'field'    => 'slug',
				'taxonomy' => dpa_get_event_tax_id(),
				'terms'    => $args['ach_event'],
			)
		);

		unset( $args['ach_event'] );
	}

	$args         = dpa_parse_args( $args, $defaults, 'get_achievements' );
	$achievements = new WP_Query;

	return apply_filters( 'dpa_get_achievements', $achievements->query( $args ), $args );
}

/**
 * Output the unique id of the custom post type for achievement
 *
 * @since Achievements (3.0)
 * @uses dpa_get_achievement_post_type() To get the achievement post type
 */
function dpa_achievement_post_type() {
	echo dpa_get_achievement_post_type();
}
	/**
	 * Return the unique id of the custom post type for achievement
	 *
	 * @return string The unique post type id
	 * @since Achievements (3.0)
	 */
	function dpa_get_achievement_post_type() {
		return apply_filters( 'dpa_get_achievement_post_type', achievements()->achievement_post_type );
	}

/**
 * Output the id of the authors achievements endpoint
 *
 * @since Achievements (3.0)
 * @uses dpa_get_authors_endpoint() To get the authors achievements endpoint
 */
function dpa_authors_endpoint() {
	echo dpa_get_authors_endpoint();
}
	/**
	 * Return the id of the authors achievements endpoint
	 *
	 * @return string The endpoint
	 * @since Achievements (3.0)
	 */
	function dpa_get_authors_endpoint() {
		return apply_filters( 'dpa_get_authors_endpoint', achievements()->authors_endpoint );
	}

/**
 * Return the event taxonomy ID
 *
 * @since Achievements (3.0)
 * @return string
 */
function dpa_get_event_tax_id() {
	return apply_filters( 'dpa_get_event_tax_id', achievements()->event_tax_id );
}

/**
 * Return the total count of the number of achievements
 *
 * @return int Total achievement count
 * @since Achievements (3.0)
 */
function dpa_get_total_achievement_count() {
	$counts = wp_count_posts( dpa_get_achievement_post_type() );
	return apply_filters( 'dpa_get_total_achievement_count', (int) $counts->publish );
}

/**
 * When an achievement is unlocked, update various stats relating to the achievement.
 *
 * @param object $achievement_obj The Achievement object.
 * @param int $user_id ID of the user who unlocked the achievement.
 * @param int $progress_id The Progress object's ID.
 * @since Achievements (3.0)
 */
function dpa_update_achievement_stats( $achievement_obj, $user_id, $progress_id ) {
	// Update the 'last unlocked achievement' stats
	dpa_stats_update_last_achievement_id( $achievement_obj->ID );
	dpa_stats_update_last_achievement_user_id( $user_id );

	// Allow other plugins to update their own stats when an achievement is unlocked
	do_action( 'dpa_update_achievement_stats', $achievement_obj, $user_id, $progress_id );
}

/**
 * Returns details of all events from the event taxonomy, and groups the events by extension.
 *
 * This is used in the new/edit post type screen, but can be used anywhere where you need to
 * show a list of all events grouped by the extension which provides them.
 *
 * @return array
 * @since Achievements (3.0)
 */
function dpa_get_all_events_details() {
	$temp_events = array();

	// Get all events from the event taxonomy and sort them by the plugin which provides them
	$events = get_terms( achievements()->event_tax_id, array( 'hide_empty' => false ) );

	foreach ( $events as $event ) {

		// Find out which plugin provides this event
		foreach ( achievements()->extensions as $extension ) {
			if ( ! is_a( $extension, 'DPA_Extension' ) )
				continue;

			// If this extension contains this event
			if ( array_key_exists( $event->name, $extension->get_actions() )) {
				if ( ! isset( $temp_events[$extension->get_name()] ) )
					$temp_events[$extension->get_name()] = array();

				// Store term description and ID
				$temp_events[$extension->get_name()][] = array( 'description' => $event->description, 'id' => $event->term_id );
				break;
			}
		}

	}
	$events = $temp_events;

	return apply_filters( 'dpa_get_all_events_details', $events );
}

/**
 * Called before a post is deleted; if an achievement post, we tidy up any related Progress posts.
 * 
 * This function is supplemental to the actual achievement deletion which is handled by WordPress core API functions.
 * It is used to clean up after an achievement that is being deleted.
 *
 * @param int $post_id Optional; post ID that is being deleted.
 * @since Achievements (3.0)
 */
function dpa_before_achievement_deleted( $post_id = 0 ) {
	$post_id = dpa_get_achievement_id( $post_id );
	if ( empty( $post_id ) || ! dpa_is_achievement( $post_id ) )
		return;

	do_action( 'dpa_before_achievement_deleted', $post_id );

	// An achievement is being permanently deleted, so any related Progress posts have to go, too.
	$progress = new WP_Query( array(
		'fields'         => 'id=>parent',
		'nopaging'       => true,
		'post_parent'    => $post_id,
		'post_status'    => array( dpa_get_locked_status_id(), dpa_get_unlocked_status_id() ),
		'post_type'      => dpa_get_progress_post_type(),
		'posts_per_page' => -1,
	) );

	if ( empty( $progress ) )
		return;

	foreach ( $progress->posts as $post ) 
		wp_delete_post( $post->ID, true );
}

/**
 * Handles the redeem achievement form submission.
 * 
 * Finds any achievements with the specific redemption code, and if the user hasn't already unlocked
 * that achievement, it's awarded to the user.
 *
 * @param string $action Optional. If 'dpa-redeem-achievement', handle the form submission.
 * @since Achievements (3.1)
 */
function dpa_form_redeem_achievement( $action = '' ) {
	if ( 'dpa-redeem-achievement' !== $action || ! dpa_is_user_active() )
		return;

	// Check required form values are present
	$redemption_code = isset( $_POST['dpa_code'] ) ? sanitize_text_field( stripslashes( $_POST['dpa_code'] ) ) : '';
	$redemption_code = apply_filters( 'dpa_form_redeem_achievement_code', $redemption_code );

	if ( empty( $redemption_code ) || ! dpa_verify_nonce_request( 'dpa-redeem-achievement' ) )
		return;

	// If multisite and running network-wide, switch_to_blog to the data store site
	if ( is_multisite() && dpa_is_running_networkwide() )
		switch_to_blog( DPA_DATA_STORE );

	// Find achievements that match the same redemption code
	$achievements = dpa_get_achievements( array(
		'meta_key'   => '_dpa_redemption_code',
		'meta_value' => $redemption_code,
	) );

	// Bail out early if no achievements found
	if ( empty( $achievements ) ) {
		dpa_add_error( 'dpa_redeem_achievement_nonce', __( 'That code was invalid. Try again!', 'dpa' ) );

		// If multisite and running network-wide, undo the switch_to_blog
		if ( is_multisite() && dpa_is_running_networkwide() )
			restore_current_blog();

		return;
	}

	$existing_progress = dpa_get_progress( array(
		'author' => get_current_user_id(),
	) );

	foreach ( $achievements as $achievement_obj ) {
		$progress_obj = array();

		// If we have existing progress, pass that to dpa_maybe_unlock_achievement().
		foreach ( $existing_progress as $progress ) {
			if ( $achievement_obj->ID === $progress->post_parent ) {

				// If the user has already unlocked this achievement, don't give it to them again.
				if ( dpa_get_unlocked_status_id() === $progress->post_status )
					$progress_obj = false;
				else
					$progress_obj = $progress;
	
				break;
			}
		}

		if ( false !== $progress_obj )
			dpa_maybe_unlock_achievement( get_current_user_id(), 'skip_validation', $progress_obj, $achievement_obj );
	}

	// If multisite and running network-wide, undo the switch_to_blog
	if ( is_multisite() && dpa_is_running_networkwide() )
		restore_current_blog();
}

/**
 * Has a specific user unlocked a specific achievement?
 *
 * @param int $user_id
 * @param int $achievement_id
 * @return bool True if user has unlocked the achievement
 * @since Achievements (3.4) 
 */
function dpa_has_user_unlocked_achievement( $user_id, $achievement_id ) {

	if ( ! dpa_is_user_active( $user_id ) )
		return false;

	$achievement_id = dpa_get_achievement_id( $achievement_id );
	if ( empty( $achievement_id ) || ! dpa_is_achievement( $achievement_id ) )
		return false;

	// Try to fetched an unlocked progress item for this user pair/achievement pair
	$progress = dpa_get_progress( array(
		'author'        => $user_id,
		'fields'        => 'ids',
		'no_found_rows' => true,
		'nopaging'      => true,
		'numberposts'   => 1,
		'post_parent'   => $achievement_id,
		'post_status'   => dpa_get_unlocked_status_id(),
	) );

	return apply_filters( 'dpa_has_user_unlocked_achievement', ! empty( $progress ), $progress, $user_id, $achievement_id );
}

/**
 * Updates the dpa_event taxonomy's term count.
 *
 * Mostly a copy of WordPress core's _update_post_term_count() function, but updated to work for Private posts.
 *
 * @param array $terms List of term taxonomy IDs
 * @param object $taxonomy Current taxonomy object of terms
 * @since Achievements (3.4)
 */
function dpa_update_event_term_count( $terms, $taxonomy ) {
	global $wpdb;

	$object_types = (array) $taxonomy->object_type;

	foreach ( $object_types as &$object_type )
		list( $object_type ) = explode( ':', $object_type );

	$object_types = array_unique( $object_types );

	if ( $object_types )
		$object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );

	foreach ( (array) $terms as $term ) {
		$count = 0;

		if ( $object_types ) {
			$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status IN ('publish', 'private') AND post_type IN ('" . implode( "', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );
		}

		do_action( 'edit_term_taxonomy', $term, $taxonomy );
		$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
		do_action( 'edited_term_taxonomy', $term, $taxonomy );
	}
}