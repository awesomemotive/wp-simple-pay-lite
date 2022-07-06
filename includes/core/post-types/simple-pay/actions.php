<?php
/**
 * Simple Pay: Actions
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Actions
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Actions;

use Exception;
use SimplePay\Core\API;
use SimplePay\Vendor\Stripe\Exception\ApiErrorException;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saves the Payment Form's settings.
 *
 * @since 3.8.0
 *
 * @param int      $post_id Current Payment Form ID.
 * @param \WP_Post $post    Current Payment Form \WP_Post object.
 * @param bool     $update  Whether this is an existing Payment Form or update.
 */
function save( $post_id, $post, $update ) {
	// Bail if we have no Payment Form.
	if ( empty( $post_id ) || empty( $post ) ) {
		return;
	}

	// Bail if doing an autosave or is a revision.
	if (
		defined( 'DOING_AUTOSAVE' ) ||
		is_int( wp_is_post_revision( $post ) ) ||
		is_int( wp_is_post_autosave( $post ) )
	) {
		return;
	}

	// Bail if we cannot verify our nonce.
	if (
		empty( $_POST['simpay_meta_nonce'] ) ||
		! wp_verify_nonce( $_POST['simpay_meta_nonce'], 'simpay_save_data' )
	) {
		return;
	}

	// Bail if the current user does not have permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$form = simpay_get_form( $post_id );

	if ( false === $form ) {
		return;
	}

	// Payment Mode.
	$livemode = isset( $_POST['_livemode'] ) && '' !== $_POST['_livemode']
		? absint( $_POST['_livemode'] )
		: '';

	update_post_meta(
		$post_id,
		'_livemode_prev',
		get_post_meta( $post_id, '_livemode', true )
	);

	update_post_meta( $post_id, '_livemode', $livemode );

	// Success redirect type.
	$success_redirect_type = isset( $_POST['_success_redirect_type'] )
		? esc_attr( $_POST['_success_redirect_type'] )
		: 'default';

	update_post_meta( $post_id, '_success_redirect_type', $success_redirect_type );

	// Success redirect page.
	$success_redirect_page = isset( $_POST['_success_redirect_page'] )
		? esc_attr( $_POST['_success_redirect_page'] )
		: '';

	update_post_meta( $post_id, '_success_redirect_page', $success_redirect_page );

	// Success redirect URL.
	$success_redirect_url = isset( $_POST['_success_redirect_url'] )
		? esc_url( $_POST['_success_redirect_url'] )
		: '';

	update_post_meta( $post_id, '_success_redirect_url', $success_redirect_url );

	// Company name.
	$company_name = isset( $_POST['_company_name'] )
		? sanitize_text_field( $_POST['_company_name'] )
		: '';

	update_post_meta( $post_id, '_company_name', $company_name );

	// Item description.
	$item_description = isset( $_POST['_item_description'] )
		? sanitize_text_field( $_POST['_item_description'] )
		: '';

	update_post_meta( $post_id, '_item_description', $item_description );

	// Image URL.
	$image_url = isset( $_POST['_image_url'] )
		? sanitize_text_field( $_POST['_image_url'] )
		: '';

	update_post_meta( $post_id, '_image_url', $image_url );

	// Submit type.
	$checkout_submit_type = isset( $_POST['_checkout_submit_type'] )
		? sanitize_text_field( $_POST['_checkout_submit_type'] )
		: 'pay';

	update_post_meta( $post_id, '_checkout_submit_type', $checkout_submit_type );

	// Billing address.
	$enable_billing_address = isset( $_POST['_enable_billing_address'] )
		? 'yes'
		: 'no';

	update_post_meta( $post_id, '_enable_billing_address', $enable_billing_address );

	// Shipping address.
	$enable_shipping_address = isset( $_POST['_enable_shipping_address'] )
		? 'yes'
		: 'no';

	update_post_meta( $post_id, '_enable_shipping_address', $enable_shipping_address );

	// Phone number.
	$enable_phone = isset( $_POST['_enable_phone'] ) ? 'yes' : 'no';

	update_post_meta( $post_id, '_enable_phone', $enable_phone );

	// Promotion codes.
	$enable_promotion_codes = isset( $_POST['_enable_promotion_codes'] )
		? 'yes'
		: 'no';

	update_post_meta(
		$post_id,
		'_enable_promotion_codes',
		$enable_promotion_codes
	);

	// Tax ID.
	$enable_tax_id = isset( $_POST['_enable_tax_id'] ) ? 'yes' : 'no';

	update_post_meta( $post_id, '_enable_tax_id', $enable_tax_id );

	// Quantity.
	$enable_quantity = isset( $_POST['_enable_quantity'] ) ? 'yes' : 'no';

	update_post_meta( $post_id, '_enable_quantity', $enable_quantity );

	// Custom fields.
	// Handles "Button Text" and "Button Processing Text".
	$fields = isset( $_POST['_simpay_custom_field'] )
		? $_POST['_simpay_custom_field']
		: array();

	update_post_meta( $post_id, '_custom_fields', $fields );

	if ( false === $update ) {
		/**
		 * Allows further action to be taken when a Payment Form is created.
		 *
		 * @since 3.0.0
		 *
		 * @param int $post_id Payment Form ID.
		 */
		do_action( 'simpay_form_created', $post_id );
	}

	/**
	 * Allows further action to be taken when a Payment Form is updated.
	 *
	 * @since 3.0.0
	 *
	 * @param int                            $post_id Payment Form ID.
	 * @param \WP_Post                       $post Payment Form \WP_Post object.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
	 */
	do_action( 'simpay_save_form_settings', $post_id, $post, $form );
}
add_action( 'save_post_simple-pay', __NAMESPACE__ . '\\save', 10, 3 );

/**
 * Saves the Payment Form's container Product.
 *
 * @since 4.1.0
 *
 * @param int                            $post_id Payment Form ID.
 * @param \WP_Post                       $post Payment Form \WP_Post object.
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @return \SimplePay\Vendor\Stripe\Product
 */
function save_product( $post_id, $post, $form ) {
	$error        = new WP_Error();
	$product_args = array();

	$product_key = true === $form->is_livemode()
		? '_simpay_product_live'
		: '_simpay_product_test';

	$form_product = get_post_meta( $post_id, $product_key, true );

	// Name.
	$title = get_post_meta( $form->id, '_company_name', true );
	$name  = ! empty( $title ) ? $title : get_bloginfo( 'name' );

	// https://github.com/wpsimplepay/wp-simple-pay-pro/issues/1598
	if ( empty( $name ) ) {
		$name = sprintf(
			__( 'WP Simple Pay - Form %d', 'stripe' ),
			$form->id
		);
	}

	$product_args['name'] = esc_html( $name );

	// Description. Optional.
	$description = get_post_meta( $form->id, '_item_description', true );

	// Images. Optional.
	if ( '' !== $form->image_url ) {
		$product_args['images'] = array(
			$form->image_url,
		);
	}

	try {
		if ( empty( $form_product ) ) {
			if ( ! empty( $description ) ) {
				$product_args['description'] = sanitize_text_field( $description );
			}

			$product = API\Products\create(
				$product_args,
				$form->get_api_request_args()
			);

			update_post_meta( $form->id, $product_key, $product->id );

			$form_product = $product->id;
		} else {
			$product_args['description'] = sanitize_text_field( $description );

			// Try to update an existing product.
			try {
				$product = API\Products\update(
					$form_product,
					$product_args,
					$form->get_api_request_args()
				);

				// Create a new one if the previous cannot be updated.
			} catch ( \Exception $e ) {
				$product = API\Products\create(
					$product_args,
					$form->get_api_request_args()
				);

				update_post_meta( $form->id, $product_key, $product->id );

				$form_product = $product->id;
			}
		}
	} catch ( ApiErrorException $e ) {
		$error->add(
			$e->getStripeCode(),
			$e->getMessage(),
			array(
				'status' => $e->getHttpStatus(),
			)
		);
	} catch ( Exception $e ) {
		$error->add(
			'simpay-update-payment-form',
			$e->getMessage(),
			array(
				'status' => 500,
			)
		);
	}

	if ( ! empty( $error->errors ) ) {
		$redirect = add_query_arg(
			array(
				'post_type'               => 'simple-pay',
				'post'                    => $post_id,
				'action'                  => 'edit',
				'simpay-stripe-api-error' => $error->get_error_message(),
			),
			admin_url( 'post.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	return $product;
}

/**
 * Saves the Payment Form's Prices.
 *
 * @since 4.1.0
 *
 * @param int                            $post_id Payment Form ID.
 * @param \WP_Post                       $post Payment Form \WP_Post object.
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 */
function save_prices( $post_id, $post, $form ) {
	$error   = new WP_Error();
	$product = save_product( $post_id, $post, $form );

	$prices = isset( $_POST['_simpay_prices'] )
		? $_POST['_simpay_prices']
		: array();

	$_prices = array();

	$prices_key = true === $form->is_livemode()
		? '_simpay_prices_live'
		: '_simpay_prices_test';

	$livemode_prev = get_post_meta( $form->id, '_livemode_prev', true );

	if ( '' === $livemode_prev ) {
		$livemode_prev = ! simpay_is_test_mode();
	}

	// Let the syncing handle the Prices if the mode has changed.
	if ( (bool) $livemode_prev !== $form->is_livemode() ) {
		delete_post_meta( $post_id, sprintf( '%s_modified', $prices_key ), time() );

		return;
	}

	foreach ( $prices as $instance_id => $price ) {
		$price_args = array(
			'default' => isset( $price['default'] ) || 1 === count( $prices ),
		);

		$recurring_args = array();
		$line_item_args = array();

		// Setup shared arguments.

		$currency    = sanitize_text_field( $price['currency'] );
		$unit_amount = sanitize_text_field( $price['unit_amount'] );

		// Short circuit for Lite. If the current unit amount is
		// the same as the new amount, do nothing.
		$unit_amount_current = isset( $price['unit_amount_current'] )
			? sanitize_text_field( $price['unit_amount_current'] )
			: false;

		if (
			false !== $unit_amount_current &&
			$unit_amount_current === $unit_amount
		) {
			$price_id = sanitize_text_field( $price['id_current'] );

			$_prices[ $instance_id ] = array_merge(
				$price_args,
				array(
					'id' => $price_id,
				)
			);

			continue;
		}

		$is_zero_decimal     = simpay_is_zero_decimal( $currency );
		$currency_min_amount = simpay_get_currency_minimum( $currency );

		$unit_amount = $is_zero_decimal
			? simpay_unformat_currency( $unit_amount )
			: simpay_convert_amount_to_cents( $unit_amount );

		if ( $unit_amount < $currency_min_amount ) {
			$unit_amount = $currency_min_amount;
		}

		$amount_type = 'recurring' === $price['amount_type']
			? 'recurring'
			: 'one-time';

		$can_recur = isset( $price['can_recur'] );

		$label = isset( $price['label'] )
			? sanitize_text_field( $price['label'] )
			: '';

		if ( 'recurring' === $amount_type || $can_recur ) {
			$recurring = isset( $price['recurring'] )
				? $price['recurring']
				: array();

			if ( isset( $recurring['id'] ) ) {
				$recurring_args['id'] = $recurring['id'];
			}

			$recurring_args['interval'] = isset( $recurring['interval'] )
				? sanitize_text_field( $recurring['interval'] )
				: 'month';

			$recurring_args['interval_count'] = isset( $recurring['interval_count'] )
				? (int) $recurring['interval_count']
				: 1;

			$invoice_limit = isset( $recurring['invoice_limit'] )
				? (int) $recurring['invoice_limit']
				: '';

			if ( ! empty( $invoice_limit ) ) {
				$recurring_args['invoice_limit'] = $invoice_limit;
			}

			$trial_period_days = isset( $recurring['trial_period_days'] )
				? (int) $recurring['trial_period_days']
				: '';

			if ( ! empty( $trial_period_days ) ) {
				$recurring_args['trial_period_days'] = $trial_period_days;
			}

			$line_items = isset( $price['line_items'] )
				? $price['line_items']
				: array();

			$setup_fee_amount = isset( $line_items['subscription-setup-fee'] )
				? $line_items['subscription-setup-fee']['unit_amount']
				: '';

			if ( ! empty( $setup_fee_amount ) ) {
				$line_item_args[] = array(
					'unit_amount' => $is_zero_decimal
						? simpay_unformat_currency( $setup_fee_amount )
						: simpay_convert_amount_to_cents( $setup_fee_amount ),
					'currency'    => $currency,
				);
			}

			$plan_fee_amount = isset( $line_items['plan-setup-fee'] )
				? $line_items['plan-setup-fee']['unit_amount']
				: '';

			if ( ! empty( $plan_fee_amount ) ) {
				$line_item_args[] = array(
					'unit_amount' => $is_zero_decimal
						? simpay_unformat_currency( $plan_fee_amount )
						: simpay_convert_amount_to_cents( $plan_fee_amount ),
					'currency'    => $currency,
				);
			}
		} else {
			$recurring_args = array();
		}

		// Custom price data.
		if ( isset( $price['custom'] ) ) {
			$price_args['id']          = $price['id'];
			$price_args['unit_amount'] = $unit_amount;
			$price_args['currency']    = $currency;

			$unit_amount_min = isset( $price['unit_amount_min'] )
				? sanitize_text_field(
					$is_zero_decimal
						? $price['unit_amount_min']
						: simpay_convert_amount_to_cents( $price['unit_amount_min'] )
				)
				: $currency_min_amount;

			if ( $unit_amount_min < $currency_min_amount ) {
				$unit_amount_min = $currency_min_amount;
			}

			$price_args['unit_amount_min'] = $unit_amount_min;

			// Defined Price.
		} else {

			// Existing Price.
			if ( simpay_payment_form_prices_is_defined_price( $price['id'] ) ) {
				$price_args['id'] = $price['id'];

				// Create a new Price.
			} else {
				$stripe_price_args = array(
					'unit_amount' => $unit_amount,
					'currency'    => $currency,
					'product'     => $product->id,
				);

				if ( 'recurring' === $amount_type ) {
					$stripe_price_args['recurring'] = array(
						'interval'       => $recurring_args['interval'],
						'interval_count' => $recurring_args['interval_count'],
					);
				}

				try {
					$stripe_price = API\Prices\create(
						$stripe_price_args,
						$form->get_api_request_args()
					);

					$price_args['id'] = $stripe_price->id;

					if ( $can_recur ) {
						$stripe_recurring_price = API\Prices\create(
							array_merge(
								$stripe_price_args,
								array(
									'recurring'  => array(
										'interval'       =>
											$recurring_args['interval'],
										'interval_count' =>
											$recurring_args['interval_count'],
									),
								)
							),
							$form->get_api_request_args()
						);

						$recurring_args['id'] = $stripe_recurring_price->id;
					}
				} catch ( ApiErrorException $e ) {
					$error->add(
						$e->getStripeCode(),
						$e->getMessage(),
						array(
							'status' => $e->getHttpStatus(),
						)
					);
				} catch ( Exception $e ) {
					$error->add(
						'simpay-update-payment-form',
						$e->getMessage(),
						array(
							'status' => 500,
						)
					);
				}
			}
		}

		if ( ! empty( $error->errors ) ) {
			$redirect = add_query_arg(
				array(
					'post_type'               => 'simple-pay',
					'post'                    => $post_id,
					'action'                  => 'edit',
					'simpay-stripe-api-error' => $error->get_error_message(),
				),
				admin_url( 'post.php' )
			);

			wp_safe_redirect( $redirect );
			exit;
		}

		// Label.
		if ( ! empty( $label ) ) {
			$price_args['label'] = $label;
		}

		// Optional recurring.
		$price_args['can_recur'] = $can_recur;

		// Recurring.
		if ( ! empty( $recurring_args ) ) {
			// Remove data that can be retrieved from Stripe.
			if ( ! isset( $price['custom'] ) ) {
				unset( $recurring_args['interval'] );
				unset( $recurring_args['interval_count'] );
			}

			if ( ! empty( $recurring_args ) ) {
				$price_args['recurring'] = $recurring_args;
			}
		}

		// Line items.
		if ( ! empty( $line_item_args ) ) {
			$price_args['line_items'] = $line_item_args;
		}

		$_prices[ $instance_id ] = $price_args;
	}

	update_post_meta( $post_id, $prices_key, $_prices );
	update_post_meta( $post_id, sprintf( '%s_modified', $prices_key ), time() );
}
add_action(
	'simpay_save_form_settings',
	__NAMESPACE__ . '\\save_prices',
	30,
	3
);

/**
 * Redirects the Payment Form back to the current Payment Form settings tab.
 *
 * @since 3.8.0
 *
 * @param string $location Location to redirect to.
 * @param int    $post_id  Payment Form ID.
 * @return string URL to redirect to.
 */
function save_redirect( $location, $post_id ) {
	$post = get_post( $post_id );

	if ( 'simple-pay' !== $post->post_type ) {
		return $location;
	}

	$hash = isset( $_REQUEST['simpay_form_settings_tab'] )
		? sanitize_text_field( $_REQUEST['simpay_form_settings_tab'] )
		: '#form-display-options-settings-panel';

	$location .= $hash;

	return $location;
}
add_filter( 'redirect_post_location', __NAMESPACE__ . '\\save_redirect', 10, 2 );

/**
 * Duplicates an existing Payment Form.
 *
 * @since 3.8.0
 */
function duplicate() {
	// Bail if no post is found.
	if ( ! isset( $_REQUEST['form'] ) ) {
		return;
	}

	// Bail if no action is found, or not duplicating.
	if ( ! isset( $_REQUEST['simpay-action'] ) || 'duplicate' !== $_REQUEST['simpay-action'] ) {
		return;
	}

	// Bail if the request is invalid.
	check_admin_referer( 'simpay-duplicate-payment-form' );

	$post = get_post( absint( $_GET['form'] ) );

	// Bail if post cannot be found.
	if ( empty( $post ) ) {
		return;
	}

	// Allow empty post.
	add_filter( 'wp_insert_post_empty_content', '__return_false' );

	// Insert the new post using the original post values.
	$duplicate = wp_insert_post(
		array(
			'post_author' => wp_get_current_user()->ID,
			'post_type'   => $post->post_type,
			'post_status' => $post->post_status,
		)
	);

	// If the new post did not get inserted then exit now.
	if ( ! $duplicate ) {
		return;
	}

	// Duplicate metadata.
	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}postmeta (post_id, meta_key, meta_value) SELECT %d, meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d",
			$duplicate,
			$post->ID
		)
	);

	// Remove the linked container Product. Existing Prices will still be linked to
	// the Payment Form that was originally duplicated, but new ones will not.
	delete_post_meta( $duplicate, '_simpay_product_live' );
	delete_post_meta( $duplicate, '_simpay_product_test' );

	// Update form title to append - Duplicate
	$form_name = get_post_meta( $duplicate, '_company_name', true );
	update_post_meta(
		$duplicate,
		'_company_name',
		/* translators: %s Payment form name. */
		sprintf( __( '%s - Duplicate', 'stripe' ), $form_name )
	);

	$redirect = add_query_arg(
		array(
			'post'    => $duplicate,
			'action'  => 'edit',
			'message' => 299,
		),
		admin_url( 'post.php' )
	);

	wp_safe_redirect( $redirect );
}
add_action( 'admin_init', __NAMESPACE__ . '\\duplicate' );
