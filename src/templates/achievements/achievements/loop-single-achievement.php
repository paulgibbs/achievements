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


	<?php if ( has_post_thumbnail( dpa_get_the_achievement_id() ) ) : ?>

		<?php do_action( 'dpa_template_before_achievement_image' ); ?>

		<?php echo the_post_thumbnail( 'dpa-grid', array( 'class' => 'dpa-archive-achievements-image', 'alt' => dpa_get_achievement_title() ) ); ?>

		<?php do_action( 'dpa_template_after_achievement_image' ); ?>

	<?php endif; ?>


	<div class="dpa-archive-achievements-description">

		<?php do_action( 'dpa_template_before_achievement_name' ); ?>

		<a href="<?php dpa_achievement_permalink(); ?>"><?php dpa_achievement_title(); ?></a>

		<?php do_action( 'dpa_template_after_achievement_name' ); ?>


		<?php do_action( 'dpa_template_before_achievement_excerpt' ); ?>

		<?php dpa_achievement_excerpt(); ?>

		<?php do_action( 'dpa_template_after_achievement_excerpt' ); ?>

	</div>


	<?php do_action( 'dpa_template_in_achievements_loop_late' ); ?>

</li>
