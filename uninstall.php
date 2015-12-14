<?php

/**
*	Main Plugin Uninstall Handler
*	@since 0.1
*	
*	Cleanup Tasks:
*	Delete our 'yikes_simple_taxonomy_ordering_options' stored options
*	Delete all 'tax_position' term meta created by this plugin
*/

// if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

// Delete the taxonomy ordering options
delete_option( 'yikes_simple_taxonomy_ordering_options' );

// remove ALL 'tax_position' term meta created by this plugin
$registered_taxonomies = get_taxonomies();
// loop over all registered taxonomies
foreach( $registered_taxonomies as $taxonomy ) {
	// get terms associated with this taxonomy
	$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
	// if terms are set --
	if( $terms ) {
		// loop over site terms
		foreach( $terms as $term ) {
			// delete 'tax_position' term meta
			delete_term_meta( $term->term_id, 'tax_position' );
		}
	}
}

/* End Uninstall.php */