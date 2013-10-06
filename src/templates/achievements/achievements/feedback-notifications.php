<?php
/**
 * Notifications template part; usually used for the "you've unlocked an achievement!" pop-ups.
 *
 * @package Achievements
 * @since Achievements (3.5)
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<script type="text/html" id="tmpl-achievements-wrapper">
	<div id="dpa-notifications-wrapper" style="display: none">
		<ul id="dpa-notifications" aria-live="polite">
			<h1><?php _e( 'Achievement Unlocked', 'dpa' ); ?></h1>

		</ul>
	</div><!-- #dpa-notifications-wrapper -->
</script>

<script type="text/html" id="tmpl-achievements-item">
	<li aria-live="polite" class="dpa-notification" id="dpa-notification-id-{{ data.ID }}">
		<# if (data.image_url) { #>
			<a href="{{ data.permalink }}"><img src="{{ data.image_url }}" class="attachment-medium dpa-achievement-unlocked-thumbnail" /></a>
		<# } #>

		<h2>{{ data.title }}</h2>

		<div>
			<p><?php _e( "Hey, you've unlocked the ??? achievement. Congratulations!", 'dpa' ); ?></p><?php /* DJPAULTODO: use WP localise string function */ ?>

			<p><?php
				printf(
					__( 'Celebrate and share with your friends on %1$s and %2$s.', 'dpa' ),
					sprintf( '<a href="%1$s" target="_new">%2$s</a>', esc_url( 'djpaultodo' ), __( 'Facebook', 'dpa' ) ),
					sprintf( '<a href="%1$s" target="_new">%2$s</a>', esc_url( 'djpaultodo' ), __( 'Twitter',  'dpa' ) )
				);
			?></p>

			<p><a class="dpa-notification-cta" href="#"><?php _e( 'See your other achievements', 'dpa' ); ?></a></p>
		</div>
	</li>
</script>