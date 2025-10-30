<?php
/**
 * Payment Methods: Collection
 *
 * @package SimplePay\Core\PaymentMethods
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\PaymentMethods;

use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collection class
 *
 * @since 3.8.0
 */
class Collection extends Utils\Collection {

	/**
	 * Adds a Payment Method to the registry.
	 *
	 * @since 3.8.0
	 *
	 * @param string                                       $payment_method_id Payment Method ID.
	 * @param \SimplePay\Core\PaymentMethods\PaymentMethod $payment_method Payment Method.
	 * @return \WP_Error|true True on successful addition, otherwise a \WP_Error object.
	 */
	public function add( $payment_method_id, $payment_method ) {
		if ( empty( $payment_method_id ) ) {
			return new \WP_Error(
				'invalid_payment_method_id',
				sprintf(
					/* translators: %s Collection ID that could not be registered. */
					__( 'The %s Payment Method already exists and cannot be added.', 'stripe' ),
					$payment_method_id
				)
			);
		}

		if ( ! is_a( $payment_method, 'SimplePay\Core\PaymentMethods\PaymentMethod' ) ) {
			return new \WP_Error(
				'invalid_payment_method',
				sprintf(
					/* translators: %s Collection ID that could not be registered. */
					__( 'The Payment Method is invalid.', 'stripe' ),
					$payment_method
				)
			);
		}

		return $this->add_item( $payment_method_id, $payment_method );
	}
}
