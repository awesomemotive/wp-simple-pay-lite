<?php
/**
 * Utils: Rate limit validation
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\RestApi\Internal\Utils;

/**
 * RateLimitValidationUtils trait.
 *
 * Shared IP rate limit check for public REST routes, guarding against abuse
 * such as email enumeration or outbound mail relay.
 *
 * @since 4.17.4
 */
trait RateLimitValidationUtils {

	/**
	 * Determines if the REST API request is valid based on the current rate limit.
	 *
	 * @since 4.17.4
	 *
	 * @param \WP_REST_Request $request The REST request.
	 * @return bool True if the request is allowed, false if the limit is exceeded.
	 */
	protected function validate_rate_limit( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

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
			false,
			$request
		);

		return ! $has_exceeded_rate_limit;
	}
}
