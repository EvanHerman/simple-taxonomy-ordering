/*
*	YIKES Simple Taxonomy Ordering Scripts
*	@compiled by YIKES & Evan Herman
*	@since v0.1
*/
jQuery( document ).ready( function() {

	// if the tax table contains items
	if( ! jQuery( '#the-list' ).find( 'tr:first-child' ).hasClass( 'no-items' ) ) {
		
		jQuery( '#the-list' ).sortable({
			placeholder: "yikes-drag-drop-tax-placeholder",
			axis: "y",
			// on start set a height for the placeholder to prevent table jumps
			start: function(event, ui) {
				var height = jQuery( ui.item[0] ).css( 'height' );
				jQuery( '.yikes-drag-drop-tax-placeholder' ).css( 'height', height );
			},
			// update callback
			update: function( event, ui ) {
				// hide checkbox, append a preloader
				jQuery( ui.item[0] ).find( 'input[type="checkbox"]' ).hide().after( '<img src="' + simple_taxonomy_ordering_data.preloader_url + '" class="yikes-simple-taxonomy-preloader" />' );
				
				// empty array				
				var updated_array = [];
				
				// store the updated tax ID
				jQuery( '#the-list' ).find( 'tr.ui-sortable-handle' ).each( function() {
					var tax_id = jQuery( this ).attr( 'id' ).replace( 'tag-', '' );
					updated_array.push( [ tax_id, jQuery( this ).index() ] );
				});
				
				// build the ajax data
				var data = {
					'action': 'update_taxonomy_order',
					'updated_array': updated_array 
				};
				
				// Run the ajax request
				jQuery.post( simple_taxonomy_ordering_data.ajax_url, data, function( response ) {
					jQuery( '.yikes-simple-taxonomy-preloader' ).remove();
					jQuery( ui.item[0] ).find( 'input[type="checkbox"]' ).show();
				});
			}
		});
	}

}); 