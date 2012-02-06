<?php
/**
 * Achievements' Admin Functions
 *
 * @package Achievements
 * @subpackage Administration
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filter sample permalinks so that certain languages display properly.
 *
 * @param string $post_link Custom post type permalink
 * @param object $post Post data object
 * @param bool $leavename Optional, defaults to false. Whether to keep post name or page name.
 * @param bool $sample Optional, defaults to false. Is it a sample permalink.
 * @return string The custom post type permalink
 * @since 3.0
 */
function dpa_filter_sample_permalink( $post_link, $post, $leavename, $sample ) {
	// Bail if not on an admin page and not getting a sample permalink
	if ( ! empty( $sample ) && is_admin() && dpa_is_custom_post_type() )
		return urldecode( $post_link );

	// Return post link
	return $post_link;
}


/**
 * Common toolbar header for supported plugins header screen
 *
 * @global achievements $achievements Main Achievements object
 * @since 1.0
 */
function dpa_supported_plugins_header() {
	global $achievements;

	?>
	<form name="dpa-toolbar" method="post" enctype="multipart/form-data">

		<div id="dpa-toolbar-wrapper">
			<input type="search" results="5" name="dpa-toolbar-search" id="dpa-toolbar-search" />
			<select class="<?php if ( ! $GLOBALS['is_gecko'] ) echo 'dpa-ff-hack'; ?>" name="dpa-toolbar-filter" id="dpa-toolbar-filter">
				<option value="all"><?php esc_html_e( 'All Plugins', 'dpa' ); ?></option>
				<option value="available"><?php esc_html_e( 'Available Plugins', 'dpa' ); ?></option>
				<option value="installed"><?php esc_html_e( 'Installed Plugins', 'dpa' ); ?></option>
			</select>

			<ul id="dpa-toolbar-views">
				<li><a class="grid" href="#"></a></li>
				<li><a class="list" href="#"></a></li>
				<li><a class="detail" href="#"></a></li>
				<li><p class="label"><?php _e( 'View', 'dpa' ); ?></p></li>
			</ul>
		</div>

	</form>
	<?php
}
?>