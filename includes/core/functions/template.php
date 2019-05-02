<?php
/**
 * Shared template functions/functionality.
 *
 * @since 3.5.0
 */

namespace SimplePay\Core\Template;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add viewport tag for Stripe Elements.
 *
 * @link https://stripe.com/docs/stripe-js/elements/quickstart#viewport-meta-requirements
 *
 * @since 3.5.0
 */
function viewport_tag() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';
}
add_action( 'wp_head', __NAMESPACE__ . '\viewport_tag' );
