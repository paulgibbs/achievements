<?php
/**
 * Integrates Achievements into BuddyPress' Activity component.
 *
 * @package Achievements
 * @subpackage BuddyPressClasses
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DPA_BuddyPress_Activity' ) ) :
/**
 * Integrates Achievements into BuddyPress' Activity component.
 *
 * This class requires BuddyPress.
 *
 * @since Achievements (3.2)
 */
class DPA_BuddyPress_Activity {

	/**
	 * The name of the BuddyPress component, used in activity streams
	 *
	 * @var string
	 */
	protected $component = '';

	/**
	 * Achievement unlocked action
	 *
	 * @var string
	 */
	protected $achievement_unlocked = '';


	/**
	 * Constructor. Begins the process to integrate Achievements into BuddyPress' Activity component.
	 *
	 * @since Achievements (3.2)
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Set extension variables
	 *
	 * @since Achievements (3.2)
	 */
	private function setup_globals() {
		$this->component            = 'achievements';
		$this->achievement_unlocked = 'dpa_unlocked';
	}

	/**
	 * Setup the actions
	 *
	 * @since Achievements (3.2)
	 */
	private function setup_actions() {

		// Register the activity stream actions
		add_action( 'bp_register_activity_actions',      array( $this, 'register_activity_actions' )        );

		// Hook into the achievement unlocked action
		add_action( 'dpa_unlock_achievement',            array( $this, 'achievement_unlocked'      ), 10, 3 );

		// Hook into the achievement deleted action
		add_action( 'dpa_before_achievement_deleted',    array( $this, 'achievement_deleted'       ), 10, 1 );

		// Append filters in site wide activity streams
		add_action( 'bp_activity_filter_options',        array( $this, 'activity_filter_options'   ), 10    );

		// Append filters in single member activity streams
		add_action( 'bp_member_activity_filter_options', array( $this, 'activity_filter_options'   ), 10    );

		// Append filters in single group activity streams
		add_action( 'bp_group_activity_filter_options',  array( $this, 'activity_filter_options'   ), 10    );
	}

	/**
	 * Register our activity actions with BuddyPress
	 *
	 * @since Achievements (3.2)
	 */
	public function register_activity_actions() {
		bp_activity_set_action( $this->component, $this->achievement_unlocked, __( 'Unlocked achievements', 'dpa' ) );
	}

	/**
	 * Wrapper for adding Achievements actions to the BuddyPress activity stream.
	 *
	 * @param array $args Optional. Array of arguments for bp_activity_add().
	 * @return int|bool Activity ID if successful, false if not.
	 * @since Achievements (3.2)
	 */
	private function record_activity( $args = array() ) {
		$activity = dpa_parse_args( $args, array(
			'action'            => '',
			'component'         => $this->component,
			'content'           => '',
			'hide_sitewide'     => false,
			'id'                => false,
			'item_id'           => false,
			'primary_link'      => '',
			'recorded_time'     => bp_core_current_time(),
			'secondary_item_id' => false,
			'type'              => false,
			'user_id'           => get_current_user_id(),
		), 'record_activity' );

		return bp_activity_add( $activity );
	}

	/**
	 * Wrapper for deleting Achievements actions from the BuddyPress activity stream.
	 *
	 * @param array $args Array of arguments for bp_activity_add().
	 * @return int|bool Activity ID if successful, false if not.
	 * @since Achievements (3.2)
	 */
	public function delete_activity( $args = array() ) {
		$activity = dpa_parse_args( $args,
		array(
			'component'         => $this->component,
			'item_id'           => false,
			'secondary_item_id' => false,
			'type'              => false,
			'user_id'           => false,
		), 'delete_activity' );

		bp_activity_delete_by_item_id( $activity );
	}

	/**
	 * Append Achievements' options to activity filter select box
	 *
	 * @since Achievements (3.2)
	 */
	function activity_filter_options() {
	?>

		<option value="<?php echo esc_attr( $this->achievement_unlocked ); ?>"><?php echo esc_html_x( 'Achievements', 'Unlocked achievements filter label', 'dpa' ); ?></option>

	<?php
	}

	/**
	 * Record an activity stream entry when a user unlocks an achievement
	 *
	 * @param WP_Post $achievement Achievement post object
	 * @param int $user_id
	 * @param int $progress_id
	 * @since Achievements (3.2)
	 */
	public function achievement_unlocked( $achievement, $user_id, $progress_id ) {

		// Bail if user is not active
		if ( ! dpa_is_user_active( $user_id ) )
			return;

		// Achievement details
		$achievement_permalink = dpa_get_achievement_permalink( $achievement->ID );
		$achievement_title     = get_post_field( 'post_title', $achievement->ID, 'raw' );
		$achievement_link      = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $achievement_permalink ), esc_html( $achievement_title ) );

		// Activity action & text
		$activity_text    = sprintf( __( '%1$s unlocked the achievement: %2$s', 'dpa' ), bp_core_get_userlink( $user_id ), $achievement_link );
		$activity_action  = apply_filters( 'dpa_activity_achievement_unlocked', $activity_text, $achievement->ID, $user_id, $progress_id );

		// Record the activity
		$activity = array(
			'action'            => $activity_action,
			'content'           => '',
			'item_id'           => $achievement->ID,
			'hide_sitewide'     => false,
			'primary_link'      => $achievement_permalink,
			'type'              => $this->achievement_unlocked,
			'user_id'           => $user_id,
		);
		$activity_id = $this->record_activity( $activity );
	}

	/**
	 * Delete Achievements' activity stream entries when an achievement is deleted.
	 *
	 * @param int $achievement_id Achievement post ID being deleted
	 * @since Achievevements (3.2)
	 */
	public function achievement_deleted( $achievement_id ) {

		// "Achievement unlock" activities
		bp_activity_delete( array(
			'component' => $this->component,
			'item_id'   => $achievement_id,
			'type'      => $this->achievement_unlocked,
		) );
	}
}
endif;
