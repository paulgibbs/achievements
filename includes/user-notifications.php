<?php
/**
 * User notification functions
 *
 * @package Achievements
 * @subpackage UserNotifications
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sends a notification to a user when they unlock an achievement.
 *
 * @param object $achievement_obj The Achievement object to send a notification for.
 * @param int $user_id ID of the user who unlocked the achievement.
 * @param int $progress_id The Progress object's ID.
 * @since 3.0
 */
function dpa_send_notification( $achievement_obj, $user_id, $progress_id ) {
	// Let other plugins easily bypass sending notifications.
	if ( ! apply_filters( 'dpa_maybe_send_notification', true, $achievement_obj, $user_id, $progress_id ) )
		return;

	// Create a notification for this user/achievement.
	dpa_new_notification( $user_id, $achievement_obj->ID );

	// Tell other plugins that we've just added a new notification
	do_action( 'dpa_send_notification', $achievement_obj, $user_id, $progress_id );
}

/**
 * Print any notifications for the current user to the page footer.
 * 
 * @since 3.0
 */
function dpa_print_notifications() {
	// If user's not logged in or inside the WordPress Admin, bail out.
	if ( ! is_user_logged_in() || is_admin() )
		return;

	// Get current notifications
	$achievements = array();
	$notifications = dpa_get_user_notifications();

	// If we're viewing an achievement, don't show a notification for it
	if ( ! empty( $notifications ) && dpa_is_single_achievement() && isset( $notifications[get_the_ID()] ) ) {
		unset( $notifications[get_the_ID()] );

		// Save the user's notifications
		dpa_update_user_notifications( $notifications );
	}

	// Allow other plugins to filters the notifications before we display then
	$notifications = apply_filters( 'dpa_print_notifications', $notifications );

	// Check we actually have notifications to display
	if ( ! empty( $notifications ) ) :
		echo '<div id="dpa-notifications" aria-live="polite">';

		foreach ( $notifications as $achievement_id => $n_message ) :
			$n_message = apply_filters( 'dpa_get_achievement_title', $n_message, $achievement_id );
			$n_url     = esc_url( home_url() . '/?p=' . $achievement_id );
	?>

		<div class="dpa-notification-message">
			<a class="close" title="<?php esc_attr_e( 'Hide notifications', 'dpa' ); ?>" alt="<?php esc_attr_e( 'Hide notifications', 'dpa' ); ?>"><?php /* translators: click the "X" to close the panel - like on Windows computers. */ _e( 'x', 'dpa' ); ?></a>
			<p><?php printf( __( '<span>You\'ve unlocked an achievement!</span> <a href="%1$s">%2$s</a>', 'dpa' ), $n_url, $n_message ); ?></p>
		</div>

	<?php
		endforeach;
		echo '</div>';
	endif;
}

/**
 * Add a new notification for the specified user
 *
 * @param int $user_id int Optional. The ID for the user.
 * @param int $post_id int Optional. The post ID of the achievement to clear the notification for.
 * @since 3.0
 */
function dpa_new_notification( $user_id = 0, $post_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// Default to current post
	if ( empty( $post_id ) && is_single() )
		$post_id = get_the_ID();

	// No user or post ID to check
	if ( empty( $user_id ) || empty( $post_id ) )
		return;

	// Get existing notifications
	$notifications = dpa_get_user_notifications( $user_id );

	// Add the new notification: key = post ID, value = name of the achievement.
	$notifications[$post_id] = get_post( $post_id )->post_title;
	dpa_update_user_notifications( $notifications, $user_id );

	// Tell other plugins that we've just created a new notification
	do_action( 'dpa_new_notification', $user_id, $post_id );
}

/**
 * Clears any notifications for the specified user for the specified achievement.
 *
 * @param int $post_id int Optional. The post ID of the achievement to clear the notification for.
 * @param int $user_id int Optional. The ID for the user.
 * @since 3.0
 */
function dpa_clear_notification( $post_id = 0, $user_id = 0 ) {
	// Default to current user
	if ( empty( $user_id ) && is_user_logged_in() )
		$user_id = get_current_user_id();

	// Default to current post
	if ( empty( $post_id ) && is_single() )
		$post_id = get_the_ID();

	// No user or post ID to check
	if ( empty( $user_id ) || empty( $post_id ) )
		return;

	// The notifications array is keyed by the achievement (post) ID.
	$notifications = dpa_get_user_notifications( $user_id );

	// Is there a notification to clear?
	if ( ! isset( $notifications[$post_id] ) )
		return;

	// Clear the notification
	unset( $notifications[$post_id] );
	dpa_update_user_notifications( $notifications, $user_id );

	// Tell other plugins that we've just cleared other plugins
	do_action( 'dpa_clear_notification', $post_id, $user_id );
}