// Admin JS - Shared between SP Lite & Pro

(function( $ ) {
	'use strict';

	$( document ).ready( function( $ ) {

		var body = $( document.body );

		// Get the hash fragment (#) (tab id) of the page so we can find out what tab to show.
		function get_hash_fragment() {
			if ( window.location.hash ) {
				return window.location.hash.substring( 1 );
			} else {
				return 'stripe-keys';
			}
		}

		// Show the tab content.
		$( '#' + get_hash_fragment() + '-settings-tab' ).addClass( 'tab-content' ).show();

		// Make the actual tab selected.
		$( '.nav-tab-wrapper' ).children( '.nav-tab' ).each( function() {
			if ( $( this ).data( 'tab-id' ) == get_hash_fragment() ) {
				$( this ).addClass( 'nav-tab-active' );
			}
		} );

		// Tab click event.
		$( '.sc-nav-tab' ).click( function() {

			// Remove active class from all tabs, then re-add only to the clicked one.
			$( this ).parent().children( '.nav-tab' ).each( function() {
				$( this ).removeClass( 'nav-tab-active' );
			} );

			$( this ).addClass( 'nav-tab-active' );

			var tab_id = $( this ).data( 'tab-id' );

			// Hide content element form all tabs, then re-add only to clicked one.
			$( '.tab-content' ).hide().removeClass( 'tab-content' );
			$( '#' + tab_id + '-settings-tab' ).addClass( 'tab-content' ).show();

			// Trigger custom event passing in tab_id.
			body.trigger( 'spAdminTabOnChange', [ tab_id ] );
		} );

		// Set fragment of url (section after hash (#) symbol) so we land back on the right tab.
		// Should work for primary settings save button, but also other submit buttons like license deactivation.
		$( '#sc-settings-content form' ).on( 'submit', function() {
			$( this ).closest( 'form' ).attr( 'action', 'options.php#' + get_hash_fragment() );
		} );

	} );

}( jQuery ));
