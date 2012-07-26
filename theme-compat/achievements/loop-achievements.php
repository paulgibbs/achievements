<?php
/**
 * Achievements loop
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

?>

<?php do_action( 'dpa_template_before_achievements_loop' ); ?>

<?php while ( dpa_achievements() ) : dpa_the_achievement(); ?>
	<?php dpa_get_template_part( 'loop', 'single-achievement' ); ?>
<?php endwhile; ?>

<?php do_action( 'dpa_template_after_achievements_loop' ); ?>
