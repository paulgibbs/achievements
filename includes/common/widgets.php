<?php
/**
 * Achievements widgets
 *
 * Contains the forum list, topic list, reply list and login form widgets.
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
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		if ( empty( $title ) )
			$title = __( 'Redeem achievement', 'dpa' );

		$title = apply_filters( 'dpa_redeem_achievements_widget_title', $instance['title'], $instance, $this->id_base );

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
		$instance['title'] = strip_tags( $new_instance['title'] );

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
	function __construct() {
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
		$settings['post_id'] = (int) apply_filters( 'dpa_featured_achievement_post_id', $settings['post_id'], $instance, $this->id_base );

		// Get the specified achievement
		$achievement = get_posts( array(
			'numberposts'      => 1,
			'no_found_rows'    => true,
			'p'                => $settings['post_id'],
			'post_status'      => 'publish',
			'post_type'        => dpa_get_achievement_post_type(),
			'suppress_filters' => false,
		) );

		// Bail if it doesn't exist
		if ( empty( $achievement ) )
			return;

		$achievement = array_shift( $achievement );

		echo $args['before_widget'];
		echo $args['before_title'];
		dpa_achievement_title( $achievement->ID );
		echo $args['after_title'];


			<a href="<?php dpa_achievement_permalink( $achievement->ID ); ?>"><?php echo get_the_post_thumbnail( $achievement->ID, 'thumbnail', array( 'alt' => dpa_get_achievement_title( $achievement->ID ) ) ); ?></a>
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
		$instance['post_id'] = (int) $new_instance['post_id'];

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
	public function parse_settings( $instance = array() ) {
		return dpa_parse_args( $instance, array(
			'post_id' => 0,
		), 'featured_achievement_widget_settings' );
	}
}
