<?php
/**
 * Achievements user and leaderboard template tags
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

		// BuddyPress
		if ( dpa_integrate_into_buddypress() ) {
			$user_url = user_trailingslashit( bp_core_get_user_domain( $user_id ) . dpa_get_authors_endpoint() );

		// WordPress
		} else {
			$user_url = user_trailingslashit( trailingslashit( get_author_posts_url( $user_id ) ) . dpa_get_authors_endpoint() );

			/**
			 * Multisite, running network-wide.
			 *
			 * When this function is used by the "unlocked achievement" popup, if multisite + running network-wide + and not subdomains,
			 * we'll have already done switch_to_blog( DPA_ROOT_BLOG ) by the time that this function is called. This makes inspecting
			 * the current site ID, and is_main_site(), both useless as the globals will have already been changed.
			 *
			 * We need to find out if the user is likely to be on the "main site" in this situation. so we can modify our link.
			 * The main site's author URLs are prefixed with "/blog". We do this by inspecting the _wp_switched_stack global.
			 *
			 * I think this solution might result in a wrong link in multi-network configuration, or if the main site has been set
			 * to something non-default, but these are edge-cases for now.
			 */
			if ( is_multisite() && ! is_subdomain_install() && dpa_is_running_networkwide() && DPA_DATA_STORE === 1 && ! empty( $GLOBALS['_wp_switched_stack'] ) ) {
				$last_element = count( $GLOBALS['_wp_switched_stack'] ) - 1;

				if ( isset( $GLOBALS['_wp_switched_stack'][$last_element] ) && $GLOBALS['_wp_switched_stack'][$last_element] != 1 )
					$user_url = str_replace( home_url(), home_url() . '/blog', $user_url );
			}
		}

		// Get avatar
		if ( 'avatar' === $type || 'both' === $type )
			$user_link[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $user_url ), get_avatar( $user_id, $size ) );

		// Get display name
		if ( 'avatar' !== $type )
			$user_link[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $user_url ), get_the_author_meta( 'display_name', $user_id ) );

		// Maybe return user URL only
		if ( 'url' === $type ) {
			$user_link = $user_url;

		// Otherwise piece together the link parts and return
		} else {
			$user_link = join( '&nbsp;', $user_link );
		}

		return apply_filters( 'dpa_get_user_avatar_link', $user_link, $args );
	}
