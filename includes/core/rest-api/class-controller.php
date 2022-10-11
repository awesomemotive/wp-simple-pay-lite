<?php
/**
 * REST API: Controller
 *
 * @package SimplePay\Core\REST_API
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Core\REST_API;

use WP_REST_Controller;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller class.
 *
 * @since 3.5.0
 */
abstract class Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $namespace = 'wpsp/v1';

	/**
	 * Route base.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Runs supplied permission checks on a REST API request.
	 *
	 * @since 4.2.0
	 *
	 * @param array            $checks List of permission checks to call.
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return \WP_Error|true Error if a permission check fails.
	 */
	protected function permission_checks( $checks, $request ) {
		foreach ( $checks as $check ) {
			$method = sprintf( 'check_%s', $check );

			if ( false === method_exists( $this, $method ) ) {
				return new \WP_Error(
					'rest_forbidden',
					__(
						'Unable to complete request. Please try again.',
						'stripe'
					),
					array(
						'status' => rest_authorization_required_code(),
					)
				);
			}

			$to_check = $this->$method( $request );

			if ( is_wp_error( $to_check ) ) {
				return $to_check;
			}
		}

		return true;
	}

	/**
	 * Ensures the Stripe cookie has been set by stripe.js.
	 *
	 * @since 4.6.0
	 * @param \WP_REST_Request $request REST API request data.
	 * @return \WP_Error|true Error if the rate limit has been exceeded.
	 */
	protected function check_stripe_cookie( $request ) {
		$check_stripe_cookie = is_ssl();

		/**
		 * Determines if the Stripe __stripe_sid cookie is required to proceed.
		 *
		 * @since 4.6.0
		 *
		 * @param bool $check_stripe_cookie Whether to check for the __stripe_sid cookie.
		 */
		$check_stripe_cookie = apply_filters(
			'simpay_rest_api_check_stripe_cookie',
			$check_stripe_cookie
		);

		if (
			true === $check_stripe_cookie &&
			! isset( $_COOKIE['__stripe_sid'] )
		) {
			return new \WP_Error(
				'rest_forbidden',
				__(
					'Invalid request. Please refresh the page and try again.',
					'stripe'
				),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}
	}

	/**
	 * Determines if the REST API request is valid based on the current rate limit.
	 *
	 * @since 4.2.0
	 *
	 * @param \WP_REST_Request $request {
	 *   Incoming REST API request data.
	 *
	 *   @type array $form_values Values of named fields in the payment form.
	 * }
	 * @return \WP_Error|true Error if the rate limit has been exceeded.
	 */
	protected function check_rate_limit( $request ) {
		$has_exceeded_rate_limit = false;

		/**
		 * Filters if the current IP address has exceeded the rate limit.
		 *
		 * @since 3.9.5
		 *
		 * @param bool $has_exceeded_rate_limit
		 */
		$has_exceeded_rate_limit = apply_filters(
			'simpay_has_exceeded_rate_limit',
			$has_exceeded_rate_limit
		);

		if ( true === $has_exceeded_rate_limit ) {
			return new \WP_Error(
				'rest_forbidden',
				__(
					'Sorry, you have made too many requests. Please try again later.',
					'stripe'
				),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}

	/**
	 * Determines if the REST API request contains a valid Payment Form nonce.
	 *
	 * @since 4.2.0
	 *
	 * @param \WP_REST_Request $request {
	 *   Incoming REST API request data.
	 *
	 *   @type array $form_values Values of named fields in the payment form.
	 * }
	 * @return \WP_Error|true Error if the rate limit has been exceeded.
	 */
	protected function check_form_nonce( $request ) {
		$form_values = $request['form_values'];

		if (
			! isset( $form_values['_wpnonce'] ) ||
			! wp_verify_nonce( $form_values['_wpnonce'], 'simpay_payment_form' )
		) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Invalid request. Please try again.', 'stripe' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}

	/**
	 * Determines if the REST API request contains all required fields.
	 *
	 * @since 4.5.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return \WP_Error|true Error if the required fields are missing.
	 */
	protected function check_required_fields( $request ) {
		$invalid_request = new \WP_Error(
			'rest_forbidden',
			__( 'Invalid request. Please try again.', 'stripe' ),
			array(
				'status' => rest_authorization_required_code(),
			)
		);

		if ( ! isset( $request['form_id'] ) ) {
			return $invalid_request;
		}

		$form_id = intval( $request['form_id'] );
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return $invalid_request;
		}

		$form_values   = $request['form_values'];
		$custom_fields = simpay_get_saved_meta( $form->id, '_custom_fields' );

		foreach ( $custom_fields as $custom_field_type => $custom_field_types ) {
			foreach ( $custom_field_types as $field ) {
				if ( ! isset( $field['required'] ) ) {
					continue;
				}

				// Check custom fields.
				if ( isset( $field['metadata'] ) ) {
					if ( empty( $field['metadata'] ) ) {
						$id = isset( $field['uid'] )
							? $field['uid']
							: '';

						$meta_key = 'simpay-form-' . $form->id . '-field-' . $id;
					} else {
						$meta_key = $field['metadata'];
					}

					if ( ! isset( $form_values['simpay_field'][ $meta_key ] ) ) {
						return $invalid_request;
					}

					$value = trim( $form_values['simpay_field'][ $meta_key ] );

					if ( empty( $value ) ) {
						return $invalid_request;
					}
				}

				// Check Customer fields.
				switch ( $custom_field_type ) {
					case 'address':
						$address_parts = array(
							'line1',
							'city',
							'state',
							'postal_code',
							'country',
						);

						foreach ( $address_parts as $address_part ) {
							if ( ! isset( $form_values[ 'simpay_billing_address_' . $address_part ] ) ) {
								return $invalid_request;
							}

							$value = trim(
								$form_values[ 'simpay_billing_address_' . $address_part ]
							);

							if ( empty( $value ) ) {
								return $invalid_request;
							}
						}

						break;
					case 'tax_id':
						if (
							! isset( $form_values['simpay_tax_id'] ) ||
							! isset( $form_values['simpay_tax_id_type'] )
						) {
							return $invalid_request;
						}

						$tax_id   = trim( $form_values['simpay_tax_id'] );
						$tax_type = trim( $form_values['simpay_tax_id_type'] );

						if ( empty( $tax_id ) || empty( $tax_type ) ) {
							return $invalid_request;
						}

						break;
					case 'customer_name':
					case 'telephone':
					case 'email':
						if ( ! isset( $form_values[ 'simpay_' . $custom_field_type ] ) ) {
							return $invalid_request;
						}

						$value = trim(
							$form_values[ 'simpay_' . $custom_field_type ]
						);

						if ( empty( $value ) ) {
							return $invalid_request;
						}

						break;
				}
			}
		}

		return true;
	}

	/**
	 * Determines if the REST API request contains a valid Customer nonce.
	 *
	 * @since 4.2.0
	 *
	 * @param \WP_REST_Request $request {
	 *   Incoming REST API request data.
	 *
	 *   @type string $customer_id Customer ID.
	 *   @type string $customer_nonce Customer nonce.
	 * }
	 * @return \WP_Error|true Error if the rate limit has been exceeded.
	 */
	protected function check_customer_nonce( $request ) {
		// Ensure a Customer ID is available.
		$customer_id = isset( $request['customer_id'] )
			? $request['customer_id']
			: false;

		if ( false === $customer_id ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Invalid customer record. Please try again.', 'stripe' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		$customer_nonce = isset( $request['customer_nonce'] )
			? $request['customer_nonce']
			: false;

		if ( false === $customer_nonce ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Missing customer token. Please try again.', 'stripe' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		// Validate the nonce based on the Customer ID.
		add_filter( 'nonce_life', 'simpay_nonce_life_2_min' );

		$customer_nonce_action = sprintf(
			'simpay_payment_form_customer_%s',
			$customer_id
		);

		$valid_nonce = wp_verify_nonce(
			$customer_nonce,
			$customer_nonce_action
		);

		remove_filter( 'nonce_life', 'simpay_nonce_life_2_min' );

		if ( false === $valid_nonce ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Invalid customer token. Please try again.', 'stripe' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}

}
