<?php
/**
 * Achievements Cache Helpers
 *
 * Helper functions used to communicate with WordPress' various caches. Many
 * of these functions are used to work around specific WordPress nuances. They
 * are subject to changes, tweaking, and will need iteration as performance
 * improvements are made to WordPress core.
 *
 * @package Achievements
 * @subpackage CoreCapabilities
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * If multisite and running network-wide, clear custom caches when something
 * is added, removed, or updated in the Events taxonomy.
 *
 * @param int|array $ids Single or list of term IDs
 * @param string $taxonomy Taxonomy
 * @see dpa_register_events()
 * @since Achievements (3.0)
 */
function dpa_clear_events_tax_cache( $ids, $taxonomy ) {
	if ( dpa_get_event_tax_id() !== $taxonomy )
		return;

	// If multisite and running network-wide, clear the registered events cache for the events taxonomy.
	if ( is_multisite() && dpa_is_running_networkwide() )
		wp_cache_delete( 'dpa_registered_events', 'achievements_events' );
}
add_action( 'clean_term_cache', 'dpa_clear_events_tax_cache', 10, 2 );
