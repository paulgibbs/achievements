<?php
/**
 * Extension for BuddyPress ScholarPress Courseware
 *
 * This file extends Achievements to support actions from BuddyPress ScholarPress Courseware.
 *
 * @package Achievements
 * @subpackage ExtensionScholarPress
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extends Achievements to support actions from BuddyPress ScholarPress Courseware.
 *
 * @since 3.0
 */
function dpa_init_scholarpress_extension() {
	achievements()->extensions->scholarpress = new DPA_ScholarPress_Extension;
}
add_action( 'dpa_ready', 'dpa_init_scholarpress_extension' );

class DPA_ScholarPress_Extension extends DPA_CPT_Extension {
	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'dpa_handle_event_user_id', array( $this, 'event_user_id' ), 10, 3 );
	}

	/**
 	 * For some actions from ScholarPress, get the user ID from the function arguments.
	 *
	 * @param int $user_id
	 * @param string $action_name
	 * @param array $action_func_args The action's arguments from func_get_args().
	 * @return int|false New user ID or false to skip any further processing
	 * @since 3.0
	 */
	protected function event_user_id( $user_id, $action_name, $action_func_args ) {
		// Only deal with events added by this extension.
		if ( ! in_array( $action_name, array( 'courseware_new_teacher_added', 'courseware_new_teacher_removed', 'courseware_grade_added', 'courseware_grade_updated', ) ) )
			return $user_id;

		// User added/removed as a teacher
		if ( in_array( $action_name, array( 'courseware_new_teacher_added', 'courseware_new_teacher_removed', ) ) ) {
			$user_id = $action_func_args[0];  // $bp->displayed_user->id

		// User has a grade added/updated
		} elseif ( in_array( $action_name, array( 'courseware_grade_added', 'courseware_grade_updated', ) ) ) {
			$user_id = $action_func_args[0]['grade']['uid'];
		}

		return (int) $user_id;
	}

	/**
	 * Returns details of actions from this plugin that Achievements can use.
	 *
	 * @return array
	 * @since 3.0
	 */
	public function get_actions() {
		return array(
			'courseware_new_teacher_added'   => __( 'The user is added as a new teacher', 'dpa' ),
			'courseware_new_teacher_removed' => __( 'The user is removed as a teacher', 'dpa' ),
			'courseware_grade_added'         => __( 'The user has a grade added', 'dpa' ),
			'courseware_grade_updated'       => __( 'The user has an existing grade updated', 'dpa' ),
			'courseware_assignment_added'    => __( 'The user adds a new assignment', 'dpa' ),
			'courseware_lecture_added'       => __( 'The user adds a new lecture', 'dpa' ),
			'courseware_response_added'      => __( 'The user adds a response to an assignment', 'dpa' ),
			'courseware_schedule_activity'   => __( 'The user adds a new schedule', 'dpa' ),
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
	}

	/**
	 * Plugin description
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_description() {
		return __( 'A Learning Management System for BuddyPress.', 'dpa' );
	}

	/**
	 * Absolute URL to plugin image.
	 *
	 * @return string
	 * @since 3.0
	 * @todo Add ScholarPress logo image
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
		return __( 'BuddyPress ScholarPress Courseware', 'dpa' );
	}

	/**
	 * Absolute URL to a news RSS feed for this plugin. This may be your own website.
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_rss_url() {
		return 'http://feeds.nerd.ro/stas/';
	}

	/**
	 * Plugin identifier
	 *
	 * @return string
	 * @since 3.0
	 */
	public function get_id() {
		return 'BPScholarPressCourseware';
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
		return 'http://wordpress.org/extend/plugins/buddypress-courseware/';
	}
}