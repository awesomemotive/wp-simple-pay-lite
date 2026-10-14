<?php
/**
 * REST API: Form data rate limiter
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.5
 */

namespace SimplePay\Core\RestApi\Internal\Form;

use function SimplePay\Core\Utils\get_current_ip_address;

/**
 * FormDataRateLimiter class.
 *
 * Counts form-data requests per visitor in a fixed window, stored in the
 * persistent object cache.
 *
 * Without a persistent object cache, transients are rows in `wp_options`, and
 * the route is public: every request with a new visitor id would add rows,
 * and a client can pick its own id through forwarded-IP headers. So by
 * default nothing is counted unless an external object cache is in use
 * (see the `simpay_form_data_rate_limit_storage_available` filter). The route
 * only returns form data that is already public, so failing open costs little.
 *
 * Every page view of a payment form fetches its data from the form-data
 * route, so this counter runs on a hot path. The payment routes' limiter
 * (`\SimplePay\Core\Utils\Rate_Limiting`) keeps every visitor in one shared
 * log file that it reads three times and rewrites without a lock on each
 * check; that is fine for rare payment attempts but not for every page view.
 * A counter per visitor avoids the shared file entirely. In a persistent
 * object cache it is incremented atomically.
 *
 * @since 4.17.5
 */
class FormDataRateLimiter {

	/**
	 * Transient name prefix.
	 *
	 * @since 4.17.5
	 * @var string
	 */
	const TRANSIENT_PREFIX = 'simpay_form_data_rl_';

	/**
	 * Object cache group for counters kept in a persistent object cache.
	 *
	 * @since 4.17.5
	 * @var string
	 */
	const CACHE_GROUP = 'simpay_form_data_rate_limit';

	/**
	 * Default maximum requests per window.
	 *
	 * @since 4.17.5
	 * @var int
	 */
	const DEFAULT_MAX_COUNT = 100;

	/**
	 * Default window length, in seconds.
	 *
	 * @since 4.17.5
	 * @var int
	 */
	const DEFAULT_WINDOW = HOUR_IN_SECONDS;

	/**
	 * Determines if the current request is within the form-data rate limit,
	 * and counts it.
	 *
	 * @since 4.17.5
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request The request object.
	 * @return bool True if the request is allowed, false if the limit is exceeded.
	 */
	public function is_allowed( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		/** This filter is documented in includes/core/utils/class-rate-limiting.php */
		$enabled = apply_filters( 'simpay_rate_limiting_enabled', true );

		if ( true !== $enabled ) {
			return true;
		}

		if ( ! $this->is_storage_available() ) {
			return true;
		}

		$exceeded = ! $this->count_request( $request );

		/**
		 * Filters if the current visitor has exceeded the form-data rate limit.
		 *
		 * @since 4.17.5
		 *
		 * @param bool                                   $exceeded Whether the limit has been exceeded.
		 * @param \WP_REST_Request<array<string, mixed>> $request  The form-data request.
		 */
		$exceeded = apply_filters(
			'simpay_form_data_has_exceeded_rate_limit',
			$exceeded,
			$request
		);

		return true !== $exceeded;
	}

	/**
	 * Counts the request in the visitor's current window.
	 *
	 * @since 4.17.5
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request The request object.
	 * @return bool True if the request is within the limit.
	 */
	private function count_request( $request ) {
		$key = $this->get_transient_key( $request );

		if ( wp_using_ext_object_cache() ) {
			return $this->count_request_in_object_cache( $key );
		}

		return $this->count_request_in_transient( $key );
	}

	/**
	 * Counts the request with an atomic increment in the persistent object
	 * cache, so concurrent requests from one visitor are each counted.
	 *
	 * `wp_cache_add()` only creates the counter, with the window as its
	 * expiration, when it does not exist yet; incrementing does not extend
	 * that expiration, so the window stays fixed.
	 *
	 * @since 4.17.5
	 *
	 * @param string $key Counter key.
	 * @return bool True if the request is within the limit.
	 */
	private function count_request_in_object_cache( $key ) {
		wp_cache_add( $key, 0, self::CACHE_GROUP, $this->get_window() );

		$count = wp_cache_incr( $key, 1, self::CACHE_GROUP );

		// The counter could not be incremented (e.g. it was evicted between
		// the add and the increment, or holds a non-numeric value). Start a
		// new window rather than blocking the visitor.
		if ( ! is_int( $count ) ) {
			wp_cache_set( $key, 1, self::CACHE_GROUP, $this->get_window() );

			return true;
		}

		return $count <= $this->get_max_count();
	}

	/**
	 * Counts the request in a transient.
	 *
	 * Only used when counting is enabled without a persistent object cache
	 * (see the `simpay_form_data_rate_limit_storage_available` filter). The
	 * read-modify-write is not atomic, so concurrent requests can be counted
	 * once; that is acceptable for a coarse abuse ceiling.
	 *
	 * @since 4.17.5
	 *
	 * @param string $key Transient key.
	 * @return bool True if the request is within the limit.
	 */
	private function count_request_in_transient( $key ) {
		$now   = time();
		$entry = get_transient( $key );

		$count   = 0;
		$expires = $now + $this->get_window();

		// Continue the stored window unless it is missing, malformed, or expired.
		if (
			is_array( $entry ) &&
			isset( $entry['count'], $entry['expires'] ) &&
			is_int( $entry['count'] ) &&
			is_int( $entry['expires'] ) &&
			$entry['expires'] > $now
		) {
			$count   = $entry['count'];
			$expires = $entry['expires'];
		}

		// Stop counting once blocked; the window still expires on schedule.
		if ( $count >= $this->get_max_count() ) {
			return false;
		}

		set_transient(
			$key,
			array(
				'count'   => $count + 1,
				'expires' => $expires,
			),
			max( 1, $expires - $now )
		);

		return true;
	}

	/**
	 * Returns the transient key for the current visitor.
	 *
	 * @since 4.17.5
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request The request object.
	 * @return string
	 */
	private function get_transient_key( $request ) {
		/** This filter is documented in includes/core/utils/class-rate-limiting.php */
		$id = apply_filters( 'simpay_rate_limiting_id', $this->get_visitor_ip(), $request );

		$id = is_scalar( $id ) ? (string) $id : '';

		return self::TRANSIENT_PREFIX . md5( $id );
	}

	/**
	 * Determines if request counts can be stored.
	 *
	 * @since 4.17.5
	 *
	 * @return bool
	 */
	private function is_storage_available() {
		/**
		 * Filters if the form-data rate limit can store its counters.
		 *
		 * Defaults to true only when a persistent object cache is in use.
		 * Without one, each counter is stored as rows in the `wp_options`
		 * table, so return true only if that database write on every
		 * form-data request is acceptable.
		 *
		 * @since 4.17.5
		 *
		 * @param bool $available Whether counters can be stored.
		 */
		$available = apply_filters(
			'simpay_form_data_rate_limit_storage_available',
			wp_using_ext_object_cache()
		);

		return true === $available;
	}

	/**
	 * Returns the IP address used to identify the current visitor.
	 *
	 * Uses the plugin's proxy-aware `get_current_ip_address()` by default.
	 * Sites that are not behind a reverse proxy or CDN can stop trusting the
	 * forwarded-IP headers, which a client can set, so the counter is keyed
	 * on the connection's `REMOTE_ADDR` instead.
	 *
	 * @since 4.17.5
	 *
	 * @return string
	 */
	private function get_visitor_ip() {
		/**
		 * Filters if the form-data rate limit trusts forwarded-IP headers
		 * (`Client-IP`, `X-Forwarded-For`) to identify the visitor.
		 *
		 * Keep the default (true) behind a reverse proxy or CDN, where every
		 * visitor shares the proxy's `REMOTE_ADDR`. Return false when the site
		 * is reached directly, so clients cannot choose their own id.
		 *
		 * @since 4.17.5
		 *
		 * @param bool $trust Whether to trust forwarded-IP headers. Default true.
		 */
		$trust = apply_filters( 'simpay_form_data_rate_limit_trust_proxy_headers', true );

		if ( false !== $trust ) {
			return get_current_ip_address();
		}

		$ip = isset( $_SERVER['REMOTE_ADDR'] )
			? filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated as an IP address.
			: false;

		$ip = is_string( $ip ) ? $ip : '127.0.0.1';

		/** This filter is documented in includes/core/utils/functions.php */
		$ip = apply_filters( 'simpay_get_current_ip_address', $ip );

		return is_scalar( $ip ) ? (string) $ip : '';
	}

	/**
	 * Returns the maximum number of form-data requests per window.
	 *
	 * @since 4.17.5
	 *
	 * @return int
	 */
	private function get_max_count() {
		/**
		 * Filters how many form-data requests one visitor can make in a
		 * rate-limiting window before receiving a 429.
		 *
		 * @since 4.17.5
		 *
		 * @param int $max_count Maximum form-data requests. Default 100.
		 */
		$max = apply_filters( 'simpay_form_data_rate_limit_max_count', self::DEFAULT_MAX_COUNT );
		$max = is_numeric( $max ) ? (int) $max : 0;

		// A zero or non-numeric value would block every request.
		return $max > 0 ? $max : self::DEFAULT_MAX_COUNT;
	}

	/**
	 * Returns the rate-limiting window length, in seconds.
	 *
	 * @since 4.17.5
	 *
	 * @return int
	 */
	private function get_window() {
		/**
		 * Filters the length of the form-data rate-limiting window.
		 *
		 * @since 4.17.5
		 *
		 * @param int $window Window length in seconds. Default one hour.
		 */
		$window = apply_filters( 'simpay_form_data_rate_limit_window', self::DEFAULT_WINDOW );
		$window = is_numeric( $window ) ? (int) $window : 0;

		return $window > 0 ? $window : self::DEFAULT_WINDOW;
	}
}
