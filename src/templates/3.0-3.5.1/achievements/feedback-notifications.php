<?php
/**
 * Notifications template part; usually used for the "you've unlocked an achievement!" pop-ups.
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<script type="text/html" id="tmpl-achievements-wrapper">
	<ul aria-live="polite" id="dpa-toaster" role="status" style="display: none">
		<h1><?php _e( 'Achievement unlocked!', 'achievements' ); ?></h1>
	</ul>
</script>

<script type="text/html" id="tmpl-achievements-item">
	<li class="dpa-toast" id="dpa-toast-id-{{ data.ID }}">
		<# if (data.image_url) { #>
			<a href="{{ data.permalink }}"><img class="attachment-medium dpa-toast-image" src="{{ data.image_url }}"  style="width: {{ data.image_width }}px" /></a>
		<# } #>

		<h2>{{ data.title }}</h2>
		<p>
			<?php
			// translators: "{{ data.title }}" will be replaced with the name of the achievement; leave this bit exactly as is.
			_e( "Hey, you&#8217;ve unlocked the &#8220;{{ data.title }}&#8221; achievement. Congratulations!", 'achievements' );
			?>
		</p>

		<p><a class="dpa-toast-cta" href="<?php echo esc_url( dpa_get_user_avatar_link( 'type=url&user_id=' . get_current_user_id() ) ); ?>"><?php _e( 'See your other achievements', 'achievements' ); ?></a></p>
	</li>
</script>