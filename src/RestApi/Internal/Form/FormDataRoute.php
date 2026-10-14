<?php
/**
 * FormDataRoute class file
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.5
 */

namespace SimplePay\Core\RestApi\Internal\Form;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Utils\PaymentFormNonce;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * FormDataRoute class.
 *
 * @package SimplePay
 * @subpackage Core
 * @since 4.17.5
 */
class FormDataRoute implements SubscriberInterface {

	private const NAMESPACE = 'wpsp/__internal__';
	private const ROUTE     = 'form-data/(?P<form_id>\d+)';

	/**
	 * Registers the REST API route.
	 *
	 * @since 4.17.5
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_form_data' ),
				'permission_callback' => array( $this, 'get_form_data_permissions_check' ),
				'args'                => array(
					'form_id' => array(
						'description'       => __( 'Payment form ID.', 'stripe' ),
						'type'              => 'integer',
						'required'          => true,
						// No validate_callback on purpose: existence is checked
						// in get_form_data() so an unknown form returns the
						// route's own 404 `rest_form_not_found` error instead
						// of a generic 400 `rest_invalid_param`.
						'sanitize_callback' => function ( $param ) {
							return absint( $param );
						},
					),
				),
			)
		);
	}

	/**
	 * Determines if the current visitor can read payment form data.
	 *
	 * Always true: the endpoint is public, because anonymous visitors on
	 * (possibly page-cached) pages load their payment forms through it. See
	 * get_form_data() for how the public surface is bounded.
	 *
	 * @since 4.17.5
	 * @return true
	 */
	public function get_form_data_permissions_check() {
		return true;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 4.17.5
	 * @return array<string, string>
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Gets and returns form data for the given form ID.
	 *
	 * SECURITY: This route is intentionally unauthenticated to support cached
	 * page rendering — cached visitors must hydrate the form client-side. The
	 * response therefore includes a freshly-issued `simpay_payment_form_{form_id}`
	 * nonce; anyone who can reach the endpoint can obtain a nonce valid for
	 * the payment-create / validate-coupon routes for that specific form. The
	 * nonce is scoped per form (so leaking one nonce only affects the matching
	 * form) and is emitted with a shortened lifetime (a 1-hour `nonce_life`,
	 * so valid for 30-60 minutes, vs. WP's default 12-24h); the frontend
	 * fetches a fresh one when a request is rejected for an expired nonce.
	 * The payload is the same form configuration that used to be printed in
	 * the page HTML, and it is only returned for published forms (see
	 * {@see FormDataRoute::is_form_readable()}). When a persistent object cache is available, a
	 * per-visitor rate limit (see {@see
	 * FormDataRoute::validate_form_data_rate_limit()}), counted separately
	 * from payment attempts, slows down scraping. The payment routes keep
	 * their own rate limit, validation and Stripe-side checks.
	 *
	 * @since 4.17.5
	 * @param \WP_REST_Request<array<string, mixed>> $request The request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_form_data( $request ) {
		// The response carries a short-lived nonce, so it must never be
		// cached — including the 404/429 error responses below.
		$this->prevent_response_caching();

		// The route is intentionally public so cached-page visitors can
		// hydrate forms. Guard against abuse with a per-visitor rate limit.
		if ( false === $this->validate_form_data_rate_limit( $request ) ) {
			return new WP_Error(
				'rest_rate_limit_exceeded',
				__( 'Sorry, you have made too many requests. Please try again later.', 'stripe' ),
				array( 'status' => 429 )
			);
		}

		/** @var int $form_id */
		$form_id = $request->get_param( 'form_id' );

		/** @var \SimplePay\Core\Abstracts\Form|false $form */
		$form = simpay_get_form( $form_id );

		// Unpublished forms (draft, pending, private, scheduled, trashed) are
		// answered exactly like unknown IDs, so enumerating IDs cannot reveal
		// that they exist or what they contain.
		if ( false === $form || ! $this->is_form_readable( $form ) ) {
			return new WP_Error(
				'rest_form_not_found',
				__( 'Payment form not found.', 'stripe' ),
				array( 'status' => 404 )
			);
		}

		// Get the form script variables (same as what was localized).
		$form_variables = $form->get_upe_script_variables();

		// Apply the same filters that are used when localizing.
		$temp = array( $form_id => $form_variables );

		/** This filter is documented in includes/core/forms/class-default-form.php */
		$temp = apply_filters( 'simpay_form_' . absint( $form_id ) . '_script_variables', $temp, $form_id );

		/** This filter is documented in includes/core/forms/class-default-form.php */
		$temp = apply_filters( 'simpay_form_script_variables', $temp, $form_id );

		// Extract the form data for this form ID.
		$form_data = isset( $temp[ $form_id ] ) ? $temp[ $form_id ] : $form_variables;

		// Add form ID to the response.
		$form_data['id'] = $form_id;

		// Add livemode to settings if not already present.
		if ( ! isset( $form_data['settings']['livemode'] ) ) {
			$form_data['settings']['livemode'] = $form->is_livemode();
		}

		// Include a fresh per-form nonce in the response.
		$form_data['nonce'] = self::create_form_nonce( $form_id );

		return new WP_REST_Response( $form_data, 200 );
	}

	/**
	 * Determines if the current visitor may read the given payment form's data.
	 *
	 * Published forms are public: their configuration is what a visitor sees
	 * on the page that embeds them. Any other status is limited to users who
	 * can manage the plugin, matching the rule `simpay_get_form()` applies.
	 * It is checked here as well so this unauthenticated route never depends
	 * on that function's internals, or on a `simpay_form_view` override, to
	 * keep unpublished forms private.
	 *
	 * @since 4.17.5
	 *
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @return bool True if the form data can be returned.
	 */
	private function is_form_readable( $form ) {
		$post = get_post( $form->id );

		if ( ! $post instanceof \WP_Post || 'simple-pay' !== $post->post_type ) {
			return false;
		}

		if ( 'publish' === $post->post_status ) {
			return true;
		}

		return 'trash' !== $post->post_status && current_user_can( 'manage_options' );
	}

	/**
	 * Marks the current form-data response as uncacheable.
	 *
	 * WordPress only sends no-cache headers on REST responses for logged-in
	 * users, so an anonymous form-data response would otherwise be cacheable
	 * by REST-caching layers (e.g. LiteSpeed Cache's "Cache REST API" option,
	 * Varnish, or a CDN). A cached response would hand every visitor the same
	 * nonce, which stops verifying 30-60 minutes after it is minted — the exact
	 * stale-nonce failure this route exists to avoid.
	 *
	 * - `rest_send_nocache_headers` makes core send `wp_get_nocache_headers()`
	 *   (`Cache-Control: no-cache, must-revalidate, max-age=0, no-store,
	 *   private`), respected by proxies and CDNs. It is read after dispatch,
	 *   so adding it from the route callback takes effect.
	 * - `DONOTCACHEPAGE` is the constant page-cache plugins check (WP Super
	 *   Cache, W3 Total Cache, WP Rocket, LiteSpeed Cache, and others).
	 *
	 * @since 4.17.5
	 *
	 * @return void
	 */
	protected function prevent_response_caching() {
		add_filter( 'rest_send_nocache_headers', '__return_true' );

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
	}

	/**
	 * Checks the rate limit for form-data requests.
	 *
	 * Every page view of a payment form fetches its data from this route, so
	 * it must not share the payment-attempt budget (5 per IP) used by the
	 * payment routes: a visitor who viewed a few forms would otherwise be
	 * unable to load, or pay with, any form. It also must not use the payment
	 * routes' file-backed log, which every visitor shares. Form-data requests
	 * are counted per visitor in the object cache, with a higher limit (see
	 * {@see FormDataRateLimiter}); nothing is counted without a persistent
	 * object cache.
	 *
	 * @since 4.17.5
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request The request object.
	 * @return bool True if the request is allowed, false if the limit is exceeded.
	 */
	protected function validate_form_data_rate_limit( $request ) {
		return ( new FormDataRateLimiter() )->is_allowed( $request );
	}

	/**
	 * Creates a fresh per-form nonce with a shortened lifetime (a 1-hour
	 * `nonce_life`, so valid for 30-60 minutes after it is minted).
	 *
	 * Shared by the form-data REST response and server-rendered inline
	 * configs (e.g. the block editor preview via
	 * {@see \SimplePay\Core\Block\PaymentFormBlock}) so every payment form
	 * initialization path carries a nonce that
	 * `AbstractPaymentRoute::check_form_nonce()` accepts.
	 *
	 * Delegates to {@see PaymentFormNonce::create_short_lived()}, which also
	 * owns verification, so the minted TTL and the accepted TTLs cannot drift.
	 *
	 * @since 4.17.5
	 *
	 * @param int $form_id Payment form ID.
	 * @return string Nonce for the `simpay_payment_form_{form_id}` action.
	 */
	public static function create_form_nonce( $form_id ) {
		return PaymentFormNonce::create_short_lived( $form_id );
	}
}
