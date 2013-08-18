<?php
/**
 * Leaderboard loop
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'dpa_template_before_leaderboard_loop_block' ); ?>

<table class="dpa-leaderboard-widget">
	<caption class="screen-reader-text"><?php _e( 'All of the available achievements with the name, avatar, and karma points for each.', 'dpa' ); ?></caption>

	<thead>
		<tr>

			<th id="dpa-leaderboard-position" scope="col"><?php _ex( 'Position', 'user position column header for leaderboard table', 'dpa' ); ?></th>
			<th id="dpa-leaderboard-name" scope="col"><?php _ex( 'Name', 'user name column header for leaderboard table', 'dpa' ); ?></th>
			<th id="dpa-leaderboard-karma" scope="col"><?php _ex( 'Karma', 'column header for leaderboard table', 'dpa' ); ?></th>

		</tr>
	</thead>

	<tfoot>
		<tr>

			<th scope="col"><?php _ex( 'Position', 'user position column header for leaderboard table', 'dpa' ); ?></th>
			<th scope="col"><?php _ex( 'Name', 'user name column header for leaderboard table', 'dpa' ); ?></th>
			<th scope="col"><?php _ex( 'Karma', 'column header for leaderboard table', 'dpa' ); ?></th>

		</tr>
	</tfoot>

	<tbody>

		<?php do_action( 'dpa_template_before_leaderboard_loop' ); ?>

		<?php while ( dpa_leaderboard_has_users() ) : dpa_the_leaderboard_user(); ?>

			<?php dpa_get_template_part( 'loop-single-leaderboard' ); ?>

		<?php endwhile; ?>

		<?php do_action( 'dpa_template_after_leaderboard_loop' ); ?>

	</tbody>

</table>

<?php do_action( 'dpa_template_after_leaderboard_loop_block' ); ?>
