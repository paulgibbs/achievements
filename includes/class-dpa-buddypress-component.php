<?php
/**
 * Achievements' BuddyPress integration
 *
 * Achievements and BuddyPress are designed to connect together seamlessly and this makes that happen.
 *
 * @package Achievements
 * @subpackage BuddyPressClasses
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DPA_BuddyPress_Component' ) ) :
/**
 * Loads achievements component for BuddyPress
 *
 * @since Achievements (3.2)
 */
class DPA_BuddyPress_Component extends BP_Component {

	/**
	 * Start the achievements component creation process
	 *
	 * @since Achievements (3.2)
	 */
	public function __construct() {
		parent::start(
			'achievements',
			__( 'Achievements', 'dpa' ),
			BP_PLUGIN_DIR
		);

		$this->includes();
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Include BuddyPress classes and functions
	 *
	 * @since Achievements (3.2)
	 */
	public function includes() {

		// Members modifications
		//require( bbpress()->includes_dir . 'extend/buddypress/members.php' );

		// BuddyPress Activity Extension class
		//if ( bp_is_active( 'activity' ) )
		//	require( bbpress()->includes_dir . 'extend/buddypress/activity.php' );
	}

	/**
	 * Setup globals
	 *
	 * @since Achievements (3.2)
	 */
	public function setup_globals() {
		parent::setup_globals( array(
			'has_directory' => true,
			'root_slug'     => dpa_get_achievement_slug(),
			'slug'          => dpa_get_achievement_slug(),
		) );
	}

	/**
	 * Setup the actions
	 *
	 * @since Achievements (3.2)
	 * @link http://bbpress.trac.wordpress.org/ticket/2176
	 */
	public function setup_actions() {
		//add_action( 'bp_init', array( $this, 'setup_components' ), 7 );
		parent::setup_actions();
	}

	/**
	 * Instantiate classes for BuddyPress integration
	 *
	 * @since Achievements (3.2)
	 */
	public function setup_components() {
		bbpress()->extend->buddypress->members = new BBP_BuddyPress_Members;

		// Create new activity class
		if ( bp_is_active( 'activity' ) )
			bbpress()->extend->buddypress->activity = new BBP_BuddyPress_Activity;
	}

	/**
	 * Setup BuddyBar navigation
	 *
	 * @since Achievements (3.2)
	 */
	public function setup_nav() {

		// Stop if there is no user displayed or logged in
		if ( ! is_user_logged_in() && ! bp_displayed_user_id() )
			return;

		// Add to the main navigation
		$main_nav = array(
			'default_subnav_slug' => '',
			'item_css_id'         => $this->id,
			'name'                => __( 'Achievements', 'dpa' ),
			'position'            => 90,
			'screen_function'     => 'bbp_member_forums_screen_topics',
			'slug'                => $this->slug,
		);

		// Determine user to use
		if ( bp_displayed_user_id() )
			$user_domain = bp_displayed_user_domain();
		elseif ( bp_loggedin_user_domain() )
			$user_domain = bp_loggedin_user_domain();
		else
			return;

		// Favorite topics
		$sub_nav = array(
			'item_css_id'     => 'achievements',
			'name'            => _x( 'All', 'all achievements', 'bbpress' ),
			'parent_slug'     => $this->slug,
			'parent_url'      => trailingslashit( $user_domain . $this->slug ),
			'position'        => 20,
			'screen_function' => 'bbp_member_forums_screen_topics',
			'slug'            => '',
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Sets up the title for pages and <title>
	 *
	 * @since Achievements (3.2)
	 */
	public function setup_title() {
		/*$bp = buddypress();

		// Adjust title based on view
		if ( bp_is_forums_component() ) {

			if ( bp_is_my_profile() ) {
				$bp->bp_options_title = __( 'Forums', 'bbpress' );

			} elseif ( bp_is_user() ) {
				$bp->bp_options_avatar = bp_core_fetch_avatar( array(
					'item_id' => bp_displayed_user_id(),
					'type'    => 'thumb'
				) );

				$bp->bp_options_title = bp_get_displayed_user_fullname();
			}
		}
*/
		parent::setup_title();
	}
}
endif;
