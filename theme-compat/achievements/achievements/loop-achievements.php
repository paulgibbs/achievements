<?php
/**
 * Achievements loop
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */
?>

<?php do_action( 'dpa_template_before_achievements_loop_block' ); ?>

<table class="archive-achievements">
	<caption class="screen-reader-text"><?php _e( 'All of the available achievements with the name, avatar, and karma points for each.', 'dpa' ); ?></caption>

	<thead>
		<tr>

			<th id="achievements-archive-name" scope="col"><?php _ex( 'Achievement Name', 'column header for list of achievements', 'dpa' ); ?></th>
			<th id="achievements-archive-karma" scope="col"><?php _ex( 'Karma', 'column header for list of achievements', 'dpa' ); ?></th>
			<th id="achievements-archive-excerpt" scope="col"><?php _ex( 'Description', 'column header for list of achievements', 'dpa' ); ?></th>

		</tr>
	</thead>

	<tfoot>
		<tr>

			<th scope="col"><?php _ex( 'Achievement Name', 'column header for list of achievements', 'dpa' ); ?></th>
			<th scope="col"><?php _ex( 'Karma', 'column header for list of achievements', 'dpa' ); ?></th>
			<th scope="col"><?php _ex( 'Description', 'column header for list of achievements', 'dpa' ); ?></th>

		</tr>
	</tfoot>

	<tbody>

		<?php do_action( 'dpa_template_before_achievements_loop' ); ?>

		<?php while ( dpa_achievements() ) : dpa_the_achievement(); ?>

			<?php dpa_get_template_part( 'loop', 'single-achievement' ); ?>

		<?php endwhile; ?>

		<?php do_action( 'dpa_template_after_achievements_loop' ); ?>

	</tbody>

</table>

<?php do_action( 'dpa_template_after_achievements_loop_block' ); ?>
