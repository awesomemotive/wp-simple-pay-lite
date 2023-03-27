<?php
/**
 * Coupon validation route
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment;

use Exception;
use SimplePay\Core\Utils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\CouponUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\SchemaUtils;
use SimplePay\Pro\Coupons\Coupon_Query;
use WP_REST_Response;
use WP_REST_Server;

/**
 * ValidateCouponRoute class.
 *
 * @since 4.7.0
 */
class ValidateCouponRoute extends AbstractPaymentRoute {

	/**
	 * {@inheritdoc}
	 */
	public function register_route() {
		// Route `POST /wpsp/__internal__/payment/validate-coupon`.
		$create_args = array(
			'form_id'     => SchemaUtils::get_form_id_schema(),
			'currency'    => SchemaUtils::get_currency_schema(
				array(
					'required' => true,
				)
			),
			'subtotal'    => SchemaUtils::get_subtotal_schema(
				array(
					'required' => true,
				)
			),
			'coupon_code' => SchemaUtils::get_coupon_code_schema(),
		);

		$create_item_route = array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array(
				$this,
				'create_coupon_validation',
			),
			'permission_callback' => array(
				$this,
				'create_coupon_validation_permissions_check',
			),
			'args'                => $create_args,
		);

		register_rest_route(
			$this->namespace,
			'payment/validate-coupon',
			$create_item_route
		);
	}

	/**
	 * Determines who can validate a coupon using this route.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	public function create_coupon_validation_permissions_check() {
		return true;
	}

	/**
	 * Validates a coupon code for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param \WP_REST_Request $request The coupon validation request.
	 * @return \WP_REST_Response The coupon validation response.
	 * @throws \Exception If the coupon code is invalid.
	 */
	public function create_coupon_validation( $request ) {
		$form        = PaymentRequestUtils::get_form( $request );
		$coupon_code = PaymentRequestUtils::get_coupon_code( $request );

		/** @var string $currency */
		$currency = $request->get_param( 'currency' );

		/** @var int $subtotal */
		$subtotal = $request->get_param( 'subtotal' );

		try {
			if ( empty( $coupon_code ) ) {
				throw new Exception(
					__( 'Please enter a coupon code.', 'stripe' )
				);
			}

			$coupon_data = CouponUtils::get_coupon_data(
				$request,
				$coupon_code,
				$subtotal,
				$currency
			);

			if ( isset( $coupon_data['error'] ) ) {
				/** @var string $error_message */
				$error_message = $coupon_data['error'];
				throw new Exception( $error_message );
			}

			$api_args = $form->get_api_request_args();
			$coupons  = new Coupon_Query(
				$form->is_livemode(),
				$api_args['api_key']
			);

			$simpay_coupon = $coupons->get_by_name( $coupon_code );

			if (
				$simpay_coupon instanceof \SimplePay\Pro\Coupons\Coupon &&
				false === $simpay_coupon->applies_to_form( $form->id )
			) {
				throw new Exception(
					__( 'Coupon is invalid.', 'stripe' )
				);
			}

			return new WP_REST_Response( $coupon_data );
		} catch ( Exception $e ) {
			return new WP_REST_Response(
				array(
					'message' => Utils\handle_exception_message( $e ),
				),
				400
			);
		}
	}

}
