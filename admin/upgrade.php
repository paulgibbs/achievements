<?php
/**
 * Install/upgrade routines
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage upgrade
 */

/**
 * Setup the Achievements updater
 *
 * @since 3.0
 */
function dpa_setup_updater() {
	// Are we running an old version of Achievements?
	if ( dpa_do_update() ) {

		// Bump the version
		dpa_version_bump();

		// Run the deactivation function to wipe roles, caps, and rewrite rules
		dpa_deactivate();

		// Run the activation function to reset roles, caps, and rewrite rules
		dpa_activate();
	}
}

/**
 * Update the database to the latest version. Manages plugin install and upgrade.
 *
 * @since 3.0
 */
function dpa_version_bump() {
	$new_version = dpa_get_db_version();

	if ( empty( $new_version ) )
		$new_version = 0;

	if ( $new_version > 0 && $new_version <= 27 ) {  // CPT introduced in 3.0 (DB version = 30)
		// @todo add_action( 'init', 'dpa_upgrade_to_cpt' );
		$new_version = 28;
	}

	for ( $i = $new_version; $i <= ACHIEVEMENTS_DB_VERSION; $i++ ) {
		switch ( $i ) {
			default:
			case 0:
			break;

			// 3.0
			case 30:
				dpa_install_3_dot_0();
			break;
		}
	}

	update_site_option( 'achievements-db-version', ACHIEVEMENTS_DB_VERSION );
}

/**
 * Install version 3.0 of Achievements
 *
 * @global wpdb $wpdb WordPress database object
 * @since 3.0
 */
function dpa_install_3_dot_0() {
	$user_id = get_current_user_id();

	// DPA_Actions taxonomy
	$actions   = array();
	$actions[] = array( 'blog',            'comment_post',                         __( 'The user writes a comment on a post or page.', 'dpa' ),                             0 );
	$actions[] = array( 'blog',            'draft_to_publish',                     __( 'The user publishes a post or page.', 'dpa' ),                                       0 );
	$actions[] = array( 'members',         'friends_friendship_requested',         __( 'The user sends a friendship request to someone.', 'dpa' ),                          0 );
	$actions[] = array( 'groups',          'groups_invite_user',                   __( 'The user invites someone to join a group.', 'dpa' ),                                1 );
	$actions[] = array( 'groups',          'groups_join_group',                    __( 'The user joins a group.', 'dpa' ),                                                  1 );
	$actions[] = array( 'groups',          'groups_promoted_member',               __( 'The user promotes a group member to a moderator or administrator.', 'dpa' ),        1 );
	$actions[] = array( 'messaging',       'messages_message_sent',                __( 'The user sends or replies to a private message.', 'dpa' ),                          0 );
	$actions[] = array( 'profile',         'xprofile_updated_profile',             __( 'The user updates their profile.', 'dpa' ),                                          0 );
	$actions[] = array( 'blog',            'bp_core_activated_user',               __( 'A new user activates their account.', 'dpa' ),                                      0 );
	$actions[] = array( 'profile',         'xprofile_avatar_uploaded',             __( "The user changes their profile's avatar.", 'dpa' ),                                 0 );
	$actions[] = array( 'members',         'friends_friendship_accepted',          __( 'The user accepts a friendship request from someone.', 'dpa' ),                      0 );
	$actions[] = array( 'members',         'friends_friendship_rejected',          __( 'The user rejects a friendship request from someone.', 'dpa' ),                      0 );
	$actions[] = array( 'blog',            'trashed_post',                         __( 'The user trashes a post or page.', 'dpa' ),                                         0 );
	$actions[] = array( 'messaging',       'messages_delete_thread',               __( 'The user deletes a private message.', 'dpa' ),                                      0 );
	$actions[] = array( 'members',         'friends_friendship_deleted',           __( 'The user cancels a friendship.', 'dpa' ),                                           0 );
	$actions[] = array( 'groups',          'groups_create_group',                  __( 'The user creates a group.', 'dpa' ),                                                0 );
	$actions[] = array( 'groups',          'groups_leave_group',                   __( 'The user leaves a group.', 'dpa' ),                                                 1 );
	$actions[] = array( 'groups',          'groups_delete_group',                  __( 'The user deletes a group.', 'dpa' ),                                                0 );
	$actions[] = array( 'forum',           'groups_new_forum_topic',               __( 'The user creates a new group forum topic.', 'dpa' ),                                1 );
	$actions[] = array( 'forum',           'groups_new_forum_topic_post',          __( 'The user replies to a group forum topic.', 'dpa' ),                                 1 );
	$actions[] = array( 'forum',           'groups_delete_group_forum_post',       __( 'The user deletes a group forum post.', 'dpa' ),                                     1 );
	$actions[] = array( 'forum',           'groups_delete_group_forum_topic',      __( 'The user deletes a group forum topic.', 'dpa' ),                                    1 );
	$actions[] = array( 'forum',           'groups_update_group_forum_post',       __( 'The user modifies a group forum post.', 'dpa' ),                                    1 );
	$actions[] = array( 'forum',           'groups_update_group_forum_topic',      __( 'The user modifies a group forum topic.', 'dpa' ),                                   1 );
	$actions[] = array( 'activitystream',  'bp_groups_posted_update',              __( "The user writes a message in a group's activity stream.", 'dpa' ),                  1 );
	$actions[] = array( 'activitystream',  'bp_activity_posted_update',            __( 'The user writes a message in their activity stream.', 'dpa' ),                      0 );
	$actions[] = array( 'activitystream',  'bp_activity_comment_posted',           __( 'The user replies to any item in any activity stream.', 'dpa' ),                     0 );
	$actions[] = array( 'multisite',       'signup_finished',                      __( 'The user creates a new site.', 'dpa' ),                                             0 );
	$actions[] = array( 'activitystream',  'bp_activity_add_user_favorite',        __( 'The user marks any item in any activity stream as a favourite.', 'dpa' ),           0 );
	$actions[] = array( 'activitystream',  'bp_activity_remove_user_favorite',     __( 'The user removes a favourited item from any activity stream.', 'dpa' ),             0 );
	$actions[] = array( 'groups',          'groups_demoted_member',                __( 'The user demotes a group member from a moderator or administrator.', 'dpa' ),       1 );
	$actions[] = array( 'groups',          'groups_banned_member',                 __( 'The user bans a member from a group.', 'dpa' ),                                     1 );
	$actions[] = array( 'groups',          'groups_unbanned_member',               __( 'The user unbans a member from a group.', 'dpa' ),                                   1 );
	$actions[] = array( 'groups',          'groups_premote_member',                __( 'The user receives a promotion to group moderator or administrator.', 'dpa' ),       1 );
	$actions[] = array( 'groups',          'groups_demote_member',                 __( 'The user receives a demotion from group moderator or administrator.', 'dpa' ),      1 );
	$actions[] = array( 'blog',            'wp_login',                             __( 'The user logs in to the site.', 'dpa' ),                                            0 );

	foreach ( $actions as $action ) {
		$args  = apply_filters( 'dpa_install_3_dot_0_action_taxonomy', array( 
			'description' => $action[0],
			'slug'        => $action[1]
		), $action );
		wp_insert_term( $action[2], 'dpa_action', $args );
	}
}
?>