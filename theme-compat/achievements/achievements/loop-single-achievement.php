<?php
/**
 * Achievements loop - single
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */
?>

<tr id="achievement-<?php dpa_achievement_id(); ?>" <?php dpa_achievement_class(); ?>>

	<?php do_action( 'dpa_template_in_achievements_loop_early' ); ?>


	<td headers="achievements-archive-name">
		<?php do_action( 'dpa_template_before_achievement_name' ); ?>

		<a href="<?php dpa_achievement_permalink(); ?>"><?php dpa_achievement_title(); ?></a>

		<?php do_action( 'dpa_template_after_achievement_name' ); ?>
	</td>


	<td headers="achievements-archive-karma">
		<?php do_action( 'dpa_template_before_achievement_karma' ); ?>

		<?php echo '100'; ?>

		<?php do_action( 'dpa_template_after_achievement_karma' ); ?>
	</td>


	<td headers="achievements-archive-excerpt">
		<?php do_action( 'dpa_template_before_achievement_excerpt' ); ?>

		<?php dpa_achievement_excerpt(); ?>

		<?php do_action( 'dpa_template_after_achievement_excerpt' ); ?>
	</td>


	<?php do_action( 'dpa_template_in_achievements_loop_late' ); ?>

</tr>
