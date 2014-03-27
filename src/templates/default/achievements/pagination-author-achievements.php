<?php
/**
 * Pagination for the progress/achievements on the author pages
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'dpa_template_before_author_pagination_loop' ); ?>

<div class="dpa-pagination dpa-author-pagination">
	<div class="dpa-pagination-count">

		<?php dpa_progress_pagination_count(); ?>

	</div>
</div>

<?php do_action( 'dpa_template_after_author_pagination_loop' ); ?>
