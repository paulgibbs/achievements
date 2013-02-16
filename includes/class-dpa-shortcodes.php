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
			'dpa-redeem-achievement-form' => array( $this, 'display_redeem_achievement_form' ),

			// Misc
			'dpa-breadcrumb'              => array( $this, 'display_breadcrumb' ),
			'dpa-unlock-notice'           => array( $this, 'display_feedback_achievement_unlocked' ),
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
		achievements()->achievement_query = new stdClass();
		achievements()->progress_query    = new stdClass();

		// Unset global IDs
		achievements()->current_achievement_id = 0;

		// Reset the post data globals
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

		// Remove 'dpa_replace_the_content' filter to prevent infinite loops
		remove_filter( 'the_content', 'dpa_replace_the_content' );

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
		// Get contents of the output buffer
		$output = ob_get_contents();

		$this->unset_globals();

		// Flush the output buffer
		ob_end_clean();

		dpa_reset_query_name();

		// Add 'dpa_replace_the_content' filter back (@see $this::start())
		add_filter( 'the_content', 'dpa_replace_the_content' );

		return $output;
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

		// Start output buffer
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
		$achievement_id = achievements()->current_achievement_id = (int) $attr['id'];

		// Bail if ID passed is not an achievement
		if ( ! dpa_is_achievement( $achievement_id ) )
			return $content;

		// If not in theme compat, reset necessary achievement_query attributes for achievements loop to function
		if ( ! dpa_is_theme_compat_active() ) {
			achievements()->achievement_query->query_vars['post_type'] = dpa_get_achievement_post_type();
			achievements()->achievement_query->in_the_loop             = true;
			achievements()->achievement_query->post                    = get_post( $achievement_id );
		}

		// Start output buffer
		$this->start( 'dpa_single_achievement' );

		// Check achievement caps
		$post = get_post( $achievement_id );
		if ( ! empty( $post ) && 'publish' == $post->post_status && current_user_can( 'read_achievement', $achievement_id ) )
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

		// Start output buffer
		$this->start( 'dpa_single_user_achievements' );

		dpa_get_template_part( 'content-author-achievement' );

		return $this->end();
	}


	/**
	 * Widget shortcodes
	 */

	/**
	 * Display the redeem achievements widget in an output buffer and return to ensure that post/page
	 * contents are displayed first.
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.1)
	 */
	public function display_redeem_achievement_form() {
		$this->unset_globals();

		// Start output buffer
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

		// Start output buffer
		$this->start();

		dpa_breadcrumb();

		return $this->end();
	}

	/**
	 * Display the "achievement unlocked" feedback template
	 *
	 * @return string Contents of output buffer
	 * @since Achievements (3.0)
	 */
	public function display_feedback_achievement_unlocked() {

		// Style and script
		achievements()->theme_functions->enqueue_notifications_style( true );
		achievements()->theme_functions->enqueue_notifications_script( true );

		$this->unset_globals();

		// Start output buffer
		$this->start();

		dpa_get_template_part( 'feedback-achievement-unlocked' );

		return $this->end();
	}
}
endif;  // class_exists
