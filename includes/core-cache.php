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
 * Skip invalidation of child post content when editing a parent.
 *
 * This prevents invalidating caches for unlocks when editing an achievement.
 * Without this in place, WordPress will attempt to invalidate all child posts
 * whenever a parent post is modified. This can cause thousands of cache
 * invalidations to occur on a single edit, which is no good for anyone.
 *
 * @since 3.0
 */
class DPA_Cache_Skip_Children {
	/**
	 * Post ID being updated
	 *
	 * @since 3.0
	 * @var int
	 */
	private $updating_post = 0;

	/**
	 * The original value of $_wp_suspend_cache_invalidation global
	 *
	 * @since 3.0
	 * @var bool
	 */
	private $original_cache_invalidation = false;

	/**
	 * Hook into the 'pre_post_update' action.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		add_action( 'pre_post_update', array( $this, 'pre_post_update' ) );
	}

	/**
	 * Only clean post caches for main Achievement posts.
	 *
	 * Check that the post being updated is an achievement post type, saves the
	 * post ID to be used later, and adds an action to 'clean_post_cache' that
	 * prevents child post caches from being cleared.
	 *
	 * @param int $post_id The post ID being updated
	 * @since 3.0
	 */
	public function pre_post_update( $post_id = 0 ) {
		// Bail if post ID is not an achievement post type
		if ( empty( $post_id ) || ! dpa_is_custom_post_type( $post_id ) )
			return;

		$this->updating_post = $post_id;

		/**
		 * Skip related post cache invalidation. This prevents invalidating the
		 * caches of the child posts when there is no reason to do so.
		 */
		add_action( 'clean_post_cache', array( $this, 'skip_related_posts' ) );
	}

	/**
	 * Skip cache invalidation of related posts if the post ID being invalidated
	 * is not the one that was just updated.
	 *
	 * @param int $post_id The post ID of the cache being invalidated
	 * @since 3.0
	 */
	public function skip_related_posts( $post_id = 0 ) {
		// Bail if this post is not the current achievement post
		if ( empty( $post_id ) || ( $this->updating_post != $post_id ) )
			return;

		/**
		 * Disable cache invalidation and stash the current cache invalidation value in
		 * a variable so we can restore back to it nicely in the future.
		 */
		$this->original_cache_invalidation = $GLOBALS['_wp_suspend_cache_invalidation'];
		wp_suspend_cache_invalidation( true );

		// Arrange to restore cache invalidation next time a post is inserted
		add_action( 'wp_insert_post', array( $this, 'restore_cache_invalidation' ) );
	}

	/**
	 * Restore the cache invalidation to its previous value.
	 *
	 * @since 3.0
	 */
	public function restore_cache_invalidation() {
		wp_suspend_cache_invalidation( $this->original_cache_invalidation );
	}
}
new DPA_Cache_Skip_Children();

/**
 * If multisite and running network-wide, clear custom caches when something
 * is added, removed, or updated in the Events taxonomy.
 *
 * @param int|array $ids Single or list of term IDs
 * @param string $taxonomy Taxonomy
 * @see dpa_register_events()
 * @since 3.0
 */
function dpa_clear_events_tax_cache( $ids, $taxonomy ) {
	// If multisite and running network-wide, clear the registered events cache.
	if ( is_multisite() && dpa_is_running_networkwide() )
		wp_cache_delete( 'dpa_registered_events', 'achievements' );
}
add_action( 'clean_term_cache', 'dpa_clear_events_tax_cache', 10, 2 );
