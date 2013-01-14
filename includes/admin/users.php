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
}

/**
 * Display the Achievements user admin index screen, which contains a list of all the users.
 *
 * @global DPA_Users_List_Table $dpa_users_list_table Activity screen list table
 * @global string $plugin_page
 * @since Achievements (3.)
 */
function dpa_admin_screen_users() {
	global $dpa_users_list_table, $plugin_page;

	$messages = array();

	// Prepare the list items for display
	$dpa_users_list_table->prepare_items();

	// Call an action for plugins to modify the activity before we display the edit form
	do_action( 'dpa_admin_screen_users', $messages ); ?>

	<div class="wrap">
		<?php screen_icon( 'users' ); ?>
		<h2>
			<?php _ex( 'Users', 'admin menu title', 'dpa' ); ?>

			<?php if ( ! empty( $_REQUEST['s'] ) ) : ?>
				<span class="subtitle"><?php printf( _x( 'Search results for &#8220;%s&#8221;', 'admin screen search results heading', 'dpa' ), wp_html_excerpt( esc_html( stripslashes( $_REQUEST['s'] ) ), 50 ) ); ?></span>
			<?php endif; ?>
		</h2>

		<?php // If the user has just made a change to an item, display the status messages ?>
		<?php if ( ! empty( $messages ) ) : ?>
			<div id="moderated" class="<?php echo ( ! empty( $_REQUEST['error'] ) ) ? 'error' : 'updated'; ?>"><p><?php echo implode( "<br/>\n", $messages ); ?></p></div>
		<?php endif; ?>

		<?php // Display each item on its own row ?>
		<?php $dpa_users_list_table->views(); ?>

		<form id="dpa-admin-users-form" action="" method="get">
			<?php $dpa_users_list_table->search_box( __( 'Search all Users', 'dpa' ), 'dpa-admin-users' ); ?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>" />
			<?php $dpa_users_list_table->display(); ?>
		</form>

	</div><!-- .wrap -->
<?php
}