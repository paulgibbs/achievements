<?php
/**
 * WP-CLI commands for Achievements and users (i.e. progress)
 * 
 * See http://wp-cli.org/ for more info
 *
 * @package Achievements
 * @subpackage WPCLI
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WP-CLI commands for Achievements and users (i.e. progress)
 *
 * @since Achievements (3.3)
 */
class DPA_WPCLI_Achievements_Users_Command extends WP_CLI_Command {

	/**
	 * Names of database columns that we'll retrieve
	 *
	 * @since Achievements (3.3)
	 */
	public $fields = array(
		'ID',
		'post_title',
	);


	/**
	 * List all unlocked achievements for the specified user
	 *
	 * @since Achievements (3.3)
	 * @subcommand list
	 * @synopsis --user_id=<id> [--format=<table|csv|json>]
	 */
	public function _list( $args, $assoc_args ) {
		global $wpdb;

		$defaults = array( 
			'format' => 'table',
		);
		$assoc_args = array_merge( $defaults, $assoc_args );

		if ( ! $assoc_args['user_id'] || ! $user = get_userdata( $assoc_args['user_id'] ) )
			WP_CLI::error( 'Invalid User ID specified.' );

		// Get the progress for this user
		$achievement_ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_parent FROM {$wpdb->posts} WHERE post_type = %s AND post_author = %d", dpa_get_progress_post_type(), $user->ID ) );
		if ( empty( $achievement_ids ) )
			WP_CLI::error( sprintf( 'User ID %d has not unlocked any achievements.', $user->ID ) );

		$achievement_ids   = wp_parse_id_list( $achievement_ids );
		$achievement_count = count( $achievement_ids );
		$achievement_ids   = implode( ',', $achievement_ids );

		// Get the achievements
		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE ID in ({$achievement_ids}) AND post_type = %s AND post_status = %s ORDER BY post_title ASC", dpa_get_achievement_post_type(), 'publish' ) );

		if ( empty( $posts ) )
			WP_CLI::error( sprintf( "No achievements unlocked by User ID %d have been found. This shouldn't happen.", $user->ID ) );

		WP_CLI::success( sprintf( '%d achievements have been unlocked by User ID %d:', $achievement_count, $user->ID ) );
		\WP_CLI\utils\format_items( $assoc_args['format'], $posts, $this->fields );
	}

	/**
	 * Remove an unlocked achievement from a user
	 *
	 * @alias remove
	 * @since Achievements (3.4)
	 * @synopsis --user_id=<id> --achievement=<postname>
	 */
	public function revoke( $args, $assoc_args ) {

		if ( ! $assoc_args['user_id'] || ! $user = get_userdata( $assoc_args['user_id'] ) )
			WP_CLI::error( 'Invalid User ID specified.' );

		// Get the achievement ID
		$achievement_id = $this->_get_achievement_id_by_post_name( $assoc_args['achievement'] );
		if ( ! $achievement_id )
			WP_CLI::error( sprintf( 'Achievement ID not found for post_name: %1$s', $achievement_id ) );

		// Has the user already been awarded this achievement?
		if ( dpa_has_user_unlocked_achievement( $assoc_args['user_id'], $achievement_id ) ) {
			dpa_delete_achievement_progress( $achievement_id, $assoc_args['user_id'] );

			WP_CLI::success( sprintf( 'Achievement ID %1$s has been revoked from User ID %2$s', $achievement_id, $assoc_args['user_id'] ) );
		} else {
			WP_CLI::warning( sprintf( 'User ID %1$s has not unlocked achievement ID %2$s', $assoc_args['user_id'], $achievement_id ) );
			return;
		}
	}

	/**
	 * Award an achievement to a user
	 *
	 * @alias add
	 * @since Achievements (3.4)
	 * @synopsis --user_id=<id> --achievement=<postname>
	 */
	public function award( $args, $assoc_args ) {

		if ( ! $assoc_args['user_id'] || ! $user = get_userdata( $assoc_args['user_id'] ) )
			WP_CLI::error( 'Invalid User ID specified.' );

		// Get the achievement ID
		$achievement_id = $this->_get_achievement_id_by_post_name( $assoc_args['achievement'] );
		if ( ! $achievement_id )
			WP_CLI::error( sprintf( 'Achievement ID not found for post_name: %1$s', $achievement_id ) );

		// If the user has already unlocked this achievement, bail out.
		if ( dpa_has_user_unlocked_achievement( $assoc_args['user_id'], $achievement_id ) ) {
			WP_CLI::warning( sprintf( 'User ID %1$s has already unlocked achievement ID %2$s', $assoc_args['user_id'], $achievement_id ) );
			return;
		}

		$achievement_obj = dpa_get_achievements( array(
			'no_found_rows' => true,
			'nopaging'      => true,
			'numberposts'   => 1,
			'p'             => $achievement_id,
		) );
		$achievement_obj = $achievement_obj[0];

		// Find any still-locked progress for this achievement for this user, as dpa_maybe_unlock_achievement() needs it.
		$progress_obj = dpa_get_progress( array(
			'author'        => $assoc_args['user_id'],
			'no_found_rows' => true,
			'nopaging'      => true,
			'numberposts'   => 1,
			'post_status'   => dpa_get_locked_status_id(),
		) );

		if ( empty( $progress_obj ) )
			$progress_obj = array();

		// Award the achievement
		dpa_maybe_unlock_achievement( $assoc_args['user_id'], 'skip_validation', $progress_obj, $achievement_obj );
		WP_CLI::success( sprintf( 'Achievement ID %1$s has been awarded to User ID %2$s', $achievement_id, $assoc_args['user_id'] ) );
	}

	/**
	 * Helper methods for the CLI commands to avoid code duplication
	 */

	/**
	 * Get an achievement's ID from the specified $post_name.
	 *
	 * @param string $post_name
	 * @return int Achievement ID
	 * @since Achievements (3.4)
	 */
	protected function _get_achievement_id_by_post_name( $post_name ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s LIMIT 1", dpa_get_achievement_post_type(), $post_name ) );

		return absint( $achievement_id );
	}
}

WP_CLI::add_command( 'achievements-user', 'DPA_WPCLI_Achievements_Users_Command' );
