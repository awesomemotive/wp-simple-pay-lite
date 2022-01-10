<?php
/**
 * Simple Pay: Edit form Subscription options
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds "Subscription Options" Payment Form settings tab content.
 *
 * Lite uses this as a promotional area.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_subscription_options( $post_id ) {
	_doing_it_wrong(
		__FUNCTION__,
		esc_html__( 'No longer used.', 'stripe' ),
		'4.1.0'
	);
}
