<?php
/**
 * Achievements shortcodes class
 *
 * @package Achievements
 * @subpackage CoreClasses
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'DPA_Shortcodes' ) ) :
/**
 * Achievements shortcode class
 *
 * @since Achievements (3.0)
 */
class DPA_Shortcodes {
	/**
	 * @since Achievements (3.0)
	 * @var array Shortcode => function
	 */
	public $codes = array();

	/**
	 * Constructor
	 *
	 * @since Achievements (3.0)
	 */
	public function __construct() {
		$this->setup_globals();
		$this->add_shortcodes();
	}

	/**
	 * Shortcode globals
	 *
	 * @since Achievements (3.0)
	 */
	private function setup_globals() {

		// Setup the shortcodes
		$this->codes = apply_filters( 'dpa_shortcodes', array(

			// Achievements index
			'dpa-achievements-index'      => array( $this, 'display_achievements_index' ),

			// User achievement index
			'dpa-user-achievements-index' => array( $this, 'display_user_achievements' ),

			// Specific achievement - pass an 'id' attribute
			'dpa-single-achievement'      => array( $this, 'display_achievement' ),

			// Widgets
			'dpa-leaderboard'             => array( $this, 'display_leaderboard' ),
			'dpa-redeem-achievement-form' => array( $this, 'display_redeem_achievement_form' ),

			// Misc
			'dpa-breadcrumb'              => array( $this, 'display_breadcrumb' ),
		) );
	}

	/**
	 * Register Achievements' shortcodes
	 *
	 * @since Achievements (3.0)
	 */
	private function add_shortcodes() {
		foreach( (array) $this->codes as $code => $function )
			add_shortcode( $code, $function );
	}

	/**
	 * Unset some globals in the achievements() object that hold query related info
	 *
	 * @since Achievements (3.0)
	 */
	private function unset_globals() {
		// Unset global queries
		achievements()->achievement_query = new WP_Query();
		achievements()->leaderboard_query = new ArrayObject();
		achievements()->progress_query    = new WP_Query();

		// Unset global IDs
		achievements()->current_achievement_id = 0;
		wp_reset_postdata();
	}


	/**
	 * Output Buffers
	 */

	/**
	 * Start an output buffer.
	 *
	 * This is used to put the contents of the shortcode into a variable rather
	 * than outputting the HTML at run-time. This allows shortcodes to appear
	 * in the correct location in the_content() instead of when it's created.
	 *
	 * @param string $query_name Optional
	 * @since Achievements (3.0)
	 */
	private function start( $query_name = '' ) {
		dpa_set_query_name( $query_name );

		// Start output buffer
		ob_start();
	}

	/**
	 * Return the contents of the output buffer and flush its contents.
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.0)
	 */
	private function end() {
		$this->unset_globals();

		dpa_reset_query_name();

		// Return and flush the output buffer
		return ob_get_clean();
	}


	/**
	 * Achievement shortcodes
	 */

	/**
	 * Display an index of all achievement posts in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.0)
	 */
	public function display_achievements_index() {
		$this->unset_globals();
		$this->start( 'dpa_achievement_archive' );

		dpa_get_template_part( 'content-archive-achievement' );

		return $this->end();
	}

	/**
	 * Display the contents of a specific achievement ID in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @param array $attr
	 * @param string $content Optional
	 * @return string Contents of output buffer
	 * @since Achievements (3.0)
	 */
	public function display_achievement( $attr, $content = '' ) {
		// Sanity check required info
		if ( ! empty( $content ) || ( empty( $attr['id'] ) || ! is_numeric( $attr['id'] ) ) )
			return $content;

		$this->unset_globals();

		// Set passed attribute to $achievement_id for clarity
		$achievement_id = achievements()->current_achievement_id = absint( $attr['id'] );

		// Bail if ID passed is not an achievement
		if ( ! dpa_is_achievement( $achievement_id ) )
			return $content;

		// If not in theme compat, reset necessary achievement_query attributes for achievements loop to function
		if ( ! dpa_is_theme_compat_active() ) {
			achievements()->achievement_query->query_vars['post_type'] = dpa_get_achievement_post_type();
			achievements()->achievement_query->in_the_loop             = true;
			achievements()->achievement_query->post                    = get_post( $achievement_id );
		}

		$this->start( 'dpa_single_achievement' );

		dpa_get_template_part( 'content-single-achievement' );

		return $this->end();
	}

	/**
	 * For the current author, display an index of all their unlocked achievements
	 * in an output buffer and return to ensure that post/page contents are displayed first.
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.0)
	 */
	public function display_user_achievements() {
		$this->unset_globals();
		$this->start( 'dpa_single_user_achievements' );

		dpa_get_template_part( 'content-author-achievement' );

		return $this->end();
	}


	/**
	 * Widget shortcodes
	 */

	/**
	 * Display the leaderboard widget in an output buffer and return to ensure that post/page contents are displayed first.
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.4)
	 */
	public function display_leaderboard() {
		$this->unset_globals();
		$this->start();

		dpa_get_template_part( 'content-leaderboard', 'widget' );

		return $this->end();
	}

	/**
	 * Display the redeem achievements widget in an output buffer and return to ensure that post/page contents are displayed first.
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.1)
	 */
	public function display_redeem_achievement_form() {
		$this->unset_globals();
		$this->start();

		dpa_get_template_part( 'form-redeem-code' );

		return $this->end();
	}


	/**
	 * Other templates
	 */

	/**
	 * Display a breadcrumb in an output buffer and return to ensure that post/page contents are displayed first.
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.0)
	 */
	public function display_breadcrumb() {
		$this->unset_globals();
		$this->start();

		dpa_breadcrumb();

		return $this->end();
	}

	/**
	 * Display the "achievement unlocked" feedback template and return to ensure that post/page contents are displayed first.
	 *
	 * This function is redundant. The "feedback-achievement-unlocked" template has been removed from the plugin.
	 * Notifications were overhauled in version 3.5 and were replaced with the heartbeat-powered "live notifications" system.
	 *
	 * @deprecated Achievements (3.5)
	 * @return string Contents of output buffer
	 * @since Achievements (3.0)
	 */
	public function display_feedback_achievement_unlocked() {
		$this->unset_globals();
		$this->start();

		dpa_get_template_part( 'feedback-achievement-unlocked' );

		return $this->end();
	}

	/**
	 * Display the "achievement unlocked" javascript template and return to ensure that post/page contents are displayed first.
	 * 
	 * Note: this is a JS template, not a HTML template. This template is wrapped inside <script> tags which will be used with
	 * underscore.js' _.template() method. It compiles these JS templates into functions that can be evaluated for rendering.
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.5)
	 */
	public function display_notifications_template() {
		$this->unset_globals();
		$this->start();

		dpa_get_template_part( 'feedback-notifications' );

		return $this->end();
	}
}
endif;  // class_exists
