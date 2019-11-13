<?php

namespace SimplePay\Core\Abstracts;

use SimplePay\Core\Admin\MetaBoxes\Custom_Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Form {

	/*****
	 *
	 * GLOBAL SETTINGS
	 *
	 *****/

	/** STRIPE KEYS **/

	/* Test mode options */
	public $test_mode = '';

	/* Test Keys */
	public $test_secret_key = '';
	public $test_publishable_key = '';

	/* Live Keys */
	public $live_secret_key = '';
	public $live_publishable_key = '';

	/* Ambiguous Keys */
	public $account_id = '';
	public $secret_key = '';
	public $publishable_key = '';

	/** GENERAL **/

	/* General */
	public $payment_success_page = '';
	public $payment_failure_page = '';
	public $locale = '';
	public $country = '';

	/* Currency Options */
	public $currency = '';
	public $currency_position = '';

	// Separators
	public $decimal_separator = '';
	public $thousand_separator = '';

	/*****
	 *
	 * FORM SETTINGS
	 *
	 *****/

	/** PAYMENT OPTIONS **/

	/* one-time payment options */
	public $amount = '';

	// Statement descriptor setting
	public $statement_descriptor = '';

	/** STRIPE CHECKOUT DISPLAY **/

	public $company_name = '';
	public $item_description = '';
	public $image_url = '';
	public $enable_billing_address = '';

	/*****
	 *
	 * OTHER OPTIONS
	 *
	 *****/

	// Form ID
	public $id = 0;

	// The filter specific to this form. Allows us to make each form be completely customizable through filters independent of each other
	public $filter = '';

	// Custom fields
	public $custom_fields = array();

	/**
	 * Form constructor.
	 *
	 * @param $id int
	 */
	public function __construct( $id ) {

		// Setup the post object
		$this->set_post_object( $id );

		// Set our form specific filter to apply to each setting
		$this->filter = 'simpay_form_' . $this->id;

		// Setup the global settings tied to this form
		$this->set_global_settings();

		// Setup the post meta settings tied to this form
		$this->set_post_meta_settings();

		global $simpay_form;

		// Set global form object to this instance
		$simpay_form = $this;

		$this->maybe_register_hooks();

		do_action( 'simpay_form_loaded' );
	}

	/**
	 * Determine if hooks should be registered.
	 *
	 * Hooks get run once per form instance. See https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/617
	 *
	 */
	public function maybe_register_hooks() {

		global $simpay_displayed_form_ids;

		if( ! is_array( $simpay_displayed_form_ids ) ) {
			$simpay_displayed_form_ids = array();
		}

		// Collect any form IDs we've displayed already so we can avoid duplicate IDs
		if ( ! isset( $simpay_displayed_form_ids[ $this->id ] ) ) {
		    $this->register_hooks();

		    $simpay_displayed_form_ids[ $this->id ] = true;
		}

	}

	/**
	 * Add hooks and filters for this form instance.
	 *
	 * Hooks get run once per form instance. See https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/617
	 *
	 */
	public function register_hooks() {}

	/**
	 * Setup the post object for this form
	 *
	 * @param $form
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
	 */
	public function set_global_settings() {

		// Set all the global settings that have been saved here.
		// Doing this here allows us to make every setting filterable on a per-form basis

		// We have to use simpay_get_filtered() for these since this is the first time setting these values. That's why we can't use something like simpay_get_setting()
		// Basically, think of this as the construction of global $simpay_form, so anything that uses $simpay_form will not work because the global will still be null at this point.

		/** STRIPE KEYS **/

		/* Test mode options */
		$this->test_mode = simpay_get_filtered( 'test_mode', simpay_get_global_setting( 'test_mode' ), $this->id );

		$settings = get_option( 'simpay_settings_keys' );

		/* Test Keys */
		$this->test_secret_key      = isset( $settings['test_keys']['secret_key'] ) ? $settings['test_keys']['secret_key'] : '';
		$this->test_publishable_key = isset( $settings['test_keys']['publishable_key'] ) ? $settings['test_keys']['publishable_key'] : '';

		/* Live Keys */
		$this->live_secret_key      = isset( $settings['live_keys']['secret_key'] ) ? $settings['live_keys']['secret_key'] : '';
		$this->live_publishable_key = isset( $settings['live_keys']['publishable_key'] ) ? $settings['live_keys']['publishable_key'] : '';

		/* Final ambiguous keys */
		$this->secret_key      = simpay_get_filtered( 'secret_key', simpay_is_test_mode() ? $this->test_secret_key : $this->live_secret_key, $this->id );
		$this->publishable_key = simpay_get_filtered( 'publishable_key', simpay_is_test_mode() ? $this->test_publishable_key : $this->live_publishable_key, $this->id );
		$this->account_id      = simpay_get_filtered( 'account_id', $this->account_id, $this->id );

		/** GENERAL **/

		/* General */

		// Success Page
		$payment_success_page       = simpay_get_global_setting( 'success_page' );
		$this->payment_success_page = simpay_get_filtered( 'payment_success_page', $this->get_redirect_url( $payment_success_page ), $this->id );

		// Failure Page
		$payment_failure_page       = simpay_get_global_setting( 'failure_page' );
		$this->payment_failure_page = simpay_get_filtered( 'payment_failure_page', $this->get_redirect_url( $payment_failure_page, true ), $this->id );

		// Locale backwards compatibility.
		// Can't search for the global setting key because it exists in both options now.
		$keys   = get_option( 'simpay_settings_keys' );
		$locale = isset( $keys['locale']['locale'] ) ? $keys['locale']['locale'] : 'auto';

		$fallback = get_option( 'simpay_settings_general' );
		$fallback = isset( $fallback['general']['locale'] ) ? $fallback['general']['locale'] : 'auto';

		// Previously an empty value was English. Force that to `en` to avoid errors.
		if ( '' === $locale ) {
			$locale = 'en';
		}

		$this->locale = ! empty( $locale ) ? $locale : $fallback;

		$this->country     = simpay_get_filtered( 'country', simpay_get_global_setting( 'country' ), $this->id );

		// Stripe needs something, so default to US if settings haven't been saved.
		if ( ! $this->country ) {
			$this->country = 'US';
		}

		/* Currency Options */
		$this->currency          = simpay_get_filtered( 'currency', simpay_get_global_setting( 'currency' ), $this->id );
		$this->currency_position = simpay_get_filtered( 'currency_position', simpay_get_global_setting( 'currency_position' ), $this->id );

		// Decimal Separator
		$decimal_separator       = ( true === $this->set_bool_value( simpay_get_global_setting( 'separator' ) ) ? ',' : '.' );
		$this->decimal_separator = simpay_get_filtered( 'decimal_separator', $decimal_separator, $this->id );

		// Thousand separator
		$thousand_separator       = ( true === $this->set_bool_value( simpay_get_global_setting( 'separator' ) ) ? '.' : ',' );
		$this->thousand_separator = simpay_get_filtered( 'thousand_separator', $thousand_separator, $this->id );
	}

	/**
	 * Set the form settings options to the form attributes.
	 */
	public function set_post_meta_settings() {

		// Set all the form settings that have been saved here.
		// Doing this here allows us to make every setting filterable on a per-form

		// We have to use simpay_get_filtered() for these since this is the first time setting these values. That's why we can't use something like simpay_get_setting()
		// Basically, think of this as the construction of global $simpay_form, so anything that uses $simpay_form will not work because the global will still be null at this point.

		// Custom Fields sorted by order
		$this->custom_fields = $this->sort_fields( Custom_Fields::get_fields( $this->id ) );

		/** PAYMENT OPTIONS **/

		/* one-time payment options */
		$this->amount = simpay_unformat_currency( simpay_get_filtered( 'amount', simpay_get_saved_meta( $this->id, '_amount', simpay_global_minimum_amount() ), $this->id ) );

		// Statement descriptor
		$this->statement_descriptor = simpay_validate_statement_descriptor(
			simpay_get_filtered( 'statement_descriptor', '', $this->id )
		);

		/** STRIPE CHECKOUT DISPLAY **/

		$this->checkout_button_text    = simpay_get_filtered( 'checkout_button_text', simpay_get_saved_meta( $this->id, '_checkout_button_text', sprintf( esc_html__( 'Pay %s', 'stripe' ), '{{amount}}' ) ), $this->id );

		$this->company_name = simpay_get_filtered( 'company_name', simpay_get_saved_meta( $this->id, '_company_name' ), $this->id );

		$this->item_description = simpay_get_filtered( 'item_description', simpay_get_saved_meta( $this->id, '_item_description' ), $this->id );

		$this->image_url               = simpay_get_filtered( 'image_url', simpay_get_saved_meta( $this->id, '_image_url' ), $this->id );
		$this->enable_billing_address  = simpay_get_filtered( 'enable_billing_address', $this->set_bool_value( simpay_get_saved_meta( $this->id, '_enable_billing_address' ) ), $this->id );
	}

	/**
	 * Find the page by post ID and return the permalink
	 *
	 * @param $page_id
	 *
	 * @return false|string
	 */
	public function get_redirect_url( $page_id, $failure_page = false ) {

		// If we are getting success page then check the form settings first
		if ( ! $failure_page ) {
			$success_redirect_type = simpay_get_saved_meta( $this->id, '_success_redirect_type' );

			if ( 'page' === $success_redirect_type ) {
				$page_id = simpay_get_saved_meta( $this->id, '_success_redirect_page' );
			} elseif ( 'redirect' === $success_redirect_type ) {
				return esc_url( simpay_get_saved_meta( $this->id, '_success_redirect_url' ) );
			}
		}

		// Fallback for using default global setting and for getting the failure page URL
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
	 * Sort the custom fields by their order
	 *
	 * @param $arr
	 *
	 * @return array|string
	 */
	private function sort_fields( $arr ) {
		// If our array is empty then exit now
		if ( empty( $arr ) ) {
			return '';
		}
		$fields     = $arr;
		$new_fields = array();
		$order      = array();
		if ( is_array( $fields ) ) {
			foreach ( $fields as $key => $row ) {
				if ( is_array( $row ) ) {
					foreach ( $row as $k => $v ) {
						$order[] = isset( $v['order'] ) ? $v['order'] : 9999;
						$v['type']    = $key;
						$new_fields[] = $v;
					}
				}
			}
		}
		array_multisort( $order, SORT_ASC, $new_fields );
		return $new_fields;
	}

	/**
	 * Make a check on bool fields to set them as a boolean value instead of their saved value.
	 *
	 * @param        $option
	 * @param string $check
	 *
	 * @return bool
	 */
	private function set_bool_value( $option, $check = '' ) {

		if ( ! empty( $check ) ) {
			return ( $check === $option ? true : false );
		}

		return ( 'yes' === $option ? true : false );
	}

	/**
	 * Set all the script variables for the Stripe specific settings (the ones Stripe needs for the checkout form)
	 *
	 * @return array
	 */
	public function get_stripe_script_variables() {

		// Key is required so we always include it
		$strings['strings']['key'] = $this->publishable_key;

		// Redirect URLs.
		$strings['strings']['success_url'] = $this->payment_success_page;
		$strings['strings']['error_url']   = $this->payment_failure_page;

		// Boolean/dropdown options
		$bools = array(
			'bools' => array(),
		);

		if ( $this->enable_billing_address ) {
			$bools['bools']['billingAddress'] = true;
		}

		// Optional params if set in the settings only

		// Company name
		if ( ! empty( $this->company_name ) ) {
			$strings['strings']['name'] = $this->company_name;
		}

		// Image URL
		if ( ! empty( $this->image_url ) ) {
			$strings['strings']['image'] = $this->image_url;
		}

		// Locale
		if ( ! empty( $this->locale ) ) {
			$strings['strings']['locale'] = $this->locale;
		}

		// Country
		if ( ! empty( $this->country ) ) {
			$strings['strings']['country'] = $this->country;
		}

		// Currency
		if ( ! empty( $this->currency ) ) {
			$strings['strings']['currency'] = $this->currency;
		}

		// Item description
		if ( ! empty( $this->item_description ) ) {
			$strings['strings']['description'] = $this->item_description;
		}

		// Return as hookable data
		return apply_filters( 'simpay_stripe_script_variables', array_merge( $strings, $bools ) );
	}
}
