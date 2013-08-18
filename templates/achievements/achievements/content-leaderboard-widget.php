<?php
/**
 * Leaderboard widget content part
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'dpa_template_before_leaderboard' ); ?>

<?php if ( dpa_has_leaderboard() ) : ?>

	<?php dpa_get_template_part( 'loop-leaderboard'       ); ?>

	<?php dpa_get_template_part( 'pagination-leaderboard' ); ?>

<?php endif; ?>

<?php do_action( 'dpa_template_after_leaderboard' ); ?>