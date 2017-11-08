/* global jQuery */

// Lite admin upgrade link

( function( $ ) {
	'use strict';

	$( document ).ready( function( $ ) {

		// Open upgrade link in a new window.
		$( 'a[href="admin.php?page=simpay_upgrade"]' ).on( 'click', function() {
			$( this ).attr( 'target', '_blank' );
		} );

	} );
}( jQuery ) );
