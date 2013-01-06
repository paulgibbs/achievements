<?php
/**
 * Achievements user template tags
 *
 * @package Achievements
 * @subpackage UserTemplate
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Output the avatar link of a user
 *
 * @param array $args See dpa_get_user_avatar_link() documentation.
 * @since Achievements (3.0)
 */
function dpa_user_avatar_link( $args = array() ) {
	echo dpa_get_user_avatar_link( $args );
}
	/**
	 * Return the avatar link of a user
	 *
	 * @param array $args This function supports these arguments:
	 *  - int $size If we're showing an avatar, set it to this size
	 *  - string $type What type of link to return; either "avatar", "name", or "both", or "url".
	 *  - int $user_id The ID for the user.
	 * @return string
	 * @since Achievements (3.0)
	 */
	function dpa_get_user_avatar_link( $args = array() ) {
		$defaults = array(
			'size'    => 50,
			'type'    => 'both',
			'user_id' => 0,
		);
		$r = dpa_parse_args( $args, $defaults, 'get_user_avatar_link' );
		extract( $r );

		// Default to current user
		if ( empty( $user_id ) && is_user_logged_in() )
			$user_id = get_current_user_id();

		// Assemble some link bits
		$user_link = array();
		$user_url  = user_trailingslashit( trailingslashit( get_author_posts_url( $user_id) ) . dpa_get_authors_endpoint() );

		// Get avatar
		if ( 'avatar' == $type || 'both' == $type )
			$user_link[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $user_url ), get_avatar( $user_id, $size ) );

		// Get display name
		if ( 'avatar' != $type )
			$user_link[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $user_url ), get_the_author_meta( 'display_name', $user_id ) );

		// Maybe return user URL only
		if ( 'url' == $type ) {
			$user_link = $user_url;

		// Otherwise piece together the link parts and return
		} else {
			$user_link = join( '&nbsp;', $user_link );
		}

		return apply_filters( 'dpa_get_user_avatar_link', $user_link, $args );
	}
