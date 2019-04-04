<?php
/**
 * Main Plugin Uninstall Handler.
 *
 * Cleanup Tasks:
 *  - Delete our 'yikes_simple_taxonomy_ordering_options' stored options
 *  - Delete all 'tax_position' term meta created by this plugin
 *
 * @package YIKES_Simple_Taxonomy_Ordering
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete the taxonomy ordering options.
delete_option( 'yikes_simple_taxonomy_ordering_options' );

// Delete `tax_position` term meta from the DB.
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->termmeta WHERE meta_key = 'tax_position'" );
