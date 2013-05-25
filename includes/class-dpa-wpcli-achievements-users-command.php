<?php
/**
 * WP-CLI commands for Achievements and users (i.e. progress)
 * 
 * See http://wp-cli.org/ for more info
 *
 * @package Achievements
 * @subpackage WPCLI
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

		$achievement_count = count( $achievement_ids );
		$achievement_ids   = implode( ',', array_unique( $achievement_ids ) );

		// Get the achievements
		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE ID in ({$achievement_ids}) AND post_type = %s AND post_status = %s ORDER BY post_title ASC", dpa_get_achievement_post_type(), 'publish' ) );
		if ( empty( $posts ) )
			WP_CLI::error( sprintf( "No achievements unlocked by User ID %d have been found. This shouldn't happen.", $user->ID ) );

		WP_CLI::success( sprintf( '%d achievements have been unlocked by User ID %d:', $achievement_count, $user->ID ) );
		\WP_CLI\utils\format_items( $assoc_args['format'], $this->fields, $posts );
	}

	/**
	 * Remove an unlocked achievement from a user
	 *
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

		if ( dpa_has_user_unlocked_achievement( $assoc_args['user_id'], $achievement_id ) ) {
			dpa_delete_achievement_progress( $achievement_id, $assoc_args['user_id'] );

			WP_CLI::success( sprintf( 'Achievement ID %1$s has been revoked from User ID %2$s', $achievement_id, $assoc_args['user_id'] ) );
		} else {
			WP_CLI::warning( sprintf( 'User ID %1$s has not unlocked achievement ID %2$s', $assoc_args['user_id'], $achievement_id ) );
		}
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

		return $achievement_id;
	}
}

WP_CLI::add_command( 'achievements-user', 'DPA_WPCLI_Achievements_Users_Command' );
