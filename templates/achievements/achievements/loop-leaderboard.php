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
	<caption class="screen-reader-text"><?php _e( 'A leaderboard of all users on the site who have earnt karma points. The table is sorted descendingly by the amount of karma points that the users have, and also shows the user&#8217;s name and karma points total.', 'dpa' ); ?></caption>

	<thead>
		<tr>

			<th id="dpa-leaderboard-position" scope="col"><?php /* translations: indicates a numeric position in a leaderboard */ _ex( '#', 'user position column header for leaderboard table', 'dpa' ); ?></th>
			<th id="dpa-leaderboard-name" scope="col"><?php _ex( 'Name', 'user name column header for leaderboard table', 'dpa' ); ?></th>
			<th id="dpa-leaderboard-karma" scope="col"><?php _ex( 'Karma', 'column header for leaderboard table', 'dpa' ); ?></th>

		</tr>
	</thead>

	<tfoot class="screen-reader-text">
		<tr>

			<th scope="col"><?php /* translations: indicates a numeric position in a leaderboard */ _ex( '#', 'user position column header for leaderboard table', 'dpa' ); ?></th>
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
