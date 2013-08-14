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
 * Featured Achievement widget
 *
 * @since Achievements (3.3)
 */
class DPA_Featured_Achievement_Widget extends WP_Widget {

	/**
	 * Featured Achievement widget
	 *
	 * @since Achievements (3.3)
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'dpa_featured_achievement_widget_options', array(
			'classname'   => 'widget_dpa_featured_achievement',
			'description' => __( 'Display details of a single achievement.', 'dpa' ),
		) );

		parent::__construct( false, __( '(Achievements) Featured Achievement', 'dpa' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Achievements (3.3)
	 */
	static public function register_widget() {
		register_widget( 'DPA_Featured_Achievement_Widget' );
	}

	/**
	 * Displays the output
	 *
	 * @param array $args
	 * @param array $instance
	 * @since Achievements (3.3)
	 */
	public function widget( $args, $instance ) {
		$settings            = $this->parse_settings( $instance );
		$settings['post_id'] = absint( apply_filters( 'dpa_featured_achievement_post_id', $settings['post_id'], $instance, $this->id_base ) );

		// Get the specified achievement
		$achievement = get_posts( array(
			'no_found_rows'    => true,
			'numberposts'      => 1,
			'p'                => $settings['post_id'],
			'post_status'      => 'publish',
			'post_type'        => dpa_get_achievement_post_type(),
			'suppress_filters' => false,
		) );

		// Bail if it doesn't exist
		if ( empty( $achievement ) )
			return;

		$achievement = array_shift( $achievement );
		$title       = dpa_get_achievement_title( $achievement->ID );

		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];

		if ( has_post_thumbnail( $achievement->ID ) ) : ?>
			<a href="<?php dpa_achievement_permalink( $achievement->ID ); ?>"><?php echo get_the_post_thumbnail( $achievement->ID, 'thumbnail', array( 'alt' => $title ) ); ?></a>
		<?php endif;

		dpa_achievement_excerpt( $settings['post_id'] );
		echo $args['after_widget'];
	}

	/**
	 * Update the forum widget options
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 * @since Achievements (3.3)
	 */
	public function update( $new_instance, $old_instance ) {
		$instance            = $old_instance;
		$instance['post_id'] = absint( $new_instance['post_id'] );

		return $instance;
	}

	/**
	 * Output the widget options form
	 *
	 * @param array $instance The instance of the widget.
	 * @since Achievements (3.3)
	 */
	public function form( $instance ) {
		$settings = $this->parse_settings( $instance );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'post_id' ); ?>"><?php _e( 'Achievement ID:', 'dpa' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'post_id' ); ?>" name="<?php echo $this->get_field_name( 'post_id' ); ?>" type="text" value="<?php echo esc_attr( $settings['post_id'] ); ?>" />
			</label>
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
			'post_id' => 0,
		), 'featured_achievement_widget_settings' );
	}
}