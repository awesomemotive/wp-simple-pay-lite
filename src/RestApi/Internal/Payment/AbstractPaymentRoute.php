<?php
/**
 * Payment route
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment;

use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * AbstractPaymentCreateRoute class.
 *
 * @since 4.7.0
 */
abstract class AbstractPaymentRoute implements SubscriberInterface {

	/**
	 * The REST API namespace.
	 *
	 * @since 4.7.0
	 *
	 * @var string
	 */
	protected $namespace = 'wpsp/__internal__';

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers the REST API routes for the endpoint.
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	abstract public function register_route();

	/**
	 * Determines if the REST API request is valid based on the current rate limit.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The payment request.
	 * @return bool
	 */
	protected function validate_rate_limit( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$has_exceeded_rate_limit = false;

		/**
		 * Filters if the current IP address has exceeded the rate limit.
		 *
		 * @since 3.9.5
		 * @since 4.7.0 Added $request parameter.
		 *
		 * @param bool $has_exceeded_rate_limit
		 * @param \WP_REST_Request $request The payment request.
		 */
		$has_exceeded_rate_limit = apply_filters(
			'simpay_has_exceeded_rate_limit',
			$has_exceeded_rate_limit,
			$request
		);

		return ! $has_exceeded_rate_limit;
	}

}
