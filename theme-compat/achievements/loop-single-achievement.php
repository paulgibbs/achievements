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

	<a class="dpa-achievement-permalink" href="<?php dpa_achievement_permalink(); ?>" title="<?php dpa_achievement_title(); ?>"><?php dpa_achievement_title(); ?></a>

	<?php do_action( 'dpa_theme_after_achievement_title' ); ?>

</li><!-- #achievement-<?php dpa_achievement_id(); ?> -->
