<?php
/**
 * Main Achievements Admin Class
 *
 * @package Achievements
 * @subpackage Admin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DPA_Admin' ) ) :
/**
 * Loads Achievements admin area thing
 *
 * @since Achievements (3.0)
 */
class DPA_Admin {
	// Paths

	/**
	 * Path to the Achievements admin directory
	 */
	public $admin_dir = '';

	/**
	 * URL to the Achievements admin directory
	 */
	public $admin_url = '';

	/**
	 * URL to the Achievements admin css directory
	 */
	public $css_url = '';

	/**
	 * URL to the Achievements admin image directory
	 */
	public $images_url = '';

	/**
	 * URL to the Achievements admin javascript directory
	 */
	public $javascript_url = '';


	// Capability

	/**
	 * @var bool Minimum capability to access Settings
	 */
	public $minimum_capability = 'manage_options';


	/**
	 * The main Achievements admin loader
	 *
	 * @since Achievements (3.0)
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Set up the admin hooks, actions and filters
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_actions() {

		// Bail to prevent interfering with the deactivation process 
		if ( dpa_is_deactivation() )
			return;

		// If the plugin's been activated network-wide, only load the admin stuff on the DPA_DATA_STORE site
		if ( is_multisite() && dpa_is_running_networkwide() && get_current_blog_id() !== DPA_DATA_STORE )
			return;


		// General Actions

		// Add menu item to settings menu
		add_action( 'dpa_admin_menu',                           array( $this, 'admin_menus'             ) );

		// Add settings
		add_action( 'dpa_register_admin_settings',              array( $this, 'register_admin_settings' ) );

		// Column headers
		add_filter( 'manage_achievement_posts_columns',         'dpa_achievement_posts_columns' );

		// Columns (in page row)
		add_action( 'manage_posts_custom_column',               'dpa_achievement_custom_column', 10, 2 );

		// Sortable columns
		add_filter( 'manage_edit-achievement_sortable_columns', 'dpa_achievement_sortable_columns' );

		// Metabox actions
		add_action( 'save_post',                                'dpa_achievement_metabox_save' );

		// Contextual Help
		add_action( 'load-edit.php',                            'dpa_achievement_index_contextual_help' );
		add_action( 'load-post-new.php',                        'dpa_achievement_new_contextual_help' );
		add_action( 'load-post.php',                            'dpa_achievement_new_contextual_help' );

		// Messages
		add_filter( 'post_updated_messages',                    'dpa_achievement_feedback_messages' );


		// Allow plugins to modify these actions
		do_action_ref_array( 'dpa_admin_loaded', array( &$this ) );
	}

	/**
	 * Include required files
	 *
	 * @since Achievements (3.0)
	 */
	private function includes() {
		if ( ! class_exists( 'WP_List_Table' ) )
			require( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

		if ( ! class_exists( 'WP_Users_List_Table' ) )
			require( ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php' );

		// Supported plugins screen
		require( $this->admin_dir . 'functions.php'         );
		require( $this->admin_dir . 'supported-plugins.php' );

		// Users screen
		require( $this->admin_dir . 'class-dpa-users-list-table.php' );
		require( $this->admin_dir . 'users.php'                      );
	}

	/**
	 * Set admin globals
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_globals() {
		$this->admin_dir      = trailingslashit( achievements()->includes_dir . 'admin'  ); // Admin path
		$this->admin_url      = trailingslashit( achievements()->includes_url . 'admin'  ); // Admin URL
		$this->css_url        = trailingslashit( $this->admin_url             . 'css'    ); // Admin CSS URL
		$this->images_url     = trailingslashit( $this->admin_url             . 'images' ); // Admin images URL
		$this->javascript_url = trailingslashit( $this->admin_url             . 'js'     ); // Admin javascript URL
	}

	/**
	 * Add wp-admin menus
	 *
	 * @since Achievements (3.0)
	 */
	public function admin_menus() {
		$hooks = array();

		// About
		add_dashboard_page(
			__( 'Welcome to Achievements', 'dpa' ),
			__( 'Welcome to Achievements', 'dpa' ),
			$this->minimum_capability,
			'achievements-about',
			array( $this, 'about_screen' )
		);
		remove_submenu_page( 'index.php', 'achievements-about' );

		// "Users" menu
		$hooks[] = add_submenu_page(
			'edit.php?post_type=achievement',
			__( 'Achievements &mdash; Users', 'dpa' ),
			_x( 'Users', 'admin menu title', 'dpa' ),
			$this->minimum_capability,
			'achievements-users',
			'dpa_admin_screen_users'
		);

		// "Supported Plugins" menu
		$hooks[] = add_submenu_page(
			'edit.php?post_type=achievement',
			__( 'Achievements &mdash; Supported Plugins', 'dpa' ),
			__( 'Supported Plugins', 'dpa' ),
			$this->minimum_capability,
			'achievements-plugins',
			'dpa_supported_plugins'
		);

		foreach( $hooks as $hook ) {

			// Hook into early actions to register custom CSS and JS
			add_action( "admin_print_styles-$hook",  array( $this, 'enqueue_styles'  ) );
			add_action( "admin_print_scripts-$hook", array( $this, 'enqueue_scripts' ) );

			// Hook into early actions to register contextual help and screen options
			add_action( "load-$hook", array( $this, 'screen_options' ) );
		}

		// Actions for the edit.php?post_type=achievement index screen
		add_action( 'load-edit.php', array( $this, 'enqueue_index_styles' ) );

		// Add/save custom profile field on the edit user screen
		add_action( 'edit_user_profile',        array( $this, 'add_profile_fields'  ) );
		add_action( 'show_user_profile',        array( $this, 'add_profile_fields'  ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );
		add_action( 'personal_options_update',  array( $this, 'save_profile_fields' ) );

		// Remove the "categories" submenu from inside the achievement post type menu
		remove_submenu_page( 'edit.php?post_type=' . dpa_get_achievement_post_type(), 'edit-tags.php?taxonomy=category&amp;post_type=' . dpa_get_achievement_post_type() );
	}

	/**
	 * Hook into early actions to register contextual help and screen options
	 *
	 * @since Achievements (3.0)
	 */
	public function screen_options() {
		// Only load up styles if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		// "Supported Plugins" screen
		if ( 'achievements-plugins' === $_GET['page'] )
			dpa_supported_plugins_on_load();
		elseif ( 'achievements-users' === $_GET['page'] )
			dpa_admin_screen_users_on_load();
	}

	/**
	 * Enqueue CSS for our custom admin screens
	 *
	 * @since Achievements (3.0)
	 */
	public function enqueue_styles() {
		// Only load up styles if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		$rtl = is_rtl() ? '-rtl' : '';

		// "Supported Plugins" screen
		if ( 'achievements-plugins' === $_GET['page'] )
			wp_enqueue_style( 'dpa_admin_css', trailingslashit( $this->css_url ) . "supportedplugins{$rtl}.css", array(), '20120722' );

		// Achievements "users" screen
		elseif ( 'achievements-users' === $_GET['page'] )
			wp_enqueue_style( 'dpa_admin_users_css', trailingslashit( $this->css_url ) . "users{$rtl}.css", array(), '20130113' );
	}

	/**
	 * Enqueue CSS for the edit.php?post_type=achievement index screen
	 *
	 * @since Achievements (3.3)
	 */
	public function enqueue_index_styles() {

		// Only load up styles if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		wp_enqueue_style( 'dpa_admin_index_css', trailingslashit( $this->css_url ) . 'admin-editindex.css', array(), '20130423' );
	}

	/**
	 * Enqueue JS for our custom admin screens
	 *
	 * @since Achievements (3.0)
	 */
	public function enqueue_scripts() {
		// Only load up scripts if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		// "Supported Plugins" screen
		if ( 'achievements-plugins' === $_GET['page'] ) {
			wp_enqueue_script( 'dpa_socialite',   trailingslashit( $this->javascript_url ) . 'socialite-min.js',          array(), '20120722', true );
			wp_enqueue_script( 'tablesorter_js',  trailingslashit( $this->javascript_url ) . 'jquery-tablesorter-min.js', array( 'jquery' ), '20120722', true );
			wp_enqueue_script( 'dpa_sp_admin_js', trailingslashit( $this->javascript_url ) . 'supportedplugins-min.js',   array( 'jquery', 'dpa_socialite', 'dashboard', 'postbox' ), '20120722', true );

			// Add thickbox for the 'not installed' links on the List view
			add_thickbox();
		}
	}

	/**
	 * Register the settings
	 *
	 * @since Achievements (3.0)
	 */
	public function register_admin_settings() {
		// Only do stuff if we're on an Achievements admin screen
		if ( ! DPA_Admin::is_admin_screen() )
			return;

		// Fire an action for Achievements plugins to register their custom settings
		do_action( 'dpa_register_admin_settings' );
	}

	/**
	 * Add the 'User Points' box to Edit User page, and a list of the user's current achievements.
	 *
	 * @param object $user
	 * @since Achievements (3.0)
	 */
	public function add_profile_fields( $user ) {
		if ( ! is_super_admin() )
			return;
	?>

		<h3><?php _e( 'Achievements Settings', 'dpa' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="dpa_achievements"><?php _ex( 'Total Points', "User&rsquo;s total points from unlocked Achievements", 'dpa' ); ?></label></th>
				<td><input type="number" name="dpa_achievements" id="dpa_achievements" value="<?php echo esc_attr( dpa_get_user_points( $user->ID ) ); ?>" class="regular-text" />
				</td>
			</tr>

			<?php if ( dpa_has_achievements( array( 'ach_populate_progress' => $user->ID, 'ach_progress_status' => dpa_get_unlocked_status_id(), 'posts_per_page' => -1, ) ) ) : ?>
				<tr>
					<th scope="row"><?php _e( 'Unlocked Achievements', 'dpa' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><?php _e( 'Assign or remove achievements from this user', 'dpa' ); ?></legend>

							<?php while ( dpa_achievements() ) : ?>
								<?php dpa_the_achievement(); ?>

								<label><input type="checkbox" name="dpa_user_achievements[]" value="<?php echo esc_attr( dpa_get_the_achievement_ID() ); ?>" <?php checked( dpa_is_achievement_unlocked(), true ); ?>> <?php dpa_achievement_title(); ?></label><br>
							<?php endwhile; ?>

						</fieldset>
					</td>
				</tr>
			<?php endif; ?>

		</table>

	<?php
	}

	/**
	 * Update the user's 'User Points' meta information when the Edit User page has been saved,
	 * and modify the user's current achievements as appropriate.
	 *
	 * @param int $user_id
	 * @since Achievements (3.0)
	 */
	public function save_profile_fields( $user_id ) {
		if ( ! isset( $_POST['dpa_achievements'] ) || ! is_super_admin() )
			return;

		if ( ! isset( $_POST['dpa_user_achievements'] ) )
			$_POST['dpa_user_achievements'] = array();

		// If multisite and running network-wide, switch_to_blog to the data store site
		if ( is_multisite() && dpa_is_running_networkwide() )
			switch_to_blog( DPA_DATA_STORE );


		// Update user's points
		dpa_update_user_points( (int) $_POST['dpa_achievements'], $user_id );

		// Get unlocked achievements
		$unlocked_achievements = dpa_get_progress( array(
			'author'      => $user_id,
			'post_status' => dpa_get_unlocked_status_id(),
		) );

		$old_unlocked_achievements = wp_list_pluck( $unlocked_achievements, 'post_parent' );
		$new_unlocked_achievements = array_filter( wp_parse_id_list( $_POST['dpa_user_achievements'] ) );

		// Figure out which achievements to add or remove
		$achievements_to_add    = array_diff( $new_unlocked_achievements, $old_unlocked_achievements );
		$achievements_to_remove = array_diff( $old_unlocked_achievements, $new_unlocked_achievements );


		// Remove achievements :(
		if ( ! empty( $achievements_to_remove ) ) {

			foreach ( $achievements_to_remove as $achievement_id )
				dpa_delete_achievement_progress( $achievement_id, $user_id );
		}


		// Award achievements! :D
		if ( ! empty( $achievements_to_add ) ) {

			// Get achievements to add
			$new_achievements = dpa_get_achievements( array(
				'post__in'       => $achievements_to_add,
				'posts_per_page' => count( $achievements_to_add ),
			) );

			// Get any still-locked progress for this user
			$existing_progress = dpa_get_progress( array(
				'author'      => $user_id,
				'post_status' => dpa_get_locked_status_id(),
			) );

			foreach ( $new_achievements as $achievement_obj ) {
				$progress_obj = array();

				// If we have existing progress, pass that to dpa_maybe_unlock_achievement().
				foreach ( $existing_progress as $progress ) {
					if ( $achievement_obj->ID === $progress->post_parent ) {
						$progress_obj = $progress;
						break;
					}
				}

				dpa_maybe_unlock_achievement( $user_id, 'skip_validation', $progress_obj, $achievement_obj );
			}
		}


		// If multisite and running network-wide, undo the switch_to_blog
		if ( is_multisite() && dpa_is_running_networkwide() )
			restore_current_blog();
	}

	/**
	 * Is the current screen part of Achievements? e.g. a post type screen.
	 *
	 * @return bool True if this is an Achievements admin screen
	 * @since Achievements (3.0)
	 */
	public static function is_admin_screen() {
		$result = false;

		if ( ! empty( $_GET['post_type'] ) && 'achievement' === $_GET['post_type'] )
			$result = true;

		return true;
	}

	/**
	 * Output the about screen
	 *
	 * @since Achievements (3.4)
	 */
	public function about_screen() {
		$is_new_install          = ! empty( $_GET['is_new_install'] );
		list( $display_version ) = explode( '-', dpa_get_version() );
	?>

		<div class="wrap about-wrap">
			<h1><?php _e( 'Welcome to Achievements', 'dpa' ); ?></h1>
			<div class="about-text">
				<?php if ( $is_new_install ) : ?>
					<?php printf( __( 'Achievements %s is our safest, fastest, and most feature-packed version ever!', 'dpa' ), $display_version ); ?>
				<?php else : ?>
					<?php printf( __( 'Thank you for updating! Achievements %s is our safest, fastest, and most feature-packed version ever!', 'dpa' ), $display_version ); ?>
				<?php endif; ?>
			</div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'achievements-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'What&#8217;s New', 'dpa' ); ?>
				</a>
			</h2>

			<?php if ( $is_new_install ) : ?>
			<h3><?php _e( 'Getting Started', 'dpa' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Your Default Setup', 'dpa' ); ?></h4>
					<p><?php _e( 'TODO', 'dpa' ); ?><p>

					<h4><?php _e( 'Community and Support', 'dpa' ); ?></h4>
					<p><?php _e( 'TODO', 'dpa' ) ?></p>
				</div>

			<?php endif; ?>

			<div class="changelog">
				<h3><?php _e( 'Leaderboards', 'dpa' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Todo', 'dpa' ); ?></h4>
					<p><?php //DJPAULTODO: leaderboard text ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'WP-PostRatings', 'dpa' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( "Adds an AJAX rating system for your posts and pages", 'dpa' ); ?>
					<p><?php //DJPAULTODO: postratings text ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Hidden Achievements', 'dpa' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Todo', 'dpa' ); ?></h4>
					<p><?php //DJPAULTODO: hidden achievement text ?></p>
				</div>
			</div>


			<div class="changelog">
				<h3><?php _e( 'Under the Bonnet', 'dpa' ); ?></h3>

				<div class="feature-section three-col">
					<div>
						<h4><?php _e( 'WP-CLI', 'dpa' ); ?></h4>
						<p><?php printf( __( 'Achievements supports <a href="%s">WP-CLI</a>, so you can easily manage the plugin from the command-line.', 'dpa' ), 'http://wp-cli.org/' ); ?></p>
					</div>

					<div>
						<h4><?php _e( 'Theme Compatibility Improvements', 'dpa' ); ?></h4>
						<p><?php _e( 'Achievements has the latest theme compatibility code, fresh from BuddyPress 1.7+.', 'dpa' ); ?></p>
					</div>
			</div>

		</div>

		<?php
	}
}
endif; // class_exists check

/**
 * Set up Achievements' Admin
 *
 * @since Achievements (3.0)
 */
function dpa_admin_setup() {
	achievements()->admin = new DPA_Admin();
}