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
use SimplePay\Core\RestApi\Internal\Utils\RateLimitValidationUtils;

/**
 * AbstractPaymentCreateRoute class.
 *
 * @since 4.7.0
 */
abstract class AbstractPaymentRoute implements SubscriberInterface {

	use RateLimitValidationUtils;

	/**
	 * The REST API namespace.
	 *
	 * @since 4.7.0
	 *
	 * @var non-falsy-string
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

}
