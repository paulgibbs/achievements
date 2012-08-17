<?php
/**
 * Single Achievement content part
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */
?>

<div id="dpa-achievements">

	<?php dpa_breadcrumb(); ?>

	<?php do_action( 'dpa_template_before_single_achievement' ); ?>

	<ul class="dpa-achievements dpa-single-achievement">

		<li id="achievement-<?php dpa_achievement_id(); ?>" <?php dpa_achievement_class(); ?>>

			<?php do_action( 'dpa_theme_before_achievement_title' ); ?>

			<?php dpa_achievement_title(); ?>

			<?php do_action( 'dpa_theme_after_achievement_title' ); ?>


			<?php do_action( 'dpa_theme_before_achievement_content' ); ?>

			<?php dpa_achievement_content(); ?>

			<?php do_action( 'dpa_theme_after_achievement_content' ); ?>

		</li><!-- #achievement-<?php dpa_achievement_id(); ?> -->

	</ul><!-- #dpa-achievements -->

	<?php // DJPAULTODO. Show users who have unlocked this achievement below. And add new pair of actions ?>

	<?php do_action( 'dpa_template_after_single_achievement' ); ?>

</div>
