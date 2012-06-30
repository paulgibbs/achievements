<?php
/**
 * Extension for Invite Anyone
 *
 * This file extends Achievements to support actions from Invite Anyone
 *
 * @package Achievements
 * @subpackage ExtensionInviteAnyone
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from Invite Anyone.
 *
 * @since 3.0
 */
function dpa_init_inviteanyone_extension() {
	achievements()->extensions->invite_anyone = new DPA_InviteAnyone_Extension;
}
add_action( 'dpa_ready', 'dpa_init_inviteanyone_extension' );

/**
 * Extension to add Invite Anyone support to Achievements
 *
 * @since 3.0
 */
class DPA_InviteAnyone_Extension extends DPA_Extension {
	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'dpa_handle_event_user_id', array( $this, 'event_user_id' ), 10, 3 );
	}

	/**
 	 * For the accepted_email_invite action from Invite Anyone, get the user ID from the function
 	 * arguments as the user isn't logged in yet.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since 3.0
	 */
	protected function event_user_id( $user_id, $action_name, $action_func_args ) {
		if ( 'accepted_email_invite' != $action_name )
			return $user_id;

		return (int) $action_func_args[0];
	}

	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_actions() {
		return array(
			'accepted_email_invite' => __( 'A new user activates their account.', 'dpa' ),
			'sent_email_invite'     => __( 'The user invites someone else to join the site.', 'dpa' ),
		);
	}

	/**
	 * Returns nested array of key/value pairs for each contributor to this plugin (name, gravatar URL, profile URL).
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_contributors() {
		return array(
			array(
				'name'         => 'Boone Gorges',
				'gravatar_url' => 'http://www.gravatar.com/avatar/9cf7c4541a582729a5fc7ae484786c0c',
				'profile_url'  => 'http://profiles.wordpress.org/blackphantom/',
			),
			array(
				'name'         => 'CUNY Academic Commons',
				'gravatar_url' => 'http://www.gravatar.com/avatar/80c3fc801559bbc7111d5e3f56ac6a4c',
				'profile_url'  => 'http://profiles.wordpress.org/cuny-academic-commons/',
			),
		);
	}


	/**
	 * Plugin description
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_description() {
		return __( "Makes BuddyPress's invitation features more powerful.", 'dpa' );
	}

	/**
	 * Absolute URL to plugin image.
	 *
	 * @return string
	 * @since 3.0
	 * @todo Add Invite Anyone logo image
	 */
	public function get_image_url() {
		return 'http://placekitten.com/772/250';
	}

	/**
	 * Plugin name
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_name() {
		return __( 'Invite Anyone', 'dpa' );
	}

	/**
	 * Absolute URL to a news RSS feed for this plugin. This may be your own website.
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_rss_url() {
		return 'http://teleogistic.net/feed/';
	}

	/**
	 * Plugin identifier
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_id() {
		return 'InviteAnyone';
	}

	/**
	 * Version number of your extension
	 *
	 * @return int
	 * @since 3.0
	 */
	public function get_version() {
		return 1;
	}

	/**
	 * Absolute URL to your plugin on WordPress.org
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_wporg_url() {
		return 'http://wordpress.org/extend/plugins/invite-anyone/';
	}
}