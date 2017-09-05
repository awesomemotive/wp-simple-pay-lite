// Admin JS - Shared between SP Lite & Pro

(function( $ ) {
	'use strict';

	$( document ).ready( function( $ ) {

		var body = $( document.body );

		// Get the hash fragment (#) (tab id) of the page so we can find out what tab to show.
		function getHashFragment() {
			if ( window.location.hash ) {
				return window.location.hash.substring( 1 );
			} else {
				return 'stripe-keys';
			}
		}

		// Load tab.
		function loadTab( tabId ) {
			// Hide content element form all tabs, then re-add only to clicked one.
			$( '#sc-settings-content .tab-content' ).hide();
			$( '#' + tabId + '-settings-tab' ).show();

			// Make the actual tab selected.
			$( '#sc-settings-content .nav-tab-wrapper' ).children( '.nav-tab' ).each( function() {
				if ( $( this ).data( 'tab-id' ) == tabId ) {
					$( this ).addClass( 'nav-tab-active' );
				} else {
					$( this ).removeClass( 'nav-tab-active' );
				}
			} );

			// Trigger custom event passing in tab_id.
			body.trigger( 'spAdminTabOnChange', [ tabId ] );
		}

		// Tab click event.
		$( '.sc-nav-tab' ).click( function( e ) {
			loadTab( $( this ).data( 'tab-id' ) );
		} );

		// Set fragment of url (section after hash (#) symbol) so we land back on the right tab.
		// Should work for primary settings save button, but also other submit buttons like license deactivation.
		$( '#sc-settings-content form' ).on( 'submit', function() {
			$( this ).closest( 'form' ).attr( 'action', 'options.php#' + getHashFragment() );
		} );

		loadTab( getHashFragment() );
	} );

}( jQuery ));
