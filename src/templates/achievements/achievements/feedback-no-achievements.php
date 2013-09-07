<?php
/**
 * No achievements found template part
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="dpa-template-notice">

	<?php if ( dpa_is_single_user_achievements() ) : ?>

		<p><?php printf( __( '%1$s hasn&rsquo;t unlocked any achievements.', 'dpa' ), get_the_author_meta( 'display_name', dpa_get_displayed_user_id() ) ); ?>

	<?php else : ?>

		<p><?php _e( 'This is somewhat embarrassing, isn&rsquo;t it? No achievements were found.', 'dpa' ); ?></p>

	<?php endif ?>

</div>
