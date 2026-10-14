<?php
/**
 * REST API: Stale guest REST nonce compatibility
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.5
 */

namespace SimplePay\Core\RestApi\Internal\Form;

use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * StaleGuestRestNonce class.
 *
 * Payment forms call the plugin's internal REST routes with `wp.apiFetch`,
 * whose nonce middleware sends the `wp_rest` nonce printed inline in the page.
 * On a page served from a cache, that nonce can be older than its lifetime.
 * WordPress then rejects the request with `rest_cookie_invalid_nonce`, and
 * `wp.apiFetch` cannot refresh it for a logged-out visitor, because the
 * `rest-nonce` admin-ajax action exists only for logged-in users. The form
 * would fail to load its data, and payments would fail.
 *
 * For a logged-out visitor the `wp_rest` nonce protects nothing: WordPress
 * treats a request that sends no nonce at all as an anonymous request. So for
 * logged-out visitors only, on the plugin's internal routes only, a stale
 * nonce is treated the same as a missing one. The routes still run their own
 * checks, including the payment-form nonce.
 *
 * Logged-in visitors are not affected. Their stale nonce is still rejected, and
 * `wp.apiFetch` refreshes it and retries.
 *
 * @since 4.17.5
 */
class StaleGuestRestNonce implements SubscriberInterface {

	/**
	 * REST route prefix this compatibility applies to.
	 *
	 * @since 4.17.5
	 * @var string
	 */
	const ROUTE_PREFIX = '/wpsp/__internal__/';

	/**
	 * {@inheritdoc}
	 *
	 * @return array<string, array<int, int|string>>
	 */
	public function get_subscribed_events() {
		return array(
			// After core's rest_cookie_check_errors() (priority 100).
			'rest_authentication_errors' => array( 'allow_stale_guest_nonce', 101 ),
		);
	}

	/**
	 * Treats a stale `wp_rest` nonce from a logged-out visitor as no nonce.
	 *
	 * @since 4.17.5
	 *
	 * @param \WP_Error|null|true $result Authentication result.
	 * @return \WP_Error|null|true
	 */
	public function allow_stale_guest_nonce( $result ) {
		if (
			! is_wp_error( $result ) ||
			'rest_cookie_invalid_nonce' !== $result->get_error_code()
		) {
			return $result;
		}

		if ( is_user_logged_in() ) {
			return $result;
		}

		if ( ! $this->is_internal_route() ) {
			return $result;
		}

		// Match core's handling of a request without a nonce.
		wp_set_current_user( 0 );

		return true;
	}

	/**
	 * Determines if the current REST request targets the plugin's internal routes.
	 *
	 * @since 4.17.5
	 *
	 * @return bool
	 */
	private function is_internal_route() {
		global $wp;

		if (
			! $wp instanceof \WP ||
			! isset( $wp->query_vars['rest_route'] ) ||
			! is_string( $wp->query_vars['rest_route'] )
		) {
			return false;
		}

		$route = '/' . ltrim( $wp->query_vars['rest_route'], '/' );

		return 0 === strpos( $route, self::ROUTE_PREFIX );
	}
}
