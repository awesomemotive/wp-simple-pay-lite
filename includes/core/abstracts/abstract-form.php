<?php
/**
 * Payment form
 *
 * @package SimplePay\Core\Abstracts
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form class.
 *
 * @since 3.0.0
 */
abstract class Form {

	/**
	 * @since 3.0.0
	 * @var \WP_Post
	 */
	public $post;

	/**
	 * Test mode.
	 *
	 * @since 3.0.0
	 * @var string|bool
	 */
	public $test_mode = '';


	/**
	 * Test Secret Key.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $test_secret_key = '';

	/**
	 * Test Publishable Key.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $test_publishable_key = '';

	/**
	 * Live Secret Key.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $live_secret_key = '';

	/**
	 * Live Publishable Key.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $live_publishable_key = '';

	/**
	 * Stripe Connect Account ID.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $account_id = '';

	/**
	 * Secret Key.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $secret_key = '';

	/**
	 * Publishable Key.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $publishable_key = '';

	/**
	 * Payment Confirmation: Success.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $payment_success_page = '';

	/**
	 * Payment Confirmation: Cancel.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $payment_cancelled_page = '';

	/**
	 * Payment Confirmation: Failure.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $payment_failure_page = '';

	/**
	 * Locale.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $locale = '';

	/**
	 * Stripe Checkout submit type.
	 *
	 * @since 4.7.0
	 * @var string
	 */
	public $checkout_submit_type = 'pay';

	/**
	 * Country code.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $country = '';

	/**
	 * Currency.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $currency = '';

	/**
	 * Currency position.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $currency_position = '';

	/**
	 * Decimal separator.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $decimal_separator = '';

	/**
	 * Thousand separator.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $thousand_separator = '';

	/**
	 * One-time amount.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $amount = '';

	/**
	 * Statement descriptor.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $statement_descriptor = '';

	/**
	 * Company name (form title).
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $company_name = '';

	/**
	 * Item desccription (form description).
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $item_description = '';

	/**
	 * Stripe Checkout: Image URL (form logo).
	 *
	 * @since 3.0.0
	 * @var string
	 */
	public $image_url = '';

	/**
	 * Stripe Checkout: Billing address
	 *
	 * @since 3.0.0
	 * @var bool
	 */
	public $enable_billing_address = false;

	/**
	 * Stripe Checkout: Shipping address
	 *
	 * @since 3.0.0
	 * @var bool
	 */
	public $enable_shipping_address = false;

	/**
	 * Form ID.
	 *
	 * @since 3.0.0
	 * @var int
	 */
	public $id = 0;

	/**
	 * Form filter.
	 *
	 * @since 3.0.0
	 * @deprecated 4.0.0
	 * @var string
	 */
	public $filter = '';

	/**
	 * Form custom fields.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public $custom_fields = array();

	/**
	 * Form subscription amount.
	 *
	 * @since 4.11.0
	 * @var int|float
	 */
	public $subscription_amount;

	/**
	 * Form subscription plans.
	 *
	 * @since 4.11.0
	 * @var array
	 */
	public $plans = array();

	/**
	 * Form subscription plan.
	 *
	 * @since 4.11.0
	 * @var mixed
	 */
	public $single_plan;

	/**
	 * Form subscription plan.
	 *
	 * @since 4.11.0
	 * @var mixed
	 */
	public $default_plan;

	/**
	 * Form subscription display type.
	 *
	 * @since 4.11.0
	 * @var mixed
	 */
	public $subscription_display_type;

	/**
	 * Form subscription custom amount label.
	 *
	 * @since 4.11.0
	 * @var string
	 */
	public $subscription_custom_amount_label;

	/**
	 * Form subscription default amount.
	 *
	 * @since 4.11.0
	 * @var mixed
	 */
	public $subscription_default_amount;

	/**
	 * Form subscription max charges.
	 *
	 * @since 4.11.0
	 * @var mixed
	 */
	public $subscription_max_charges;

	/**
	 * Form recurring tax amount.
	 *
	 * @since 4.11.0
	 * @var float
	 */
	public $recurring_tax_amount;

	/**
	 * Form recurring total amount.
	 *
	 * @since 4.11.0
	 * @var float
	 */
	public $recurring_total_amount;

	/**
	 * Form recurring total amount.
	 *
	 * @since 4.11.0
	 * @var bool
	 */
	public $has_max_charges;

	/**
	 * Form prices.
	 *
	 * @since 4.11.0
	 * @var mixed
	 */
	public $prices;

	/**
	 * Form constructor.
	 *
	 * @param int $id Payment Form ID.
	 */
	public function __construct( $id ) {

		// Setup the post object.
		$this->set_post_object( $id );

		if ( null === $this->post ) {
			return;
		}

		// Set our form specific filter to apply to each setting.
		$this->filter = 'simpay_form_' . $this->id;

		// Setup the global settings tied to this form.
		$this->set_global_settings();

		// Setup the post meta settings tied to this form.
		$this->set_post_meta_settings();

		global $simpay_form;

		// Set global form object to this instance.
		$simpay_form = $this;

		$this->maybe_register_hooks();

		do_action( 'simpay_form_loaded' );
	}

	/**
	 * Determine if hooks should be registered.
	 *
	 * Hooks get run once per form instance. See https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/617
	 */
	public function maybe_register_hooks() {

		global $simpay_displayed_form_ids;

		if ( ! is_array( $simpay_displayed_form_ids ) ) {
			$simpay_displayed_form_ids = array();
		}

		// Collect any form IDs we've displayed already so we can avoid duplicate IDs.
		if ( ! isset( $simpay_displayed_form_ids[ $this->id ] ) ) {
			$this->register_hooks();

			$simpay_displayed_form_ids[ $this->id ] = true;
		}
	}

	/**
	 * Add hooks and filters for this form instance.
	 *
	 * Hooks get run once per form instance.
	 *
	 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/617
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {}

	/**
	 * Setup the post object for this form.
	 *
	 * @since 3.0.0
	 *
	 * @param int|\SimplePay\Core\Abastracts\Form|\WP_Post $form Payment Form.
	 */
	public function set_post_object( $form ) {
		if ( is_numeric( $form ) ) {
			$this->id   = absint( $form );
			$this->post = get_post( $this->id );
		} elseif ( $form instanceof Form ) {
			$this->id   = absint( $form->id );
			$this->post = $form->post;
		} elseif ( $form instanceof \WP_Post ) {
			$this->id   = absint( $form->ID );
			$this->post = $form;
		} elseif ( isset( $form->id ) && isset( $form->post ) ) {
			$this->id   = $form->id;
			$this->post = $form->post;
		}
	}

	/**
	 * Determines if the Payment Form is using a live API.
	 *
	 * @since 3.9.0
	 *
	 * @return bool True if accessing Stripe's live API.
	 */
	public function is_livemode() {
		// Legacy filter.
		$test_mode = simpay_get_filtered(
			'test_mode',
			simpay_get_setting( 'test_mode', 'enabled' ),
			$this->id
		);

		// Convert to bool.
		$test_mode = empty( $test_mode ) || 'enabled' === $test_mode;

		// Per-form setting.
		$livemode = simpay_get_saved_meta( $this->id, '_livemode', '' );

		// Use per-form or global setting with backwards-compatible property.
		return '' !== $livemode
			? true === (bool) $livemode
			: false === $test_mode;
	}

	/**
	 * Returns per-request arguments for use when making a Stripe API
	 * request on behalf of the current Payment Form's context.
	 *
	 * @since 3.9.0
	 *
	 * @param array $args {
	 *   Arguments to modify the per-request arguments.
	 *
	 *   @type string $api_key  Set a specific secret key.
	 *   @type bool   $livemode Force livemode.
	 * }
	 * @return array {
	 *   Additional request arguments to send to the Stripe API when making a request.
	 *
	 *   @type string $api_key API Secret Key to use.
	 * }
	 */
	public function get_api_request_args( $args = array() ) {
		$request_args = array();
		$defaults     = array(
			'api_key'  => null,
			'livemode' => $this->is_livemode(),
		);

		$args = wp_parse_args( $args, $defaults );

		// Use a specific API key.
		if ( null !== $args['api_key'] ) {
			$request_args['api_key'] = $args['api_key'];
		} else {
			// Determine which key to use based on `livemode`.
			if ( true === $args['livemode'] ) {
				$request_args['api_key'] = simpay_get_filtered(
					'secret_key',
					$this->live_secret_key,
					$this->id
				);
			} elseif ( false === $args['livemode'] ) {
				$request_args['api_key'] = simpay_get_filtered(
					'secret_key',
					$this->test_secret_key,
					$this->id
				);
			} else {
				$request_args['api_key'] = $this->secret_key;
			}
		}

		return $request_args;
	}

	/**
	 * Determine the display type of the form.
	 *
	 * @since 3.6.0
	 *
	 * @return string
	 */
	public function get_display_type() {
		return simpay_get_saved_meta( $this->id, '_form_display_type', 'stripe_checkout' );
	}

	/**
	 * Set the global settings options to the form attributes.
	 *
	 * @since 3.0.0
	 */
	public function set_global_settings() {
		// Global keys.
		$this->test_secret_key      = simpay_get_setting( 'test_secret_key', '' );
		$this->test_publishable_key = simpay_get_setting( 'test_publishable_key', '' );
		$this->live_secret_key      = simpay_get_setting( 'live_secret_key', '' );
		$this->live_publishable_key = simpay_get_setting( 'live_publishable_key', '' );

		// Choose keys based on current mode.
		$secret_key = true === $this->is_livemode()
			? $this->live_secret_key
			: $this->test_secret_key;

		$this->secret_key = simpay_get_filtered( 'secret_key', $secret_key, $this->id );

		$publishable_key = true === $this->is_livemode()
			? $this->live_publishable_key
			: $this->test_publishable_key;

		$this->publishable_key = simpay_get_filtered( 'publishable_key', $publishable_key, $this->id );

		// Backwards compat.
		$this->test_mode  = false === $this->is_livemode();
		$this->account_id = simpay_get_filtered( 'account_id', $this->account_id, $this->id );

		// Success Page.
		$payment_success_page       = simpay_get_setting( 'success_page', '' );
		$this->payment_success_page = add_query_arg(
			array(
				'form_id' => $this->id,
			),
			simpay_get_filtered(
				'payment_success_page',
				$this->get_redirect_url( $payment_success_page ),
				$this->id
			)
		);

		// Failure Page.
		$payment_failure_page       = simpay_get_setting( 'failure_page', '' );
		$this->payment_failure_page = add_query_arg(
			array(
				'form_id' => $this->id,
			),
			simpay_get_filtered(
				'payment_failure_page',
				$this->get_redirect_url( $payment_failure_page, true ),
				$this->id
			)
		);

		// Cancel page.
		$payment_cancelled_page = simpay_get_setting( 'cancelled_page', '' );

		if ( empty( $payment_cancelled_page ) ) {
			$payment_cancelled_page = $payment_failure_page;
		}

		$this->payment_cancelled_page = simpay_get_filtered(
			'payment_cancelled_page',
			get_permalink( $payment_cancelled_page ),
			$this->id
		);

		// Locale.
		$locale = simpay_get_setting( 'stripe_checkout_locale', 'auto' );

		// Previously an empty value was English. Force that to `en` to avoid errors.
		if ( '' === $locale ) {
			$locale = 'en';
		}

		$this->locale = ! empty( $locale ) ? $locale : $fallback;

		// Country.
		$this->country = simpay_get_filtered(
			'country',
			simpay_get_setting( 'account_country', 'US' ),
			$this->id
		);

		// Stripe needs something, so default to US if settings haven't been saved.
		if ( empty( $this->country ) ) {
			$this->country = 'US';
		}

		// Currency.
		$this->currency = simpay_get_filtered(
			'currency',
			simpay_get_setting( 'currency', 'USD' ),
			$this->id
		);

		$this->currency_position = simpay_get_filtered(
			'currency_position',
			simpay_get_setting( 'currency_position', 'left' ),
			$this->id
		);

		$decimal_separator = 'yes' === simpay_get_setting( 'separator', 'no' )
			? ','
			: '.';

		$this->decimal_separator = simpay_get_filtered(
			'decimal_separator',
			$decimal_separator,
			$this->id
		);

		$thousand_separator = 'yes' === simpay_get_setting( 'separator', 'no' )
			? '.'
			: ',';

		$this->thousand_separator = simpay_get_filtered(
			'thousand_separator',
			$thousand_separator,
			$this->id
		);
	}

	/**
	 * Set the form settings options to the form attributes.
	 *
	 * @since 3.0.0
	 */
	public function set_post_meta_settings() {

		// Custom Fields.
		$custom_fields  = simpay_get_saved_meta( $this->id, '_custom_fields', array() );
		$_custom_fields = array();

		foreach ( $custom_fields as $type => $fields ) {
			foreach ( $fields as $k => $field ) {
				$field['type']    = $type;
				$_custom_fields[] = $field;
			}
		}

		$this->custom_fields = simpay_get_filtered(
			'get_custom_fields',
			$_custom_fields,
			$this->id
		);

		// Amount.
		$this->amount = simpay_unformat_currency(
			simpay_get_filtered(
				'amount',
				simpay_get_saved_meta( $this->id, '_amount', simpay_global_minimum_amount() ),
				$this->id
			)
		);

		// Statement descriptor.
		$this->statement_descriptor = simpay_validate_statement_descriptor(
			simpay_get_filtered( 'statement_descriptor', '', $this->id )
		);

		// Checkout button text.
		$this->checkout_button_text = simpay_get_filtered(
			'checkout_button_text',
			simpay_get_saved_meta(
				$this->id,
				'_checkout_button_text',
				esc_html(
					sprintf(
						/* translators: %s Amount to pay. */
						__( 'Pay %s', 'stripe' ),
						'{{amount}}'
					)
				)
			),
			$this->id
		);

		// Company name.
		$this->company_name = simpay_get_filtered(
			'company_name',
			simpay_get_saved_meta( $this->id, '_company_name' ),
			$this->id
		);

		// Item description.
		$this->item_description = simpay_get_filtered(
			'item_description',
			simpay_get_saved_meta( $this->id, '_item_description' ),
			$this->id
		);

		// Image URL.
		$this->image_url = simpay_get_filtered(
			'image_url',
			simpay_get_saved_meta( $this->id, '_image_url' ),
			$this->id
		);

		// Checkout button.
		$submit_type = simpay_get_filtered(
			'checkout_submit_type',
			simpay_get_saved_meta( $this->id, '_checkout_submit_type' ),
			$this->id
		);

		$this->checkout_submit_type = empty( $submit_type )
			? 'pay'
			: $submit_type;

		// Billing/Shipping address.
		$this->enable_billing_address = simpay_get_filtered(
			'enable_billing_address',
			$this->set_bool_value(
				simpay_get_saved_meta( $this->id, '_enable_billing_address' )
			),
			$this->id
		);

		$this->enable_shipping_address = simpay_get_filtered(
			'enable_shipping_address',
			$this->set_bool_value(
				simpay_get_saved_meta( $this->id, '_enable_shipping_address' )
			),
			$this->id
		);
	}

	/**
	 * Find the page by post ID and return the permalink.
	 *
	 * @since 3.0.0
	 *
	 * @param int  $page_id ID of page to redirect to.
	 * @param bool $failure_page If this URL is for the failure page.
	 * @return false|string
	 */
	public function get_redirect_url( $page_id, $failure_page = false ) {

		// If we are getting success page then check the form settings first.
		if ( ! $failure_page ) {
			$success_redirect_type = simpay_get_saved_meta( $this->id, '_success_redirect_type' );

			if ( 'page' === $success_redirect_type ) {
				$page_id = simpay_get_saved_meta( $this->id, '_success_redirect_page' );
			} elseif ( 'redirect' === $success_redirect_type ) {
				return esc_url( simpay_get_saved_meta( $this->id, '_success_redirect_url' ) );
			}
		}

		// Fallback for using default global setting and for getting the failure page URL.
		if ( empty( $page_id ) ) {
			return '';
		}

		$page = get_post( $page_id );

		if ( ! empty( $page_id ) ) {
			return get_permalink( $page );
		}

		return '';
	}

	/**
	 * Make a check on bool fields to set them as a boolean value instead of their saved value.
	 *
	 * @since 3.0.0
	 *
	 * @param string $option Option to conver to bool.
	 * @param string $check Existing value.
	 * @return bool
	 */
	private function set_bool_value( $option, $check = '' ) {

		if ( ! empty( $check ) ) {
			return ( $check === $option ? true : false );
		}

		return ( 'yes' === $option ? true : false );
	}

	/**
	 * Set all the script variables for the Stripe specific settings.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_stripe_script_variables() {

		// Key is required so we always include it.
		$strings['strings']['key']                = $this->publishable_key;
		$strings['strings']['stripe_api_version'] = SIMPLE_PAY_STRIPE_API_VERSION;

		// Redirect URLs.
		$strings['strings']['success_url'] = $this->payment_success_page;
		$strings['strings']['error_url']   = $this->payment_failure_page;

		// Boolean/dropdown options.
		$bools = array(
			'bools' => array(),
		);

		if ( $this->enable_billing_address ) {
			$bools['bools']['billingAddress'] = true;
		}

		// Optional params if set in the settings only.

		// Company name.
		if ( ! empty( $this->company_name ) ) {
			$strings['strings']['name'] = $this->company_name;
		}

		// Image URL.
		if ( ! empty( $this->image_url ) ) {
			$strings['strings']['image'] = $this->image_url;
		}

		// Locale.
		if ( ! empty( $this->locale ) ) {
			$strings['strings']['locale'] = $this->locale;
		}

		// Country.
		if ( ! empty( $this->country ) ) {
			$strings['strings']['country'] = $this->country;
		}

		// Currency.
		if ( ! empty( $this->currency ) ) {
			$strings['strings']['currency'] = $this->currency;
		}

		// Item description.
		if ( ! empty( $this->item_description ) ) {
			$strings['strings']['description'] = $this->item_description;
		}

		// Return as hookable data.
		return apply_filters( 'simpay_stripe_script_variables', array_merge( $strings, $bools ) );
	}

	/**
	 * Determines if the payment form has fee recovery enabled for any of the payment methods.
	 *
	 * @since 4.7.0
	 *
	 * @return bool
	 */
	abstract public function has_fee_recovery();

	/**
	 * Determines if the payment form forces fee recovery to be paid.
	 *
	 * @since 4.6.6
	 *
	 * @return bool
	 */
	public function has_forced_fee_recovery() {
		$fee_recovery_toggle = array_filter(
			$this->custom_fields,
			function ( $field ) {
				return 'fee_recovery_toggle' === $field['type'];
			}
		);

		// No custom field toggle is present. It's forced.
		if ( empty( $fee_recovery_toggle ) ) {
			return true;
		}

		$fee_recovery_toggle = current( $fee_recovery_toggle );

		return isset( $fee_recovery_toggle['on_by_default'] );
	}

	/**
	 * Determines if any part of this payment form can be purchased.
	 *
	 * @since 4.6.4
	 *
	 * @return bool True if there is at least one purchasable price option.
	 */
	public function has_available_inventory() {
		if ( false === $this->is_managing_inventory() ) {
			return true;
		}

		$behavior  = $this->get_inventory_behavior();
		$has_stock = false;

		switch ( $behavior ) {
			case 'combined':
				$combined  = $this->get_combined_inventory_data();
				$has_stock = $combined['available'] > 0;

				break;
			case 'individual':
				$individual = $this->get_individual_inventory_data();
				$in_stock   = array_filter(
					$individual,
					function ( $price_option ) {
						return $price_option['available'] > 0;
					}
				);

				$has_stock = ! empty( $in_stock );

				break;
		}

		return $has_stock;
	}

	/**
	 * Determines if the payment form is within its current schedule.
	 *
	 * @since 4.6.4
	 *
	 * @return bool True if the payment form within its set schedule.
	 */
	public function has_available_schedule() {
		// Start.
		$schedule_start     = get_post_meta(
			$this->id,
			'_schedule_start',
			true
		);
		$schedule_start_gmt = get_post_meta(
			$this->id,
			'_schedule_start_gmt',
			true
		);

		// End.
		$schedule_end     = get_post_meta(
			$this->id,
			'_schedule_end',
			true
		);
		$schedule_end_gmt = get_post_meta(
			$this->id,
			'_schedule_end_gmt',
			true
		);

		// Start and End.
		if ( 'yes' === $schedule_start && 'yes' === $schedule_end ) {
			return time() >= $schedule_start_gmt && time() <= $schedule_end_gmt;
		}

		// Start only.
		if ( 'yes' === $schedule_start && 'no' === $schedule_end ) {
			return time() >= $schedule_start_gmt;
		}

		// End only.
		if ( 'yes' === $schedule_end && 'no' === $schedule_start ) {
			return time() <= $schedule_end_gmt;
		}

		return true;
	}

	/**
	 * Determines if the payment form is managing inventory/stock.
	 *
	 * @since 4.6.4
	 *
	 * @return bool True if the payment form is managing inventory.
	 */
	public function is_managing_inventory() {
		$inventory = get_post_meta( $this->id, '_inventory', true );

		return 'yes' === $inventory;
	}

	/**
	 * Returns the form's inventory management behavior.
	 *
	 * @since 4.6.4
	 *
	 * @return string combined|individual|'' Empty if there is no inventory management.
	 */
	public function get_inventory_behavior() {
		if ( false === $this->is_managing_inventory() ) {
			return '';
		}

		/** @var string $behavior */
		$behavior = get_post_meta( $this->id, '_inventory_behavior', true );

		if ( empty( $behavior ) ) {
			return '';
		}

		return $behavior;
	}

	/**
	 * Returns the combined inventory data.
	 *
	 * @since 4.6.4
	 *
	 * @return array<string, int>
	 */
	public function get_combined_inventory_data() {
		/** @var array<string, int> $combined Combined inventory data. */
		$combined = get_post_meta(
			$this->id,
			'_inventory_behavior_combined',
			true
		);

		if (
			empty( $combined ) ||
			! isset( $combined['initial'], $combined['available'] )
		) {
			return array(
				'initial'   => 0,
				'available' => 0,
			);
		}

		return array(
			'initial'   => intval( $combined['initial'] ),
			'available' => intval( $combined['available'] ),
		);
	}

	/**
	 * Returns the individual price option inventory data.
	 *
	 * @since 4.6.4
	 *
	 * @return array<string, array<string, int>>
	 */
	public function get_individual_inventory_data() {
		/** @var array<string, array<string, int>> $individual */
		$individual = get_post_meta(
			$this->id,
			'_inventory_behavior_individual',
			true
		);

		if ( empty( $individual ) ) {
			return array();
		}

		foreach ( $individual as $instance_id => $inventory ) {
			$individual[ $instance_id ] = array(
				'initial'   => intval( $inventory['initial'] ),
				'available' => intval( $inventory['available'] ),
			);
		}

		return $individual;
	}

	/**
	 * Decrements inventory on the payment form.
	 *
	 * @since 4.6.4
	 *
	 * @param 'increment'|'decrement' $increment_or_decrement Whether to increment or decrement.
	 * @param int                     $quantity Quantity to increment or decrement.
	 * @param null|string             $price_instance_id Price option instance ID. Null if using combined stock.
	 */
	public function adjust_inventory(
		$increment_or_decrement,
		$quantity,
		$price_instance_id
	) {
		if ( ! in_array(
			$increment_or_decrement,
			array( 'increment', 'decrement' ),
			true
		) ) {
			return;
		}

		$behavior = $this->get_inventory_behavior();
		$quantity = intval( $quantity );

		switch ( $behavior ) {
			case 'combined':
				$combined = $this->get_combined_inventory_data();

				update_post_meta(
					$this->id,
					'_inventory_behavior_combined',
					array(
						'initial'   => $combined['initial'],
						'available' => 'increment' === $increment_or_decrement
							? $combined['available'] + $quantity
							: $combined['available'] - $quantity,
					)
				);

				break;

			case 'individual':
				$individual = $this->get_individual_inventory_data();

				if ( isset( $individual[ $price_instance_id ] ) ) {
					$individual[ $price_instance_id ] = array(
						'initial'   => $individual[ $price_instance_id ]['initial'],
						'available' => 'increment' === $increment_or_decrement
							? $individual[ $price_instance_id ]['available'] + $quantity
							: $individual[ $price_instance_id ]['available'] - $quantity,
					);

					update_post_meta(
						$this->id,
						'_inventory_behavior_individual',
						$individual
					);
				}

				break;
		}
	}

	/**
	 * Returns the form's success message.
	 *
	 * @since 4.7.9
	 *
	 * @return string
	 */
	public function get_confirmation_message() {
		return get_post_meta( $this->id, '_success_message', true );
	}

	/**
	 * Returns the form's email confirmation message.
	 *
	 * @since 4.7.9
	 *
	 * @return string
	 */
	public function get_email_confirmation_message() {
		return get_post_meta( $this->id, '_email_confirmation_message', true );
	}

	/**
	 * Returns the form's email notification message.
	 *
	 * @since 4.7.9
	 *
	 * @return string
	 */
	public function get_email_notification_message() {
		return get_post_meta( $this->id, '_email_notification_message', true );
	}

	/**
	 * Returns the form's multiple line items setting.
	 *
	 * @since 4.11.0
	 *
	 * @return bool
	 */
	public function allows_multiple_line_items() {
		return 'yes' === get_post_meta( $this->id, '_allow_purchasing_multiple_line_items', true );
	}
}
