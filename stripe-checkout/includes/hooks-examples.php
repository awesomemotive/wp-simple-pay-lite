<?php

/*************************
 * FILTER HOOKS
 ************************/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 
 * 
 * @since 
 */
function test_sc_payment_details_html( $sc_payment_details_html, $sc_payment_details ) {
	
	return '<p>You paid' . $sc_payment_details['amount'] . '</p>';
}
//add_filter( 'sc_payment_details_html', 'test_sc_payment_details_html', 10, 2 );


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