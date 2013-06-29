<?php
/**
 * Extension for BuddyPress Courseware
 *
 * This file extends Achievements to support actions from BuddyPress ScholarPress Courseware.
 *
 * @package Achievements
 * @subpackage ExtensionBuddyPressCourseware
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from BuddyPress ScholarPress Courseware.
 *
 * @since Achievements (3.0)
 */
function dpa_init_scholarpress_extension() {
	achievements()->extensions->bpscholarpresscourseware = new DPA_BuddyPress_Courseware_Extension;

	// Tell the world that the ScholarPress extension is ready
	do_action( 'dpa_init_scholarpress_extension' );
}
add_action( 'dpa_ready', 'dpa_init_scholarpress_extension' );

/**
 * Extension to add BuddyPress ScholarPress Courseware support to Achievements
 *
 * @since Achievements (3.0)
 */
class DPA_BuddyPress_Courseware_Extension extends DPA_Extension {
	/**
	 * Constructor
	 *
	 * Sets up extension properties. See class phpdoc for details.
	 *
	 * @since Achievements (3.0)
	 */
	public function __construct() {

		$this->actions = array(
			'courseware_new_teacher_added'   => __( 'The user is added as a teacher', 'dpa' ),
			'courseware_grade_added'         => __( 'A grade is given to the user', 'dpa' ),
			'courseware_grade_updated'       => __( "A user&rsquo;s grade is updated", 'dpa' ),
			'courseware_assignment_added'    => __( 'The user creates a new assignment', 'dpa' ),
			'courseware_lecture_added'       => __( 'The user creates a new lecture', 'dpa' ),
			'courseware_response_added'      => __( 'The user adds a response to an assignment', 'dpa' ),
			'courseware_schedule_activity'   => __( 'The user creates a new schedule', 'dpa' ),
		);

		$this->contributors = array(
			array(
				'name'         => 'Stas Sușcov',
				'gravatar_url' => 'http://www.gravatar.com/avatar/39639fde05c65fae440b775989e55006',
				'profile_url'  => 'http://profiles.wordpress.org/sushkov/',
			),
			array(
				'name'         => 'Jeremy Boggs',
				'gravatar_url' => 'http://www.gravatar.com/avatar/2a062a10cb94152f4ab3daf569af54c3',
				'profile_url'  => 'http://profiles.wordpress.org/jeremyboggs/',
			),
			array(
				'name'         => 'Boone Gorges',
				'gravatar_url' => 'http://www.gravatar.com/avatar/9cf7c4541a582729a5fc7ae484786c0c',
				'profile_url'  => 'http://profiles.wordpress.org/boonebgorges/',
			),
			array(
				'name'         => 'John James Jacoby',
				'gravatar_url' => 'http://www.gravatar.com/avatar/81ec16063d89b162d55efe72165c105f',
				'profile_url'  => 'http://profiles.wordpress.org/johnjamesjacoby/',
			),
			array(
				'name'         => 'Chelsea Otakan',
				'gravatar_url' => 'http://www.gravatar.com/avatar/0231c6b98cf90defe76bdad0c3c66acf',
				'profile_url'  => 'http://profiles.wordpress.org/chexee/',
			),
		);

		$this->description     = __( 'A Learning Management System for BuddyPress.', 'dpa' );
		$this->id              = 'buddypress-courseware';
		$this->image_url       = trailingslashit( achievements()->includes_url ) . 'admin/images/buddypress-courseware.png';
		$this->name            = __( 'BuddyPress Courseware', 'dpa' );
		$this->rss_url         = 'http://feeds.nerd.ro/stas/';
		$this->small_image_url = trailingslashit( achievements()->includes_url ) . 'admin/images/buddypress-courseware-small.png';
		$this->version         = 1;
		$this->wporg_url       = 'http://wordpress.org/plugins/buddypress-courseware/';

		add_filter( 'dpa_handle_event_user_id', array( $this, 'event_user_id' ), 10, 3 );
	}

	/**
 	 * For some actions from ScholarPress, get the user ID from the function arguments.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since Achievements (3.0)
	 */
	public function event_user_id( $user_id, $action_name, $action_func_args ) {
		// Only deal with events added by this extension.
		if ( ! in_array( $action_name, array( 'courseware_new_teacher_added', 'courseware_new_teacher_removed', 'courseware_grade_added', 'courseware_grade_updated', ) ) )
			return $user_id;

		// User added/removed as a teacher
		if ( in_array( $action_name, array( 'courseware_new_teacher_added', 'courseware_new_teacher_removed', ) ) )
			return $action_func_args[0];  // $bp->displayed_user->id

		// User has a grade added/updated
		elseif ( in_array( $action_name, array( 'courseware_grade_added', 'courseware_grade_updated', ) ) )
			return $action_func_args[0]['grade']['uid'];
	}
}