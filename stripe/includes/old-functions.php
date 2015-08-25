<?php
/**
 * Old SC functions to redirect
 */

function sc_stripe_to_formatted_amount( $amount, $currency ) {
	Stripe_Checkout_Misc::to_formatted_amount( $amount, $currency );
}
