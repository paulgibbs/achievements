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
	 * @synopsis --user=<id|login> [--format=<table|csv|json>]
	 */
	public function _list( $args, $assoc_args ) {
		global $wpdb;

		$defaults = array( 
			'format' => 'table',
			'user'   => 0,
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
}

WP_CLI::add_command( 'achievements-user', 'DPA_WPCLI_Achievements_Users_Command' );