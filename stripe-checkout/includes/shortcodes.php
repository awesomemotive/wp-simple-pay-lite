<?php
/**
 * Plugin shortcode functions
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function to process the [stripe_checkout] shortcode
 * 
 * @since 1.0.0
 */
function sc_stripe_shortcode( $attr ) {
	
	global $sc_options;
	
	extract( shortcode_atts( array(
					'name'         => get_bloginfo( 'title' ),
					'description'  => '',
					'amount'       => ''
				), $attr ) );
	
	
	if( empty( $amount ) || $amount < 50 )
		return '';
	
	
	if( empty( $sc_options['enable_test_key'] ) ) {
		$data_key = ( ! empty( $sc_options['live_publish_key'] ) ? $sc_options['live_publish_key'] : '' );
	} else {
		$data_key = ( ! empty( $sc_options['test_publish_key'] ) ? $sc_options['test_publish_key'] : '' );
	}
	
	if( ! isset( $_GET['payment'] ) ) { 
		$html = '<form action="" method="POST">
					<script
					  src="https://checkout.stripe.com/checkout.js" class="stripe-button"
					  data-key="' . $data_key . '"
					  data-amount="' . $amount . '"
					  data-description="' . $description . '"
					  data-name="' . $name . '"
					 >
					</script>
					<input type="hidden" name="sc-name" value="' . $name . '" />
					<input type="hidden" name="sc-description" value="' . $description . '" />
					<input type="hidden" name="sc-amount" value="' . $amount . '" />
					<input type="hidden" name="sc-redirect" value="' . get_permalink() . '" />
				  </form>';
		
		return $html;
	}
	
	return '';
	
}
add_shortcode( 'stripe', 'sc_stripe_shortcode' );

