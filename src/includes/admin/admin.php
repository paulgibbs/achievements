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
	 * @var string Path to the Achievements admin directory
	 */
	public $admin_dir = '';

	/**
	 * @var string URL to the Achievements admin directory
	 */
	public $admin_url = '';

	/**
	 * @var string URL to the Achievements admin css directory
	 */
	public $css_url = '';

	/**
	 * @var string URL to the Achievements admin image directory
	 */
	public $images_url = '';

	/**
	 * @var string URL to the Achievements admin javascript directory
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
			wp_enqueue_script( 'dpa_sp_admin_js', trailingslashit( $this->javascript_url ) . 'supportedplugins.js',       array( 'jquery', 'dpa_socialite', 'dashboard', 'postbox' ), '20130908', true );

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
				<td><input type="number" name="dpa_achievements" id="dpa_achievements" value="<?php echo (int) dpa_get_user_points( $user->ID ); ?>" class="regular-text" />
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

								<label><input type="checkbox" name="dpa_user_achievements[]" value="<?php echo absint( dpa_get_the_achievement_ID() ); ?>" <?php checked( dpa_is_achievement_unlocked(), true ); ?>> <?php dpa_achievement_title(); ?></label><br>
							<?php endwhile; ?>

						</fieldset>
					</td>
				</tr>
			<?php endif; ?>

		</table>

	<?php
	}

	/**
	 * Update the user's "User Points" meta information when the Edit User page has been saved,
	 * and modify the user's current achievements as appropriate.
	 *
	 * The action that this function is hooked to is only executed on a succesful update,
	 * which is behind a nonce and capability check (see wp-admin/user-edit.php).
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
		list( $display_version ) = explode( '-', dpa_get_version() );

		$is_new_install = ! empty( $_GET['is_new_install'] );
		$name           = wp_get_current_user()->display_name;
	?>

		<style type="text/css">
		.about-text {
			min-height: 0;
			margin-bottom: 0;
			margin-right: 0;
		}
		.about-wrap h3 {
			margin-bottom: 0;
			padding-top: 0;
		}
		</style>

		<div class="wrap about-wrap">
			<h1><?php _e( 'Welcome to Achievements', 'dpa' ); ?></h1>
			<div class="about-text">
				<?php if ( $is_new_install ) : ?>
					<?php printf( __( 'Hi, %s! Thanks very much for downloading Achievements %s. You really are rather nice. This exciting update screen is to confirm a few things that you probably already know:', 'dpa' ), esc_html( $name ), $display_version ); ?>

					<ol>
						<li><?php _e( 'You&#8217;re super-talented at finding great WordPress plugins.', 'dpa' ); ?></li>
						<li><?php _e( 'We think you&#8217;ve got a truly beautiful website.', 'dpa' ); ?></li>
						<li><?php _e( 'See 1 &amp; 2.', 'dpa' ); ?></li>
					</ol>

					<?php _e( 'Achievements gamifies your WordPress site with challenges, badges, and points, which are the funnest ways to reward and encourage members of your community to participate. We hope you enjoy using the plugin!', 'dpa' ); ?>
				<?php else : ?>
					<?php printf( __( 'Hello there! Version %s is a maintenance release.', 'dpa' ), $display_version ); ?>
				<?php endif; ?>
			</div>

			<?php if ( $is_new_install ) : ?>
				<h3><?php _e( 'Getting Started', 'dpa' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Create your first achievement', 'dpa' ); ?></h4>
					<p><?php printf( __( 'The first idea to grasp is that there are two different types of achievements: <strong>awards</strong> and <strong>events</strong>. Award achievements have to be manually given out by a site admin, and event achievements are awarded automatically when its criteria has been met. <a href="%s">Learn more about achievement types</a>.', 'dpa' ), esc_url( 'http://achievementsapp.com/getting-started/types-of-achievements/' ) ); ?></p>
					<p><?php printf( __( 'The best way to learn is by doing, so let&rsquo;s create an achievement and find out how everything works. Our <a href="%s">Getting Started guide</a> will walk you through this easy process.', 'dpa' ), esc_url( 'http://achievementsapp.com/getting-started/' ) ); ?></p>

					<h4><?php _e( 'Get help and support', 'dpa' ); ?></h4>
					<p><?php printf( __( 'If you have questions about the plugin or need help, get in contact by leaving a message on the <a href="%s">WordPress.org support forum</a>. We&rsquo;d love to find out how you&rsquo;re using Achievements, so be sure to drop by and tell us!', 'dpa' ), esc_url( 'http://wordpress.org/support/plugin/achievements' ) ); ?></p>
				</div>

			<?php else : ?>

			<div class="changelog">
				<div class="feature-section">
					<p><?php _e( 'This release improves compatibility with WordPress 3.8. Some of the UI elements in the admin screens have had their styles tweaked for WordPress&#8217; new admin appearance, and the unlocked achievement check (the heartbeat) now happens much more quickly. Enjoy!', 'dpa' ); ?></p>
				</div>
			</div>

			<?php endif; ?>

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