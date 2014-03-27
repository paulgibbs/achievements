<?php
/**
 * Pagination for leaderboard
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'dpa_template_before_leaderboard_pagination_loop' ); ?>

<div class="dpa-pagination">
	<div class="dpa-pagination-links">

		<?php dpa_leaderboard_pagination_links(); ?>

	</div>
</div>

<?php do_action( 'dpa_template_after_leaderboard_pagination_loop' ); ?>