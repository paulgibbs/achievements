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
 * @since Achievements (3.0)
 */
function dpa_init_inviteanyone_extension() {
	achievements()->extensions->invite_anyone = new DPA_Invite_Anyone_Extension;

	// Tell the world that the Invite Anyone extension is ready
	do_action( 'dpa_init_inviteanyone_extension' );
}
add_action( 'dpa_ready', 'dpa_init_inviteanyone_extension' );

/**
 * Extension to add Invite Anyone support to Achievements
 *
 * @since Achievements (3.0)
 */
class DPA_Invite_Anyone_Extension extends DPA_Extension {
	/**
	 * Constructor
	 *
	 * Sets up extension properties. See class phpdoc for details.
	 *
	 * @since Achievements (3.0)
	 */
	public function __construct() {

		$this->actions = array(
			'accepted_email_invite' => __( 'A new user activates their account.', 'dpa' ),
			'sent_email_invite'     => __( 'The user invites someone else to join the site.', 'dpa' ),
		);

		$this->contributors = array(
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

		$this->description     = __( "Makes BuddyPress&rsquo;s invitation features more powerful.", 'dpa' );
		$this->id              = 'invite-anyone';
		$this->image_url       = trailingslashit( achievements()->includes_url ) . 'admin/images/invite-anyone.jpg';
		$this->name            = __( 'Invite Anyone', 'dpa' );
		$this->rss_url         = 'http://teleogistic.net/feed/';
		$this->small_image_url = trailingslashit( achievements()->includes_url ) . 'admin/images/invite-anyone-small.jpg';
		$this->version         = 1;
		$this->wporg_url       = 'http://wordpress.org/plugins/invite-anyone/';

		add_filter( 'dpa_handle_event_user_id', array( $this, 'event_user_id' ), 10, 3 );
	}

	/**
 	 * For the accepted_email_invite action from Invite Anyone, get the user ID from the function
 	 * arguments as the user isn't logged in yet.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since Achievements (3.0)
	 */
	public function event_user_id( $user_id, $action_name, $action_func_args ) {
		if ( 'accepted_email_invite' !== $action_name )
			return $user_id;

		return (int) $action_func_args[0];
	}
}