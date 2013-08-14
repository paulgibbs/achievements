<?php
/**
 * Achievements admin users screen functions
 *
 * @package Achievements
 * @subpackage Admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Set up the Achievements users admin page before any output is sent. Register contextual help and screen options for this page.
 *
 * @since Achievements (3.0)
 * @todo Remove the $dpa_users_list_table and switch to a singleton
 */
function dpa_admin_screen_users_on_load() {
	global $dpa_users_list_table;

	// Call an action for plugins to hook in early
	do_action( 'dpa_admin_screen_users_on_load' );

	// Create the Activity screen list table
	$dpa_users_list_table = new DPA_Users_List_Table();

	// Help panel - overview text
	get_current_screen()->add_help_tab( array(
		'id'      => 'dpa-supported-plugins-overview',
		'title'   => __( 'Overview', 'dpa' ),
		'content' =>
			'<p>' . __( 'This screen lists all the users on your site who are eligble to unlock achievements.', 'dpa' ) . '</p>' .
			'<p>' . __( 'Each user has one of five defined roles as set by the site admin: Site Administrator, Editor, Author, Contributor, or Subscriber. Users with roles other than Administrator will see fewer options in the dashboard navigation when they are logged in, based on their role.', 'dpa' ) . '</p>'
	) );

	// Help panel - screen display text
	get_current_screen()->add_help_tab( array(
		'id'      => 'dpa-supported-plugins-views',
		'title'   => __( 'Screen Content', 'dpa' ),
		'content' =>
			'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:', 'dpa' ) . '</p>' .
			'<ul>' .
				'<li>' . __( 'You can assign and remove achievements from a user by using the links in the Actions column.', 'dpa' ) . '</li>' .
				'<li>' . __( 'You can filter the list of users by User Role using the text links in the upper left to show All, Administrator, Editor, Author, Contributor, or Subscriber. The default view is to show all users. Unused User Roles are not listed.', 'dpa' ) . '</li>' .
				'<li>' . __( 'You can hide/display columns based on your needs using the Screen Options tab.', 'dpa' ) . '</li>' .
			'</ul>'
	) );

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'dpa' ) . '</strong></p>' .
		'<p><a href="http://achievementsapp.com/" target="_blank">' . __( 'Achievements Website', 'dpa' ) . '</a></p>' .
		'<p><a href="http://wordpress.org/support/plugin/achievements/" target="_blank">' . __( 'Support Forums', 'dpa' ) . '</a></p>'
	);
}

/**
 * Display the Achievements user admin index screen, which contains a list of all the users.
 *
 * @since Achievements (3.0)
 */
function dpa_admin_screen_users() {
	global $dpa_users_list_table, $plugin_page, $usersearch;

	$messages = array();

	// Prepare the list items for display
	$dpa_users_list_table->prepare_items();

	// Call an action for plugins to modify the activity before we display the edit form
	do_action( 'dpa_admin_screen_users', $messages ); ?>

	<div class="wrap">
		<?php screen_icon( 'users' ); ?>
		<h2>
			<?php _ex( 'Users', 'admin menu title', 'dpa' ); ?>

			<?php if ( ! empty( $usersearch ) ) : ?>
				<span class="subtitle"><?php printf( _x( 'Search results for &#8220;%s&#8221;', 'admin screen search results heading', 'dpa' ), esc_html( wp_unslash( $usersearch ) ) ); ?></span>
			<?php endif; ?>
		</h2>

		<?php // If the user has just made a change to an item, display the status messages ?>
		<?php if ( ! empty( $messages ) ) : ?>
			<div id="moderated" class="<?php echo ( ! empty( $_GET['error'] ) ) ? 'error' : 'updated'; ?>"><p><?php echo implode( "<br/>\n", $messages ); ?></p></div>
		<?php endif; ?>

		<?php // Display each item on its own row ?>
		<?php $dpa_users_list_table->views(); ?>

		<form id="dpa-admin-users-form" action="" method="get">
			<?php $dpa_users_list_table->search_box( __( 'Search all Users', 'dpa' ), 'dpa-admin-users' ); ?>
			<input type="hidden" name="post_type" value="<?php echo esc_attr( dpa_get_achievement_post_type() ); ?>" />
			<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
			<?php $dpa_users_list_table->display(); ?>
		</form>

	</div><!-- .wrap -->
<?php
}