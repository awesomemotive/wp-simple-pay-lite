<?php
/**
 * Payment Methods: Subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @since 4.16.0
 */

namespace SimplePay\Core\PaymentMethods;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\PaymentMethods;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PaymentMethodsSubscriber class.
 *
 * @since 4.16.0
 */
class PaymentMethodsSubscriber implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( ! defined( 'SIMPLE_PAY_DIR' ) ) {
			return array();
		}
		/** @var string $simple_pay_dir */
		$simple_pay_dir = SIMPLE_PAY_DIR;
		require_once $simple_pay_dir . 'src/PaymentMethods/PaymentMethodsFunctions.php';
		return array(
			'simpay_register_collections' => 'register_payment_methods',
		);
	}

	/**
	 * Registers available Payment Methods.
	 *
	 * @since 4.16.0
	 *
	 * @param \SimplePay\Core\Utils\Collections $registry Collections registry.
	 */
	public function register_payment_methods( \SimplePay\Core\Utils\Collections $registry ): void {
		// Add Payment Methods registry to Collections registry.
		$payment_methods = new Collection();
		$registry->add( 'payment-methods', $payment_methods );

		/**
		 * Allows further Payment Methods to be registered.
		 *
		 * @since 3.8.0
		 *
		 * @param \SimplePay\Core\Utils\Collection $payment_methods Payment Methods registry.
		 */
		do_action( 'simpay_register_payment_methods', $payment_methods );
	}
}
