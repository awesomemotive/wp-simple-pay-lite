<?php
/**
 * Shared template functions/functionality.
 *
 * @package SimplePay\Core\Template
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
	echo '<meta name="viewport" content="width=device-width, minimum-scale=1" />';
}
add_action( 'wp_head', __NAMESPACE__ . '\\viewport_tag' );
