// Lite admin upgrade link

(function( $ ) {
	'use strict';

	$( document ).ready( function( $ ) {

		// Open upgrade link in a new window.
		$( 'a[href="admin.php?page=stripe-checkout-upgrade"]' ).on( 'click', function() {
			$( this ).attr( 'target', '_blank' );
		} );

	} );
}( jQuery ) );
