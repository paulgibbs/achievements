<?php
/**
 * Implements the main logic (achievement event monitoring, etc)
 *
 * @package Achievements
 * @subpackage Logic
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hooks into the relevant WordPress actions which Achievements need to make its magic work.
 *
 * @since 3.0
 */
function dpa_register_events() {
	$events = get_terms( 'dpa_actions', array( 'hide_empty' => true )  );
//	echo '<pre>';
//	die(Var_dump( $events ));
return;
	$events = apply_filters( 'dpa_register_events', dpa_get_active_actions() );
	foreach ( (array) $events as $event )
		add_action( $event->name, 'dpa_handle_event_' . $event->name, 12, 10 ); // Priority 12 in case object modified by other plugins
}
?>