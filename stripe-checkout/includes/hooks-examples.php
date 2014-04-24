<?php

/*************************
 * FILTER HOOKS
 ************************/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Changes the HTML output of the payment details
 * 
 * Passes in the $sc_payment_details so they can be used to completely reformat the HTML
 * however you may want
 * 
 * @since 1.1.0
 */
function test_sc_payment_details_html( $sc_payment_details_html, $sc_payment_details ) {
	
	return '<p>You paid' . $sc_payment_details['amount'] . '</p>';
}
//add_filter( 'sc_payment_details_html', 'test_sc_payment_details_html', 10, 2 );


function remove_payment_details( $payment_details ) {
	$payment_details['show'] = false;
	
	return $payment_details;
}
//add_filter( 'sc_payment_details', 'remove_payment_details' );


/*
 * Changes the redirect destination
 * 
 * @since 1.1.0
 * 
 * In this example I have setup a "Thank You" page to point to after the purchase
 */
function test_sc_redirect( $redirect ) {
	return 'http://nickyoungweb.com/zip/?page_id=1018';
}
//add_filter( 'sc_redirect', 'test_sc_redirect' );


/*
 * Add functionality before the redirect takes place
 * 
 * @since 1.1.0
 * 
 * Can't echo here as it will cause errors
 * This should mostly be used for backend processing (e.g. Adding New User to WP user list or something similar)
 */
function test_sc_redirect_before() {
	
}
//add_action( 'sc_redirect_before', 'test_sc_redirect_before' );


/*
 * Add functionality after the redirect takes place
 * 
 * @since 1.1.0
 * 
 * Can echo here but it will not be shown to screen, this happens befor page load on redirect
 * Not sure of a great use for this at this time
 */
function test_sc_redirect_after() {
	
}
//add_action( 'sc_redirect_after', 'test_sc_redirect_after' );

