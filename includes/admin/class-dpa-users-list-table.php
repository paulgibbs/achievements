<?php
/**
 * Achievements users admin list table class
 *
 * @package Achievements
 * @subpackage CoreClasses
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * List table class for the Achievements users admin page.
 *
 * @since Achievements (3.0)
 */
class DPA_Users_List_Table extends WP_Users_List_Table {

	/**
	 * Constructor
	 * 
	 * @since Achievements (3.0)
	 */
	function __construct( $args = array() ) {
		parent::__construct( $args );

		// Override the WP_Users_List_Table's opinion of whether to show network users or not.
		$this->is_site_users = dpa_is_running_networkwide();
		if ( $this->is_site_users )
			$this->site_id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
	}

	/**
	 * Get an array of all the columns on the page
	 *
	 * @return array
	 * @since BuddyPress (1.6)
	 */
	function get_column_info() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		return $this->_column_headers;
	}

	/**
	 * Get the list of views available on this table (e.g. "all", "administrator", "subscriber").
	 * 
	 * Most of this method lifted directly from WP_Users_List_Table.
	 *
	 * @global unknown $roles
	 * @global unknown $wp_roles
	 * @since Achievements (3.0)
	 */
	function get_views() {
		global $wp_roles, $role;

		// Get the number of users
		if ( $this->is_site_users ) {
			switch_to_blog( $this->site_id );
			$users_of_blog = count_users();
			restore_current_blog();

		} else {
			$users_of_blog = count_users();
		}

		// Build the URL back to this page, stripping of any parameters used on other links
		$url = remove_query_arg( array( 'action', 'error', 'role', 'updated', ), $_SERVER['REQUEST_URI'] );

		$total_users  =  $users_of_blog['total_users'];
		$avail_roles  =& $users_of_blog['avail_roles'];
		$current_role =  false;
		$class        =  empty( $role ) ? ' class="current"' : '';
		unset( $users_of_blog );

		$role_links        = array();
		$role_links['all'] = "<a href='$url'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_users, 'admin screen, types of users', 'dpa' ), number_format_i18n( $total_users ) ) . '</a>';

		// Iterate through all WP user roles, and get count of users in each.
		foreach ( $wp_roles->get_names() as $this_role => $name ) {
			if ( ! isset( $avail_roles[$this_role] ) )
				continue;

			$class = '';

			// Highlight the currently selected user role
			if ( $this_role == $role ) {
				$class        = ' class="current"';
				$current_role = $role;
			}

			$name = translate_user_role( $name );

			// translators: User role name with count
			$name = sprintf( __( '%1$s <span class="count">(%2$s)</span>', 'dpa' ), $name, number_format_i18n( $avail_roles[$this_role] ) );

			// Build the links
			$role_links[$this_role] = "<a href='" . esc_url( add_query_arg( 'role', $this_role, $url ) ) . "'$class>$name</a>";
		}

		return $role_links;
	}

	/**
	 * Get bulk actions
	 *
	 * @return array Key/value pairs for the bulk actions dropdown
	 * @since Achievements (3.0)
	 */
	function get_bulk_actions() {
		return array();
	}

	/**
	 * Markup for the "filter" part of the form (i.e. which item type to display)
	 *
	 * @param string $which 'top' or 'bottom'
	 * @since Achievements (3.0)
	 */
	function extra_tablenav( $which ) {
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @return string|bool The action name or False if no action was selected
	 * @since Achievements (3.0)
	 */
	function current_action() {
		return false;
	}

	/**
	 * Get the table column titles.
	 *
	 * @see WP_List_Table::single_row_columns()
	 * @return array
	 * @since Achievements (3.0)
	 */
	function get_columns() {
		return array(
			'username'         => __( 'Username', 'dpa' ),
			'dpa_last_id'      => __( 'Last Achievement', 'dpa' ),
			'dpa_actions'      => __( 'Actions', 'dpa' ),
			'dpa_achievements' => __( 'Achievements', 'dpa' ),
		);
	}

	/**
	 * Get the column names for sortable columns
	 *
	 * @return array
	 * @since Achievements (3.0)
	 */
	function get_sortable_columns() {
		return array(
			'dpa_achievements' => 'dpa_points',
			'username'         => 'login',
		);
	}

	/**
	 * Generate the table rows
	 *
	 * @since Achievements (3.0)
	 */
	function display_rows() {
		foreach ( $this->items as $item )
			$this->single_row( $item );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item
	 * @since Achievements (3.0)
	 */
	function single_row( $item ) {
		static $row_class = '';
		$row_class = ( $row_class == '' ) ? ' class="alternate"' : '';

		echo '<tr' . $row_class . '>';
		echo $this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Username column
	 *
	 * @param array $item A singular item (one full row)
	 * @see WP_List_Table::single_row_columns()
	 * @since Achievements (3.0)
	 */
	function column_username( $item ) {
		$avatar = get_avatar( $item->ID, 32 );
		$url    = user_trailingslashit( trailingslashit( get_author_posts_url( $item->ID ) ) . dpa_get_authors_endpoint() );

		printf( '%1$s <strong><a href="%2$s">%3$s</a></strong>', $avatar, esc_url( $url ), $item->user_login );
	}

	/**
	 * Unlock count column
	 *
	 * @param array $item A singular item (one full row)
	 * @see WP_List_Table::single_row_columns()
	 * @since Achievements (3.0)
	 */
	function column_dpa_achievements( $item ) {
		dpa_user_unlocked_count( $item->ID );
	}

	/**
	 * Last unlocked achievement column
	 *
	 * @param array $item A singular item (one full row)
	 * @see WP_List_Table::single_row_columns()
	 * @since Achievements (3.0)
	 */
	function column_dpa_last_id( $item ) {
		$output = true;

		// Get this user's most recent unlocked achievement
		$achievement_id = dpa_get_user_last_unlocked( $item->ID );
		if ( empty( $achievement_id ) )
			$output = false;

		// Check user ID is valid
		if ( $output && ! dpa_is_user_active( $item->ID ) )
			$output = false;

		// Check achievement is still valid
		if ( $output )
			$achievement = get_post( $achievement_id );

		if ( $output && ( empty( $achievement ) || 'publish' != $achievement->post_status ) )
			$output = false;

		if ( $output ) {
			printf(
				'<a href="%1$s">%2$s</a>',
				get_permalink( $achievement->ID ),
				apply_filters( 'dpa_get_achievement_title', $achievement->post_title, $achievement->ID )
			);

		} else {
			echo '&#8212;';
		}
	}


	/**
	 * Actions column
	 *
	 * @param array $item A singular item (one full row)
	 * @see WP_List_Table::single_row_columns()
	 * @since Achievements (3.0)
	 */
	function column_dpa_actions( $item ) {
		echo '<a href="#">Edit</a>';
	}
}
