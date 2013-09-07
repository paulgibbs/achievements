<?php
/**
 * Author Achievement content part
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div id="dpa-achievements">

	<?php do_action( 'dpa_template_before_author_achievements' ); ?>

	<?php if ( dpa_has_progress() && ! empty( achievements()->achievement_query->posts ) ) : ?>

		<?php dpa_get_template_part( 'pagination-author-achievements' ); ?>

		<?php dpa_get_template_part( 'loop-achievements'              ); ?>

		<?php dpa_get_template_part( 'pagination-author-achievements' ); ?>

	<?php else : ?>

		<?php dpa_get_template_part( 'feedback-no-achievements' ); ?>

	<?php endif; ?>

	<?php do_action( 'dpa_template_after_author_achievements' ); ?>

</div>
