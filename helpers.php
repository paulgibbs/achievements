<?php
/**
 * Helper and common functions. Split out of achievements.php to avoid clutter.
 *
 * @author Paul Gibbs <paul@byotos.com>
 * @package Achievements
 * @subpackage helpers
 */

/**
 * Compare the Achievements version to the DB version to determine if we're updating
 *
 * @return bool True if update
 * @since 3.0
 */
function dpa_is_update() {
	// Current DB version of this site
	$current_db   = get_site_option( 'achievements-db-version' );
	$current_live = dpa_get_db_version();

	// Compare versions
	$is_update = (bool) ( (int) $current_db < (int) $current_live );
	return $is_update;
}

/**
 * Output the Achievements database version
 *
 * @since 3.0
 * @uses dpa_get_db_version()
 */
function dpa_db_version() {
	echo dpa_get_db_version();
}
	/**
	 * Return the Achievements database version
	 *
	 * @since 3.0
	 * @global DPA_Achievements $achievements
	 * @return string The Achievements database version
	 */
	function dpa_get_db_version() {
		return ACHIEVEMENTS_DB_VERSION;
		// global $achievements;
		// @todo return $achievements->db_version;
	}
?>