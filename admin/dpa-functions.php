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
 * @since 1.0
 */
function dpa_supported_plugins_header() {
	if ( ! $GLOBALS['is_gecko'] ) : ?>
	<?php endif; ?>

	<form name="dpa_toolbar" method="post" enctype="multipart/form-data">

		<input type="search" results="5" name="dpa_toolbar_search" value="VolvoVolvo" />
		<select class="<?php if ( ! $GLOBALS['is_gecko'] ) echo 'dpa-ff-hack'; ?>" name="dpa_toolbar_search">
			<option value="x">VolvoVolvo</option>
			<option value="x">VolvoVolvo</option>
			<option value="x">VolvoVolvo</option>
		</select>

	</form>
	<?php
}
?>