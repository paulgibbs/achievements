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
 * Redeem achievements widget
 *
 * This widget consists of a text box, allowing users to type in a code and unlock an associated achievement.
 *
 * @since Achievements (3.1)
 */
class DPA_Redeem_Achievements_Widget extends WP_Widget {

	/**
	 * Redeem achievements widget
	 *
	 * @since Achievements (3.1)
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'dpa_redeem_achievements_widget_options', array(
			'classname'   => 'widget_dpa_redeem_achievements',
			'description' => __( 'Users can redeem achievements entering a code.', 'dpa' )
		) );

		parent::__construct( false, __( '(Achievements) Redemption', 'dpa' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since Achievements (3.1)
	 */
	public static function register_widget() {
		register_widget( 'DPA_Redeem_Achievements_Widget' );
	}

	/**
	 * Displays the output
	 *
	 * @param array $args
	 * @param array $instance
	 * @since Achievements (3.1)
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Redeem achievement', 'dpa' );
		$title = apply_filters( 'dpa_redeem_achievements_widget_title', $title, $instance, $this->id_base );

		// WordPress filters widget_title through esc_html.
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];

		dpa_get_template_part( 'form-redeem-code' );

		echo $args['after_widget'];
	}

	/**
	 * Update the widget options
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 * @since Achievements (3.1)
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Output the widget options form
	 *
	 * @param $instance Instance
	 * @since Achievements (3.1)
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Redeem achievement', 'dpa' );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'dpa' ); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>

		<?php
	}
}