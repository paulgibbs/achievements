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
 * @since 3.0
 */
class DPA_Shortcodes {
	/**
	 * @since 3.0
	 * @var array Shortcode => function
	 */
	public $codes = array();

	/**
	 * Constructor
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->add_shortcodes();
	}

	/**
	 * Shortcode globals
	 *
	 * @since 3.0
	 */
	private function setup_globals() {
		// Setup the shortcodes
		$this->codes = apply_filters( 'dpa_shortcodes', array(
			// Achievements index
			'dpa-achievements-index'  => array( $this, 'display_achievements_index' ),

			// Specific achievement - pass an 'id' attribute
			'dpa-single-achievement' => array( $this, 'display_achievement' ),
		) );
	}

	/**
	 * Register Achievements' shortcodes
	 *
	 * @since 3.0
	 */
	private function add_shortcodes() {
		// Loop through and add the shortcodes
		foreach( $this->codes as $code => $function )
			add_shortcode( $code, $function );

		// Custom shortcodes
		do_action( 'dpa_register_shortcodes' );
	}

	/**
	 * Unset some globals in the achievements() object that hold query related info
	 *
	 * @since 3.0
	 */
	private function unset_globals() {
		// Unset global queries
		achievements()->achievement_query = new stdClass();
		achievements()->progress_query    = new stdClass();

		// Unset global IDs
		achievements()->current_achievement_id = 0;

		// Reset the post data
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
	 * @since 3.0
	 */
	private function start() {
		// Remove 'dpa_replace_the_content' filter to prevent infinite loops
		remove_filter( 'the_content', 'dpa_replace_the_content' );

		// Start output buffer
		ob_start();
	}

	/**
	 * Return the contents of the output buffer and flush its contents.
	 *
	 * @return string Contents of output buffer.
	 * @since 3.0
	 */
	private function end() {
		// Get contents of the output buffer
		$output = ob_get_contents();

		// Unset globals
		$this->unset_globals();

		// Flush the output buffer
		ob_end_clean();

		// Reset the query name
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
	 * @return string
	 * @since 3.0
	 */
	public function display_achievements_index() {
		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start();

		dpa_get_template_part( 'content', 'archive-achievement' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Display the contents of a specific achievement ID in an output buffer
	 * and return to ensure that post/page contents are displayed first.
	 *
	 * @param array $attr
	 * @param string $content Optional
	 * @return string
	 * @since 3.0
	 */
	public function display_achievement( $attr, $content = '' ) {
		// Sanity check required info
		if ( ! empty( $content ) || ( empty( $attr['id'] ) || ! is_numeric( $attr['id'] ) ) )
			return $content;

		// Set passed attribute to $achievement_id for clarity
		$achievement_id = achievements()->current_achievement_id = $attr['id'];

		// Bail if ID passed is not an achievement
		if ( ! dpa_is_achievement( $achievement_id ) )
			return $content;

		// Start output buffer
		$this->start();

		// Check achievement caps
		// @todo Compare this to bbPress' display_forum() and port missing functions
		$post = get_post( $achievement_id );
		if ( ! empty( $post ) && 'publish' == $post->post_status && current_user_can( 'read_achievement', $achievement_id ) )
			dpa_get_template_part( 'content', 'single-achievement' );

		// Return contents of output buffer
		return $this->end();
	}


	/**
	 * Other templates
	 */

	/**
	 * Display a breadcrumb
	 *
	 * @return string
	 * @since 3.0
	 */
	public function display_breadcrumb() {
		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start();

		// Output breadcrumb
		dpa_breadcrumb();

		// Return contents of output buffer
		return $this->end();
	}
}
endif;  // class_exists
