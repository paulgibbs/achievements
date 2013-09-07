<?php
/**
 * Leaderboard loop - single item
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<tr id="dpa-leaderboard-user-<?php dpa_leaderboard_user_id(); ?>" <?php dpa_leaderboard_user_class(); ?>>

	<?php do_action( 'dpa_template_in_leaderboard_loop_early' ); ?>


	<th scope="row" headers="dpa-leaderboard-position">
		<?php do_action( 'dpa_template_before_leaderboard_position' ); ?>

		<?php dpa_leaderboard_user_position(); ?>

		<?php do_action( 'dpa_template_after_leaderboard_position' ); ?>
	</th>


	<td headers="dpa-leaderboard-name">
		<?php do_action( 'dpa_template_before_leaderboard_name' ); ?>

		<?php dpa_leaderboard_user_display_name(); ?>

		<?php do_action( 'dpa_template_after_leaderboard_name' ); ?>
	</td>


	<td headers="dpa-leaderboard-karma">
		<?php do_action( 'dpa_template_before_leaderboard_karma' ); ?>

		<?php dpa_leaderboard_user_karma(); ?>

		<?php do_action( 'dpa_template_after_leaderboard_karma' ); ?>
	</td>


	<?php do_action( 'dpa_template_in_leaderboard_loop_late' ); ?>

</tr>
