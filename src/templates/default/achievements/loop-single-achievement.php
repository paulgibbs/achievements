<?php
/**
 * Achievements loop - single
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<li data-achievement-id="<?php dpa_achievement_id(); ?>" id="dpa-achievement-<?php dpa_achievement_id(); ?>" <?php dpa_achievement_class(); ?>>

	<?php do_action( 'dpa_template_in_achievements_loop_early' ); ?>


	<a href="<?php dpa_achievement_permalink(); ?>">
		<?php if ( has_post_thumbnail( dpa_get_the_achievement_id() ) ) : ?>

			<?php do_action( 'dpa_template_before_achievement_image' ); ?>

			<?php echo the_post_thumbnail( 'post-thumbnail', array( 'class' => 'dpa-archive-achievements-image', 'alt' => dpa_get_achievement_title() ) ); ?>

			<?php do_action( 'dpa_template_after_achievement_image' ); ?>

		<?php endif; ?>


		<?php do_action( 'dpa_template_before_achievement_name' ); ?>

		<?php dpa_achievement_title(); ?>

		<?php do_action( 'dpa_template_after_achievement_name' ); ?>
	</a>


	<?php do_action( 'dpa_template_in_achievements_loop_late' ); ?>

</li>
