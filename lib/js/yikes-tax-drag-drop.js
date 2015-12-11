jQuery( document ).ready( function() {

	// if the tax table contains items
	if( ! jQuery( '#the-list' ).find( 'tr:first-child' ).hasClass( 'no-items' ) ) {
			
		jQuery( '#the-list' ).sortable({
			placeholder: "yikes-drag-drop-tax-placeholder",
			update: function( event, ui ) {
				
				var updated_array = [];
				
				// store the updated tax ID
				jQuery( '#the-list' ).find( 'tr' ).each( function() {
					var tax_id = jQuery( this ).attr( 'id' ).replace( 'tag-', '' );
					updated_array.push( [ tax_id, jQuery( this ).index() ] );
				});
				
				// build the ajax data
				var data = {
					'action': 'update_taxonomy_order',
					'updated_array': updated_array 
				};
				
				// Run the ajax request
				jQuery.post( localized_data.ajax_url, data, function( response ) {
					
				});
				
			},
		});
	
		jQuery( "#the-list" ).disableSelection();
			
	}

}); 