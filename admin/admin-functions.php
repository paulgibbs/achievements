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

/**
 * Add custom columns to the achievement post type index screen
 *
 * @param array $columns
 * @return array
 * @since 3.0
 */
function dpa_achievement_posts_columns( $columns ) {
	$columns = array(
		'cb'               => '<input type="checkbox" />',
		'title'            => __( 'Title', 'dpa' ),
		'achievement_type' => _x( 'Type', 'Type of the achievement; award or badge', 'dpa' ),
		'karma'            => __( 'Karma Points', 'dpa' ),
		'date'             => __( 'Date', 'dpa' ),
	);

	return apply_filters( 'dpa_achievements_posts_columns', $columns );
}

/**
 * Outputs the content for the custom columns on the achievement post type index screen
 *
 * @param string $column
 * @param int $post_id
 * @since 3.0
 */
function dpa_achievement_custom_column( $column, $post_id ) {
	if ( 'karma' == $column ) {
		dpa_achievement_points( $post_id );

	} elseif ( 'achievement_type' == $column ) {
		$existing_events = wp_get_post_terms( $post_id, dpa_get_event_tax_id(), array( 'fields' => 'ids', ) );
		$existing_type   = empty( $existing_events ) ? __( 'Award', 'dpa' ) : __( 'Event', 'dpa' );
		echo $existing_type;
	}
}

/**
 * Set the "achievement type" and "karma" columns as sortable on the achievement post type index screen
 *
 * @param array $columns
 * @return array
 * @since 3.0
 */
function dpa_achievement_sortable_columns( $columns ) {
	$columns['karma']            = 'karma';
	$columns['achievement_type'] = 'achievement_type';

	return apply_filters( 'dpa_achievement_sortable_columns', $columns );
}

/**
 * Contextual help for the new/edit achievement CPT screen
 *
 * @since 3.0
 */
function dpa_achievement_new_contextual_help() {
	// Bail out if we're not on the right screen
	if ( dpa_get_achievement_post_type() != get_current_screen()->post_type )
		return;

	// Most of this was copied from wpcore's New Post screen
	$customise_display = '<p>' . __( 'The title field and the big achievement Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop, and can minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide or reveal more boxes (Featured Image, Achievements, Slug) or to choose a 1- or 2-column layout for this screen.', 'dpa' ) . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'customise-display',
		'title'   => __( 'Customizing This Display', 'dpa' ),
		'content' => $customise_display,
	) );

	$title_and_editor  = '<p>' . __( '<strong>Title</strong> - Enter a title for your achievement. After you enter a title, you&#8217;ll see the permalink below, which you can edit.', 'dpa' ) . '</p>';
	$title_and_editor .= '<p>' . __( '<strong>Post editor</strong> - Enter the text for your achievement. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The Text mode allows you to enter HTML along with your achievement text. Line breaks will be converted to paragraphs automatically. You can insert media files by clicking the icons above the achievement editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in Text mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular achievement editor.', 'dpa' ) . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'title-post-editor',
		'title'   => __( 'Title and Achievement Editor', 'dpa' ),
		'content' => $title_and_editor,
	) );

	$publish_box = '<p>' . __( "<strong>Publish</strong> - You can set the terms of publishing your achievement in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting an achievement's page or setting the achievement to not appear in lists on your site). Publish (immediately) allows you to set a future or past date and time, so you can schedule an achievement to be published in the future.", 'dpa' ) . '</p>';

	$publish_box .= '<p>' . __( '<strong>Featured Image</strong> - This allows you to associate an image with your achievement without inserting it into the big achievement Editing Area.', 'dpa' ) . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'publish-box',
		'title'   => __( 'Publish Box', 'dpa' ),
		'content' => $publish_box,
	) );

	$achievements_box  = '<p>' . __( '<strong>Karma points</strong> - set the number of points (called karma) given to a user when they unlock an achievement.', 'dpa' ) . '</p>';
	$achievements_box .= '<p>' . __( '<strong>Type</strong> - there are two types of achievement, Award and Event. An Award is given by a site admin, whereas an Event is unlocked automatically when its criteria have been met.', 'dpa' ) . '</p>';
	$achievements_box .= '<p>' . __( '<strong>Event Achievements</strong> - this field appears when you create an Event achievement. Use the dropdown box to choose the events that you want to trigger this achievement.', 'dpa' ) . '</p>';
	$achievements_box .= '<p>' . __( '<strong>Events repeat</strong> - for Event achievements, set the number of times the events need to occur before the achievement is awarded.', 'dpa' ) . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'achievement-box',
		'title'   => __( 'Achievements Box', 'dpa' ),
		'content' => $achievements_box,
	) );


	get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'dpa' ) . '</strong></p>' .
			'<p><a href="http://achievementsapp.com/" target="_blank">' . __( 'Achievements Website', 'dpa' ) . '</a></p>' .
			'<p><a href="http://wordpress.org/support/plugin/achievements/" target="_blank">' . __( 'Support Forums', 'dpa' ) . '</a></p>'
	);
}
