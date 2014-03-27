<?php
/**
 * Pagination for pages of achievements
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'dpa_template_before_pagination_loop' ); ?>

<div class="dpa-pagination">
	<div class="dpa-pagination-count">

		<?php dpa_achievement_pagination_count(); ?>

	</div>

	<div class="dpa-pagination-links">

		<?php dpa_achievement_pagination_links(); ?>

	</div>
</div>

<?php do_action( 'dpa_template_after_pagination_loop' ); ?>
