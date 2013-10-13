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
	<ul aria-live="polite" id="dpa-toaster" role="status" style="display: none">
		<h1><?php _e( 'Achievements Unlocked!', 'dpa' ); ?></h1>
	</ul>
</script>

<script type="text/html" id="tmpl-achievements-item">
	<li class="dpa-toast" id="dpa-toaster-id-{{ data.ID }}">
		<# if (data.image_url) { #>
			<a href="{{ data.permalink }}"><img class="attachment-medium dpa-achievement-unlocked-thumbnail" src="{{ data.image_url }}"  style="width: {{ data.image_width }}px" /></a>
		<# } #>

		<h2>{{ data.title }}</h2>
		<p>
			<?php
			// translators: "{{ data.title }}" will be replaced with the name of the achievement; leave this bit exactly as is.
			_e( "Hey, you&#8217;ve unlocked the &#8220;{{ data.title }}&#8221; achievement. Congratulations!", 'dpa' );
			?>
		</p>

		<p><?php
			$facebook_url = '';
			$twitter_url = '';
			printf(
				__( 'Celebrate and share with your friends on %1$s and %2$s.', 'dpa' ),
				sprintf( '<a href="%1$s" target="_new">%2$s</a>', esc_url( $facebook_url ), __( 'Facebook', 'dpa' ) ),
				sprintf( '<a href="%1$s" target="_new">%2$s</a>', esc_url( $twitter_url ),  __( 'Twitter',  'dpa' ) )
			);
		?></p>
	</li>
</script>