<?php
/**
 * WP-CLI commands for Achievements
 * 
 * See http://wp-cli.org/ for more info
 *
 * @package Achievements
 * @subpackage WPCLI
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WP-CLI commands for Achievements
 *
 * @since Achievements (3.3)
 */
class DPA_WPCLI_Achievements_Command extends WP_CLI_Command {

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
	 * List all achievements
	 *
	 * @since Achievements (3.3)
	 * @subcommand list
	 * @synopsis [--format=<table|csv|json>]
	 */
	public function _list( $args, $assoc_args ) {
		global $wpdb;

		$defaults = array(
			'format' => 'table',
		);
		$assoc_args = array_merge( $defaults, $assoc_args );

		// Get the posts
		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s ORDER BY post_title ASC", dpa_get_achievement_post_type(), 'publish' ) );

		if ( empty( $posts ) )
			WP_CLI::error( 'No published achievements found.' );

		\WP_CLI\utils\format_items( $assoc_args['format'], $posts, $this->fields );
	}
}

WP_CLI::add_command( 'achievements', 'DPA_WPCLI_Achievements_Command' );