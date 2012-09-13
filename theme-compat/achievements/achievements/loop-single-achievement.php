<?php
/**
 * Achievements loop - single
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */
?>

<li id="achievement-<?php dpa_achievement_id(); ?>" <?php dpa_achievement_class(); ?>>

	<?php do_action( 'dpa_theme_before_achievement_title' ); ?>

	<a class="dpa-achievement-permalink" href="<?php dpa_achievement_permalink(); ?>"><?php dpa_achievement_title(); ?></a>

	<?php do_action( 'dpa_theme_after_achievement_title' ); ?>

	<div class="dpa-achievement-body">

		<?php do_action( 'dpa_theme_before_achievement_logo' ); ?>

		<?php if ( has_post_thumbnail( dpa_get_achievement_id() ) ) : ?>

			<a class="dpa-achievement-permalink" href="<?php the_permalink(); ?>">
				<?php echo get_the_post_thumbnail( dpa_get_achievement_id(), 'full', array( 'class' => 'dpa-achievement-logo' ) ); ?>
			</a>

		<?php endif; ?>

		<?php do_action( 'dpa_theme_after_achievement_logo' ); ?>


		<?php do_action( 'dpa_theme_before_achievement_excerpt' ); ?>
		
			<?php dpa_achievement_excerpt(); ?>
		
		<?php do_action( 'dpa_theme_after_achievement_excerpt' ); ?>

	</div>

</li><!-- #achievement-<?php dpa_achievement_id(); ?> -->
