<?php
/**
 * Achievements admin functions
 *
 * Post type/taxonomy-specific customisations.
 *
 * @package Achievements
 * @subpackage AdminFunctions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sets up metaboxes for the achievement post type admin screen.
 * This is a callback function which is invoked by register_post_type().
 *
 * Also enqueues required JavaScript and CSS.
 *
 * @since 1.0
 */
function dpa_admin_setup_metaboxes() {
	// Load metaboxes and on-save handler
	add_meta_box( 'dpa-mb', __( 'Achievements', 'dpa' ), 'dpa_achievement_metabox', dpa_get_achievement_post_type(), 'side', 'default' );
	remove_meta_box( 'tagsdiv-dpa_event', dpa_get_achievement_post_type(), 'side' );

	// Chosen is a JavaScript plugin that makes long, unwieldy select boxes much more user-friendly.
	wp_enqueue_style( 'dpa_chosen_css', trailingslashit( achievements()->admin->css_url ) . 'chosen.css', array(), '20121006' );
	wp_enqueue_script( 'dpa_chosen_js', trailingslashit( achievements()->admin->javascript_url ) . 'chosen-jquery-min.js', array( 'jquery' ), '20121006' );

	// General styles for the post type admin screen.
	wp_enqueue_script( 'dpa_admin_js', trailingslashit( achievements()->admin->javascript_url ) . 'achievements-min.js', array( 'jquery', 'dpa_chosen_js' ), '20121006' );
	wp_enqueue_style( 'dpa_admin_css', trailingslashit( achievements()->admin->css_url ) . 'achievements.css', array(), '20121006' );
}

/**
 * Achievements metabox
 *
 * Contains fields to set the karma points, pick between action/event, and the list of events.
 *
 * @param WP_Post $post The post being added or edited
 * @since 1.0
 */
function dpa_achievement_metabox( $post ) {
	// Get all events grouped by the extension which provided them
	$events = dpa_get_all_events_details();

	// Get existing values (if this is an edit)
	$existing_points = dpa_get_achievement_points( $post->ID );
	$existing_target = dpa_get_achievement_target( $post->ID );
	$existing_events = wp_get_post_terms( $post->ID, dpa_get_event_tax_id(), array( 'fields' => 'ids', ) );
	$existing_type   = ( empty( $existing_events ) && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) ? 'award' : 'event';

	// Ensure sane defaults
	if ( empty( $existing_points ) )
		$existing_points = 0;

	if ( empty( $existing_target ) )
		$existing_target = 1;
?>

	<div class="misc-pub-section">
		<label for="dpa-points"><?php _e( 'Karma points:', 'dpa' ); ?></label>
		<input type="number" name="dpa_points" id="dpa-points" value="<?php echo esc_attr( $existing_points ); ?>" />
	</div>

	<div class="misc-pub-section">
		<label for="dpa_type"><?php _ex( 'Type:', 'type of achievement', 'dpa' ); ?></label>
		<input type="radio" name="dpa_type" id="dpa-type-award" value="award" <?php checked( $existing_type, 'award' ); ?>><?php _ex( '&nbsp;Award', 'type of achievement', 'dpa' ); ?></input>
		<input type="radio" name="dpa_type" id="dpa-type-event" value="event" <?php checked( $existing_type, 'event' ); ?>><?php _ex( '&nbsp;Event', 'type of achievement', 'dpa' ); ?></input>

		<p class="hint"><?php _e( "An <em>award</em> is given by a site admin, whereas an <em>event</em> is unlocked automatically when its criteria have been met.", 'dpa' ) ?></p>

		<select id="dpa-event" name="dpa_event[]" style="visibility: hidden" data-placeholder="<?php esc_attr_e( 'Press here to pick events', 'dpa' ); ?>" class="chzn-select <?php if ( is_rtl() ) echo 'chzn-rtl'; ?>" multiple="multiple">
			<option value=""></option>

			<?php foreach ( $events as $extension => $extension_events ) : ?>
				<optgroup label="<?php echo esc_attr( $extension ); ?>">

				<?php foreach ( $extension_events as $event ) : /*echo(var_dump( in_array( $event['id'], $existing_events ) ));*/ ?>
					<option value="<?php echo esc_attr( $event['id'] ); ?>" <?php selected( in_array( $event['id'], $existing_events ), true ); ?>><?php echo esc_html( $event['description'] ); ?></option>
				<?php endforeach; ?>

				</optgroup>
			<?php endforeach; ?>

		</select>
	</div>

	<div class="misc-pub-section dpa-target">
		<label for="dpa_target"><?php _ex( 'Events repeat:', "Number of times an achievement's events need to repeat before the achievement is awarded", 'dpa' ); ?></label>
		<input type="number" name="dpa_target" id="dpa-target" min="1" value="<?php echo esc_attr( $existing_target ); ?>" />

		<p class="hint"><?php _e( "Number of times the events need to repeat before the achievement is awarded.", 'dpa' ); ?></p>
	</div>

	<?php
	wp_nonce_field( 'dpa_achievement_metabox_save', 'dpa_achievement_metabox' );
	do_action( 'dpa_achievement_metabox' );
}

/**
 * Achievements metabox on-save handler
 *
 * @param int $achievement_id
 * @return int Post ID to save
 * @since 3.0
 */
function dpa_achievement_metabox_save( $achievement_id ) {
	// Bail if doing an autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $achievement_id;

	// Bail if not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return $achievement_id;

	// Bail if not saving an achievement post type
	if ( dpa_get_achievement_post_type() != get_post_type( $achievement_id ) )
		return $achievement_id;

	// Nonce check
	if ( empty( $_POST['dpa_achievement_metabox'] ) || ! wp_verify_nonce( $_POST['dpa_achievement_metabox'], 'dpa_achievement_metabox_save' ) )
		return $achievement_id;

	// Bail if current user cannot edit this achievement
	if ( ! current_user_can( 'edit_achievement', $achievement_id ) )
		return $achievement_id;

	// Karma points
	$points = ! empty( $_POST['dpa_points'] ) ? (int) $_POST['dpa_points'] : 0;
	update_post_meta( $achievement_id, '_dpa_points', $points );

	// Type
	$type = 'award';
	if ( ! empty( $_POST['dpa_type'] ) ) {
		if ( 'event' == $_POST['dpa_type'] )
			$type = 'event';
		else
			$type = 'award';
	}

	// Event repeats count target
	$frequency = ! empty( $_POST['dpa_target'] ) ? (int) $_POST['dpa_target'] : 1;
	if ( $frequency < 1 )
		$frequency = 1;

	// Events
	$events = array();
	if ( 'event' == $type && ! empty( $_POST['dpa_event'] ) ) {
		$events = wp_parse_id_list( $_POST['dpa_event'] );
		update_post_meta( $achievement_id, '_dpa_target', $frequency );

	} else {
		delete_post_meta( $achievement_id, '_dpa_target' );
	}

	wp_set_post_terms( $achievement_id, $events, dpa_get_event_tax_id() );

	// Run an action for third party plugins to do their things
	do_action( 'dpa_achievement_metabox_save', $achievement_id );

	return $achievement_id;
}
