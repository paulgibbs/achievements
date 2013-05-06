<?php
/**
 * WP-CLI commands for Achievements
 * 
 * See http://wp-cli.org/ for more info
 *
 * @package Achievements
 * @subpackage WPCLI
 */
class DPA_WPCLI_Command extends WP_CLI_Command {

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
			'filter' => '',
			'format' => 'table',
		);
		$assoc_args = array_merge( $defaults, $assoc_args );

		// Get the posts
		$posts = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s ORDER BY post_date DESC", dpa_get_achievement_post_type(), 'publish' ) );

		if ( empty( $posts ) )
			WP_CLI::error( 'No published achievements found.' );

		\WP_CLI\utils\format_items( $assoc_args['format'], $this->fields, $posts );
	}
}

WP_CLI::add_command( 'achievements', 'DPA_WPCLI_Command' );