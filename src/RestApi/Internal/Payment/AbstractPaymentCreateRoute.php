<?php
/**
 * Payment creation route
 *
 * Utilized by both Lite and Pro.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment;

/**
 * AbstractPaymentCreateRoute class.
 *
 * @since 4.7.0
 */
abstract class AbstractPaymentCreateRoute extends AbstractPaymentRoute {

	/**
	 * The payment create route.
	 *
	 * @since 4.7.0
	 *
	 * @var string
	 */
	protected $route = 'payment/create';

	/**
	 * Application fee handling.
	 *
	 * @since 4.7.0
	 *
	 * @var \SimplePay\Core\StripeConnect\ApplicationFee
	 */
	protected $application_fee;

	/**
	 * AbstractPaymentCreateRoute.
	 *
	 * @since 4.7.0
	 *
	 * @param \SimplePay\Core\StripeConnect\ApplicationFee $application_fee Application fee service.
	 */
	public function __construct( $application_fee ) {
		$this->application_fee = $application_fee;
	}

	/**
	 * Determines if the current request should be able to create a payment.
	 *
	 * This occurs _before_ argument validation is done. This should be where
	 * user authentication permission checks are done.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool
	 */
	public function create_payment_permissions_check( $request ) {
		return true;
	}

	/**
	 * Creates a payment for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return \WP_REST_Response
	 */
	abstract public function create_payment( $request );

}
