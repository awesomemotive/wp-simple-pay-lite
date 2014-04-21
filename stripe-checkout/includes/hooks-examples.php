<?php

/*************************
 * FILTER HOOKS
 ************************/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modifies the HTML output of the shortcode button
 * 
 * @since 3.1.2
 */
function test_sc_payment_details_html( $sc_payment_details_html, $sc_payment_details ) {
	
	return '<p>You paid' . $sc_payment_details['amount'] . '</p>';
}
add_filter( 'sc_payment_details_html', 'test_sc_payment_details_html', 10, 2 );
