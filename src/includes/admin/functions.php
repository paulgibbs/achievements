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
 * @since Achievements (3.0)
 */
function dpa_admin_setup_metaboxes() {
	// Load metaboxes and on-save handler
	add_meta_box( 'dpa-mb', __( 'Achievements', 'dpa' ), 'dpa_achievement_metabox', dpa_get_achievement_post_type(), 'side', 'high' );
	remove_meta_box( 'tagsdiv-dpa_event', dpa_get_achievement_post_type(), 'side' );

	// Chosen is a JavaScript plugin that makes long, unwieldy select boxes much more user-friendly.
	wp_enqueue_style( 'dpa_chosen_css', trailingslashit( achievements()->admin->css_url ) . 'chosen.css', array(), dpa_get_version() );
	wp_enqueue_script( 'dpa_chosen_js', trailingslashit( achievements()->admin->javascript_url ) . 'chosen-jquery-min.js', array( 'jquery' ), dpa_get_version() );

	// General styles for the post type admin screen.
	$rtl = is_rtl() ? '-rtl' : '';
	wp_enqueue_script( 'dpa_admin_js', trailingslashit( achievements()->admin->javascript_url ) . 'achievements.js', array( 'jquery', 'dpa_chosen_js' ), dpa_get_version() );
	wp_enqueue_style( 'dpa_admin_css', trailingslashit( achievements()->admin->css_url ) . "achievements{$rtl}.css", array(), dpa_get_version() );
}

/**
 * Achievements metabox
 *
 * Contains fields to set the karma points, pick between action/event, and the list of events.
 *
 * @param WP_Post $post The post being added or edited
 * @since Achievements (3.0)
 */
function dpa_achievement_metabox( $post ) {
	// Get all events grouped by the extension which provided them
	$events = dpa_get_all_events_details();

	// Get existing values (if this is an edit)
	$existing_points = dpa_get_achievement_points( $post->ID );
	$existing_target = dpa_get_achievement_target( $post->ID );
	$existing_events = wp_get_post_terms( $post->ID, dpa_get_event_tax_id(), array( 'fields' => 'ids', ) );
	$existing_type   = ( empty( $existing_events ) && ! empty( $_GET['action'] ) && 'edit' === $_GET['action'] ) ? 'award' : 'event';
	$existing_code   = dpa_get_achievement_redemption_code( $post->ID );

	// Ensure sane defaults
	if ( empty( $existing_points ) )
		$existing_points = 0;

	if ( empty( $existing_target ) )
		$existing_target = 1;
?>

	<div class="misc-pub-section dpa-karma">
		<label for="dpa-points"><?php _e( 'Karma points:', 'dpa' ); ?></label>
		<input type="number" name="dpa_points" id="dpa-points" value="<?php echo esc_attr( $existing_points ); ?>" />
	</div>

	<div class="misc-pub-section dpa-type">
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
		<label for="dpa_target"><?php _ex( 'Events repeat:', "Number of times an achievement&#8217;s events need to repeat before the achievement is awarded", 'dpa' ); ?></label>
		<input type="number" name="dpa_target" id="dpa-target" min="1" value="<?php echo esc_attr( $existing_target ); ?>" />

		<p class="hint"><?php _e( "Number of times the events need to repeat before the achievement is awarded.", 'dpa' ); ?></p>
	</div>

	<div class="misc-pub-section dpa-redemption-code">
		<label for="dpa-code"><?php _e( 'Redemption code:', 'dpa' ); ?></label>
		<input id="dpa-code" value="<?php echo esc_attr( $existing_code ); ?>" name="dpa_code" type="text" />

		<p class="hint"><?php _e( "Users can enter this code into the Redemption widget to unlock the achievement.", 'dpa' ); ?></p>
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
 * @since Achievements (3.0)
 */
function dpa_achievement_metabox_save( $achievement_id ) {
	// Bail if doing an autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $achievement_id;

	// Bail if not a post request
	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return $achievement_id;

	// Bail if not saving an achievement post type
	if ( dpa_get_achievement_post_type() !== get_post_type( $achievement_id ) )
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
		if ( 'event' === $_POST['dpa_type'] )
			$type = 'event';
		else
			$type = 'award';
	}

	// Redemption code
	$redemption_code = isset( $_POST['dpa_code'] ) ? sanitize_text_field( stripslashes( $_POST['dpa_code'] ) ) : '';
	update_post_meta( $achievement_id, '_dpa_redemption_code', $redemption_code );

	// Event repeats count target
	$frequency = ! empty( $_POST['dpa_target'] ) ? absint( $_POST['dpa_target'] ) : 1;
	if ( $frequency < 1 )
		$frequency = 1;

	// Events
	$events = array();
	if ( 'event' === $type && ! empty( $_POST['dpa_event'] ) ) {
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
 * @since Achievements (3.0)
 */
function dpa_achievement_posts_columns( $columns ) {
	$columns = array(
		'cb'               => '<input type="checkbox" />',
		'dpa_thumb'        => _x( 'Image', 'Featured Image column title', 'dpa' ),
		'title'            => __( 'Title', 'dpa' ),
		'achievement_type' => _x( 'Type', 'Type of the achievement; award or badge', 'dpa' ),
		'categories'       => __( 'Categories', 'dpa' ),
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
 * @since Achievements (3.0)
 */
function dpa_achievement_custom_column( $column, $post_id ) {
	if ( 'karma' === $column ) {
		dpa_achievement_points( $post_id );

	} elseif ( 'achievement_type' === $column ) {
		$existing_events = wp_get_post_terms( $post_id, dpa_get_event_tax_id(), array( 'fields' => 'ids', ) );
		$existing_type   = empty( $existing_events ) ? __( 'Award', 'dpa' ) : __( 'Event', 'dpa' );
		echo $existing_type;

	} elseif ( 'dpa_thumb' === $column ) {
			the_post_thumbnail( 'dpa-thumb' );
	}
}

/**
 * Set the "achievement type" and "karma" columns as sortable on the achievement post type index screen
 *
 * @param array $columns
 * @return array
 * @since Achievements (3.0)
 */
function dpa_achievement_sortable_columns( $columns ) {
	$columns['karma']            = 'karma';
	$columns['achievement_type'] = 'achievement_type';

	return apply_filters( 'dpa_achievement_sortable_columns', $columns );
}

/**
 * Contextual help for the new/edit achievement CPT screen
 *
 * @since Achievements (3.0)
 */
function dpa_achievement_new_contextual_help() {
	// Bail out if we're not on the right screen
	if ( dpa_get_achievement_post_type() !== get_current_screen()->post_type )
		return;

	// Most of this was copied from wpcore
	$customise_display = '<p>' . __( 'The title field and the big achievement Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop, and can minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide or reveal more boxes (Featured Image, Achievements, Slug) or to choose a 1- or 2-column layout for this screen.', 'dpa' ) . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'customise-display',
		'title'   => __( 'Customizing This Display', 'dpa' ),
		'content' => $customise_display,
	) );

	$title_and_editor  = '<p>' . __( '<strong>Title</strong> - Enter a title for your achievement. After you enter a title, you&#8217;ll see the permalink below, which you can edit.', 'dpa' ) . '</p>';
	$title_and_editor .= '<p>' . __( '<strong>Achievement editor</strong> - Enter the text for your achievement. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The Text mode allows you to enter HTML along with your achievement text. Line breaks will be converted to paragraphs automatically. You can insert media files by clicking the icons above the achievement editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in Text mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular achievement editor.', 'dpa' ) . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'title-post-editor',
		'title'   => __( 'Title and Achievement Editor', 'dpa' ),
		'content' => $title_and_editor,
	) );

	$inserting_media  = '<p>' . __( 'You can upload and insert media (images, audio, documents, etc.) by clicking the Add Media button. You can select from the images and files already uploaded to the Media Library, or upload new media to add to your achievement. To create an image gallery, select the images to add and click the “Create a new gallery” button.', 'dpa' ) . '</p>';

	$inserting_media .= '<p>' . sprintf( __( 'You can also embed media from many popular websites including Twitter, YouTube, Flickr and others by pasting the media URL on its own line into the content of your post/page. Please refer to the WordPress Codex to <a href="%s">learn more about embeds</a>.', 'dpa' ), esc_url( 'http://codex.wordpress.org/Embeds' ) ) . '</p>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'inserting-media',
		'title'   => __( 'Inserting Media', 'dpa' ),
		'content' => $inserting_media,
	) );

	$publish_box  = '<p>' . __( 'Several boxes on this screen contain settings for how your achievement will be published, including:', 'dpa' ) . '</p>';
	$publish_box .= '<ul><li><p>' . __( "<strong>Publish</strong> - You can set the terms of publishing your achievement in the Publish box. For Status, Visibility, and Publish (immediately), click on the Edit link to reveal more options. Visibility includes options for password-protecting an achievement&#8217;s page or setting the achievement to not appear in lists on your site). Publish (immediately) allows you to set a future or past date and time, so you can schedule an achievement to be published in the future.", 'dpa' ) . '</p></li>';

	$publish_box .= '<li><p>' . __( '<strong>Featured Image</strong> - This allows you to associate an image with your achievement without inserting it into the big achievement Editing Area.', 'dpa' ) . '</p></li></ul>';

	get_current_screen()->add_help_tab( array(
		'id'      => 'publish-box',
		'title'   => __( 'Publish Settings', 'dpa' ),
		'content' => $publish_box,
	) );

	$achievements_box  = '<p>' . __( '<strong>Karma points</strong> - set the number of points (called karma) given to a user when they unlock an achievement.', 'dpa' ) . '</p>';
	$achievements_box .= '<p>' . __( '<strong>Type</strong> - there are two types of achievement, Award and Event. An Award is given by a site admin, whereas an Event is unlocked automatically when its criteria have been met.', 'dpa' ) . '</p>';
	$achievements_box .= '<p>' . __( '<strong>Event achievements</strong> - this field appears when you create an Event achievement. Use the dropdown box to choose the events that you want to trigger this achievement.', 'dpa' ) . '</p>';
	$achievements_box .= '<p>' . __( '<strong>Events repeat</strong> - for Event achievements, set the number of times the events need to occur before the achievement is awarded.', 'dpa' ) . '</p>';
	$achievements_box .= '<p>' . __( '<strong>Redemption code</strong> - users can enter this code into the Redemption widget to unlock the achievement.', 'dpa' ) . '</p>';

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

/**
 * Contextual help for the achievement CPT index screen
 *
 * @since Achievements (3.0)
 */
function dpa_achievement_index_contextual_help() {
	// Bail out if we're not on the right screen
	if ( dpa_get_achievement_post_type() !== get_current_screen()->post_type )
		return;

	// Most of this was copied from wpcore
	get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __( 'Overview', 'dpa' ),
	'content' =>
		'<p>' . __( 'This screen provides access to all of your achievements. You can customize the display of this screen to suit your workflow.', 'dpa' ) . '</p>'
	) );

	get_current_screen()->add_help_tab( array(
	'id'      => 'screen-content',
	'title'   => __( 'Screen Content', 'dpa' ),
	'content' =>
		'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:', 'dpa' ) . '</p>' .
		'<ul>' .
			'<li>' . __( 'You can hide/display columns based on your needs and decide how many achievements to list per screen using the Screen Options tab.', 'dpa' ) . '</li>' .
			'<li>' . __( 'You can filter the list of achievements by achievement status using the text links in the upper left to show All, Published, Draft, or Trashed achievements. The default view is to show all achievements.', 'dpa' ) . '</li>' .
			'<li>' . __( 'You can view achievements in a simple title list or with an excerpt. Choose the view you prefer by clicking on the icons at the top of the list on the right.', 'dpa' ) . '</li>' .
			'<li>' . __( 'You can refine the list to show only achievements from a specific month by using the dropdown menus above the posts list. Click the Filter button after making your selection.', 'dpa' ) . '</li>' .
		'</ul>'
	) );

	get_current_screen()->add_help_tab( array(
	'id'      => 'action-links',
	'title'   => __( 'Available Actions' ),
	'content' =>
		'<p>' . __( 'Hovering over a row in the achievements list will display action links that allow you to manage your achievement. You can perform the following actions:', 'dpa' ) . '</p>' .
		'<ul>' .
			'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that achievement. You can also reach that screen by clicking on the achievement title.', 'dpa' ) . '</li>' .
			'<li>' . __( '<strong>Quick Edit</strong> provides inline access to the metadata of your achievement, allowing you to update achievement details without leaving this screen.', 'dpa' ) . '</li>' .
			'<li>' . __( '<strong>Trash</strong> removes your achievement from this list and places it in the trash, from which you can permanently delete it.', 'dpa' ) . '</li>' .
			'<li>' . __( '<strong>Preview</strong> will show you what your draft achievement will look like if you publish it. View will take you to your live site to view the achievement. Which link is available depends on your achievement&#8217;s status.', 'dpa' ) . '</li>' .
		'</ul>'
	) );

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'dpa' ) . '</strong></p>' .
		'<p><a href="http://achievementsapp.com/" target="_blank">' . __( 'Achievements Website', 'dpa' ) . '</a></p>' .
		'<p><a href="http://wordpress.org/support/plugin/achievements/" target="_blank">' . __( 'Support Forums', 'dpa' ) . '</a></p>'
	);
}

/**
 * Custom user feedback messages for achievement post type
 *
 * @param array $messages
 * @return array
 * @since Achievements (3.4)
 */
function dpa_achievement_feedback_messages( $messages ) {
	global $post;

	// Bail out if we're not on the right screen
	if ( dpa_get_achievement_post_type() !== get_current_screen()->post_type )
		return;

	$achievement_url = dpa_get_achievement_permalink( $post );
	$post_date       = sanitize_post_field( 'post_date', $post->post_date, $post->ID, 'raw' );

	$messages[dpa_get_achievement_post_type()] = array(
		0 =>  '', // Left empty on purpose

		// Updated
		1 =>  sprintf( __( 'Achievement updated. <a href="%s">View achievement</a>', 'dpa' ), $achievement_url ),

		// Custom field updated
		2 => __( 'Custom field updated.', 'dpa' ),

		// Custom field deleted
		3 => __( 'Custom field deleted.', 'dpa' ),

		// Achievement updated
		4 => __( 'Achievement updated.', 'dpa' ),

		// Restored from revision
		// translators: %s: date and time of the revision
		5 => isset( $_GET['revision'] )
				 ? sprintf( __( 'Achievement restored to revision from %s', 'dpa' ), wp_post_revision_title( (int) $_GET['revision'], false ) )
				 : false,

		// Achievement created
		6 => sprintf( __( 'Achievement created. <a href="%s">View achievement</a>', 'dpa' ), $achievement_url ),

		// Achievement saved
		7 => __( 'Achievement saved.', 'dpa' ),

		// Achievement submitted
		8 => sprintf( __( 'Achievement submitted. <a target="_blank" href="%s">Preview achievement</a>', 'dpa' ), esc_url( add_query_arg( 'preview', 'true', $achievement_url ) ) ),

		// Achievement scheduled
		9 => sprintf( __( 'Achievement scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview achievement</a>', 'dpa' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'dpa' ),
				strtotime( $post_date ) ),
				$achievement_url ),

		// Achievement draft updated
		10 => sprintf( __( 'Achievement draft updated. <a target="_blank" href="%s">Preview topic</a>', 'dpa' ), esc_url( add_query_arg( 'preview', 'true', $achievement_url ) ) ),
	);

	return $messages;
}

/**
 * Redirect user to Achievements' "What's New" page on activation
 *
 * @since Achievements (3.4)
 */
function dpa_do_activation_redirect() {

	// Bail if no activation redirect
	if ( ! get_transient( '_dpa_activation_redirect' ) )
		return;

	delete_transient( '_dpa_activation_redirect' );

	// Bail if activating from network, or bulk.
	if ( isset( $_GET['activate-multi'] ) )
		return;

	$query_args = array( 'page' => 'achievements-about' );

	if ( get_transient( '_dpa_is_new_install' ) ) {
		$query_args['is_new_install'] = '1';
		delete_transient( '_dpa_is_new_install' );
	}

	wp_safe_redirect( add_query_arg( $query_args, admin_url( 'index.php' ) ) );
}