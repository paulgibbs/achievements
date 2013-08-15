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
 * Leaderboard widget; displays who da winners are!
 *
 * @since Achievements (3.4)
 */
class DPA_Leaderboard_Widget extends WP_Widget {

	/**
	 * Leadeboard widget
	 *
	 * @since Achievements (3.4)
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'dpa_leaderboard_widget_options', array(
			'classname'   => 'widget_dpa_leaderboard',
			'description' => __( 'Your users ranked by their karma points.', 'dpa' ),
		) );

		parent::__construct( false, __( '(Achievements) Leaderboard', 'dpa' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Achievements (3.4)
	 */
	static public function register_widget() {
		register_widget( 'DPA_Leaderboard_Widget' );
	}

	/**
	 * Displays the output
	 *
	 * @param array $args
	 * @param array $instance
	 * @since Achievements (3.4)
	 */
	public function widget( $args, $instance ) {
		$settings = $this->parse_settings( $instance );

		// Use this filter
		$settings['title']    = apply_filters( 'dpa_available_achievements_title', $settings['title'], $instance, $this->id_base );

		// WordPress filters widget_title through esc_html.
		$settings['title'] = apply_filters( 'widget_title', $settings['title'], $instance, $this->id_base );

		echo $args['before_widget'];
		echo $args['before_title'] . $settings['title'] . $args['after_title'];

		dpa_get_template_part( 'content-leaderboard', 'widget' );

		echo $args['after_widget'];
	}

	/**
	 * Deals with the settings when they are saved by the admin.
	 *
	 * @param array $new_instance New settings
	 * @param array $old_instance Old settings
	 * @return array The validated and (if necessary) amended settings
	 * @since Achievements (3.4)
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the widget options form
	 *
	 * @param array $instance The instance of the widget.
	 * @since Achievements (3.4)
	 */
	public function form( $instance ) {
		$settings = $this->parse_settings( $instance );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'dpa' ); ?></label><br />
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" />
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @param array $instance Optional; the instance of the widget.
	 * @since Achievements (3.4)
	 */
	public function parse_settings( array $instance = array() ) {
		return dpa_parse_args( $instance, array(
			'title'    => __( 'Leaderboard', 'dpa' ),
		), 'leaderboard_widget_settings' );
	}
}