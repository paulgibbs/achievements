<?php
/**
 * Achievements loop
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<?php do_action( 'dpa_template_before_achievements_loop_block' ); ?>

<ul class="dpa-archive-achievements-grid js-masonry" data-masonry-options='{ "columnWidth": "li", "gutter": 10, "itemSelector": "li", "isOriginLeft": <?php echo is_rtl() ? 'false' : 'true'; ?> }'>

	<?php do_action( 'dpa_template_before_achievements_loop' ); ?>

	<?php while ( dpa_achievements() ) : dpa_the_achievement(); ?>

		<?php dpa_get_template_part( 'loop-single-achievement' ); ?>

	<?php endwhile; ?>

	<?php do_action( 'dpa_template_after_achievements_loop' ); ?>

</ul><!-- .dpa-archive-achievements-grid -->

<?php do_action( 'dpa_template_after_achievements_loop_block' ); ?>