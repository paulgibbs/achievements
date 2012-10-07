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
//attributes_metabox_save -- bbpress
function dpa_admin_setup_metaboxes() {
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
 * @since 1.0
 */
function dpa_achievement_metabox() {
	// Get all events grouped by the extension which provided them
	$events = dpa_get_all_events_details();
?>

	<div class="misc-pub-section">
		<label for="dpa_points"><?php _e( 'Karma points:', 'dpa' ); ?></label>
		<input type="number" name="dpa-points" id="dpa-points" value="0" />
	</div>

	<div class="misc-pub-section">
		<label for="dpa_type"><?php _ex( 'Type:', 'type of achievement', 'dpa' ); ?></label>
		<input type="radio" name="dpa_type" id="dpa-type-award" value="award"><?php _ex( '&nbsp;Award', 'type of achievement', 'dpa' ); ?></input>
		<input type="radio" name="dpa_type" id="dpa-type-event" value="event" checked="checked"><?php _ex( '&nbsp;Event', 'type of achievement', 'dpa' ); ?></input>

		<p class="hint"><?php _e( "An <em>award</em> is given by a site admin, whereas an <em>event</em> is unlocked automatically when its criteria have been met.", 'dpa' ) ?></p>

		<select id="dpa-event" name="dpa_event" style="display: none;" data-placeholder="<?php esc_attr_e( 'Press here to pick events', 'dpa' ); ?>" class="chzn-select <?php if ( is_rtl() ) echo 'chzn-rtl'; ?>" multiple="multiple">
			<option value=""></option>

			<?php foreach ( $events as $extension => $extension_events ) : ?>
				<optgroup label="<?php echo esc_attr( $extension ); ?>">

				<?php foreach ( $extension_events as $event ) : ?>
					<option value="<?php esc_attr( $event['id'] ); ?>"><?php echo esc_html( $event['description'] ); ?></option>
				<?php endforeach; ?>

				</optgroup>
			<?php endforeach; ?>

		</select>
	</div>

	<?php
	wp_nonce_field( 'dpa_achievement_metabox_save', 'dpa_achievement_metabox' );
	do_action( 'dpa_achievement_metabox' );
}
