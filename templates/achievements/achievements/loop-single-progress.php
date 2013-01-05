<?php
/**
 * Progress loop - single
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<li id="dpa-progress-<?php dpa_progress_id(); ?>" <?php dpa_progress_class(); ?>>

	<?php do_action( 'dpa_template_in_progress_loop_early' ); ?>


	<?php do_action( 'dpa_template_before_progress_user_avatar' ); ?>

	<?php dpa_progress_user_avatar(); ?>

	<?php do_action( 'dpa_template_after_progress_user' ); ?>

	<div>

		<?php do_action( 'dpa_template_before_progress_user_link' ); ?>

		<?php dpa_progress_user_link(); ?>


		<?php do_action( 'dpa_template_before_progress_date' ); ?>

		<br><?php dpa_progress_date(); ?>

		<?php do_action( 'dpa_template_after_progress_date' ); ?>

	</div>


	<?php do_action( 'dpa_template_in_progress_loop_late' ); ?>

</li>
