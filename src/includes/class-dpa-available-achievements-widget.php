<?php
/**
 * Achievements widgets
 *
 * @package Achievements
 * @subpackage CommonWidgets
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Available Achievements widget; displays a grid of pictures of available Achievements.
 *
 * @since Achievements (2.0)
 */
class DPA_Available_Achievements_Widget extends WP_Widget {

	/**
	 * Featured Achievement widget
	 *
	 * @since Achievements (3.3)
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'dpa_available_achievements_widget_options', array(
			'classname'   => 'widget_dpa_available_achievements',
			'description' => __( 'Displays a photo grid of the achievements.', 'dpa' ),
		) );

		parent::__construct( false, __( '(Achievements) Photo Grid', 'dpa' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Achievements (3.3)
	 */
	static public function register_widget() {
		register_widget( 'DPA_Available_Achievements_Widget' );
	}

	/**
	 * Displays the output
	 *
	 * @param array $args
	 * @param array $instance
	 * @since Achievements (2.0)
	 */
	public function widget( $args, $instance ) {
		$settings = $this->parse_settings( $instance );

		// Use these filters
		$settings['limit'] = absint( apply_filters( 'dpa_available_achievements_limit', $settings['limit'], $instance, $this->id_base ) );
		$settings['title'] = apply_filters( 'dpa_available_achievements_title', $settings['title'], $instance, $this->id_base );

		// WordPress filters widget_title through esc_html.
		$settings['title'] = apply_filters( 'widget_title', $settings['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( ! empty( $settings['title'] ) )
			echo $args['before_title'] . $settings['title'] . $args['after_title'];

		// Get the posts
		$achievements = get_posts( array(
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'numberposts'         => $settings['limit'],
			'post_status'         => 'publish',
			'post_type'           => dpa_get_achievement_post_type(),
			'suppress_filters'    => false,
		) );

		// Bail if no posts
		if ( empty( $achievements ) )
			return;

		echo '<ul>';

		foreach ( $achievements as $post ) {
			if ( has_post_thumbnail( $post->ID ) ) :
			?>

				<li>
					<a href="<?php dpa_achievement_permalink( $post->ID ); ?>"><?php echo get_the_post_thumbnail( $post->ID, 'dpa-thumb', array( 'alt' => dpa_get_achievement_title( $post->ID ) ) ); ?></a>
				</li>

			<?php
			endif;
		}

		echo '</ul>';
		echo $args['after_widget'];
	}

	/**
	 * Deals with the settings when they are saved by the admin.
	 *
	 * @param array $new_instance New settings
	 * @param array $old_instance Old settings
	 * @return array The validated and (if necessary) amended settings
	 * @since Achievements (2.0)
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['limit'] = absint( $new_instance['limit'] );
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the widget options form
	 *
	 * @param array $instance The instance of the widget.
	 * @since Achievements (2.0)
	 */
	public function form( $instance ) {
		$settings = $this->parse_settings( $instance );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'dpa' ); ?></label><br />
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Show up to this many items:', 'dpa' ); ?></label><br />
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" min="1" value="<?php echo esc_attr( $settings['limit'] ); ?>" />
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @param array $instance Optional; the instance of the widget.
	 * @since Achievements (3.3)
	 */
	public function parse_settings( array $instance = array() ) {
		return dpa_parse_args( $instance, array(
			'limit' => 40,
			'title' => __( 'Available achievements', 'dpa' ),
		), 'available_achievements_widget_settings' );
	}
}