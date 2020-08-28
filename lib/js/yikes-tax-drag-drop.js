/**
 *	YIKES Simple Taxonomy Ordering Script.
 */
( function( $ ){

	$( document ).ready( function() {
		const base_index = parseInt( simple_taxonomy_ordering_data.paged ) > 0 ? ( parseInt( simple_taxonomy_ordering_data.paged ) - 1 ) * parseInt( $( '#' + simple_taxonomy_ordering_data.per_page_id ).val() ) : 0;
		const tax_table  = $( '#the-list' );

		// If the tax table contains items.
		if ( ! tax_table.find( 'tr:first-child' ).hasClass( 'no-items' ) ) {
			
			tax_table.sortable({
				placeholder: "yikes-drag-drop-tax-placeholder",
				axis: "y",

				// On start, set a height for the placeholder to prevent table jumps.
				start: function( event, ui ) {
					const item  = $( ui.item[0] );
					const index = item.index();
					const colspan = item.children( 'th,td' ).filter( ':visible' ).length;
					$( '.yikes-drag-drop-tax-placeholder' )
					.css( 'height', item.css( 'height' ) )
					.css( 'display', 'flex' )
					.css( 'width', '0' );
				},
				// Update callback.
				update: function( event, ui ) {
					const item = $( ui.item[0] );

					// Hide checkbox, append a preloader.
					item.find( 'input[type="checkbox"]' ).hide().after( '<img src="' + simple_taxonomy_ordering_data.preloader_url + '" class="yikes-simple-taxonomy-preloader" />' );

					const taxonomy_ordering_data = [];

					tax_table.find( 'tr.ui-sortable-handle' ).each( function() {
						const ele       = $( this );
						const term_data = {
							term_id: ele.attr( 'id' ).replace( 'tag-', '' ),
							order: parseInt( ele.index() ) + 1
						}
						taxonomy_ordering_data.push( term_data );
					});
					
					// AJAX Data.
					const data = {
						'action': 'yikes_sto_update_taxonomy_order',
						'taxonomy_ordering_data': taxonomy_ordering_data,
						'base_index': base_index,
						'term_order_nonce': simple_taxonomy_ordering_data.term_order_nonce
					};
					
					// Run the ajax request.
					$.ajax({
						type: 'POST',
						url: window.ajaxurl,
						data: data,
						dataType: 'JSON',
						success: function( response ) {
							console.log( response );
							$( '.yikes-simple-taxonomy-preloader' ).remove();
							item.find( 'input[type="checkbox"]' ).show();
						}
					});
				}
			});
		}
	});
})( jQuery );
