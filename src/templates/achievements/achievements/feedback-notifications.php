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
	<ul id="dpa-toaster" style="display: none">
		<h1><?php _e( 'Achievements Unlocked!', 'dpa' ); ?></h1>
	</ul>
</script>

<script type="text/html" id="tmpl-achievements-item">
	<li aria-live="polite" class="dpa-toast" id="dpa-toaster-id-{{ data.ID }}">
		<# if (data.image_url) { #>
			<a href="{{ data.permalink }}"><img class="attachment-medium dpa-achievement-unlocked-thumbnail" src="{{ data.image_url }}"  style="width: {{ data.image_width }}px" /></a>
		<# } #>

		<h2>{{ data.title }}</h2>
		<p><?php _e( "Hey, you've unlocked the ??? achievement. Congratulations!", 'dpa' ); ?></p><?php /* DJPAULTODO: use WP localise string function */ ?>

		<ul class="dpa-toaster-bottom">
			<li>[Share on Twitter]</li>
			<li>[Share on Facebook]</li>
		</ul>
	</li>
</script>