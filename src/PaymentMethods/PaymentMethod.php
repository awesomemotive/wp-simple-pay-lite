<?php
/**
 * Payment Methods: Payment Method
 *
 * @package SimplePay\Core\PaymentMethods
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\PaymentMethods;

use SimplePay\Core\i18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment_Method class.
 *
 * @since 3.8.0
 */
class PaymentMethod {

	/**
	 * Payment Method ID.
	 *
	 * @since 3.8.0
	 * @var string
	 */
	public $id;

	/**
	 * Payment Method name.
	 *
	 * @since 3.8.0
	 * @var string
	 */
	public $name;

	/**
	 * Payment Method nicename.
	 *
	 * @since 3.9.0
	 * @var string
	 */
	public $nicename;

	/**
	 * Payment method license level availability.
	 *
	 * @since 4.4.4
	 * @var string[]
	 */
	public $licenses;

	/**
	 * Payment Method flow.
	 *
	 * @since 3.8.0
	 * @var string
	 */
	public $flow;

	/**
	 * Payment Method scope.
	 *
	 * @since 4.2.0
	 * @var string
	 */
	public $scope;

	/**
	 * Payment Method country availability.
	 *
	 * @since 3.8.0
	 * @var string[]
	 */
	public $countries;

	/**
	 * Payment Method currency support.
	 *
	 * @since 3.8.0
	 * @var string[]
	 */
	public $currencies;

	/**
	 * Payment Method recurring support.
	 *
	 * @since 3.8.0
	 * @var bool
	 */
	public $recurring;

	/**
	 * Payment Method Stripe Checkout support.
	 *
	 * @since 3.8.0
	 * @var bool|bool[]
	 */
	public $stripe_checkout;

	/**
	 * Payment Method "buy now pay later" support.
	 *
	 * @since 4.5.0
	 * @var bool|bool[]
	 */
	public $bnpl;

	/**
	 * Internal (wpsimplepay.com/docs) documentation URL.
	 *
	 * @since 3.9.0
	 * @var string
	 */
	public $internal_docs;

	/**
	 * External (stripe.com) documentation URL.
	 *
	 * @since 3.9.0
	 * @var string
	 */
	public $external_docs;

	/**
	 * Icon.
	 *
	 * Dashicon slug or full SVG element.
	 *
	 * @since 4.2.0
	 *
	 * @var string
	 */
	public $icon;

	/**
	 * Icon (small)
	 *
	 * Dashicon slug or full SVG element.
	 *
	 * @since 4.4.4
	 *
	 * @var string
	 */
	public $icon_sm;

	/**
	 * Configuration provided by a payment form.
	 *
	 * @since 4.4.7
	 *
	 * @var array<string, mixed>
	 */
	public $config;

	/**
	 * Constructs the Payment Method.
	 *
	 * @since 3.8.0
	 *
	 * @param array<string, mixed> $args {
	 *   Payment Method configuration.
	 *
	 *   @type string $id              Payment Method identifier.
	 *   @type string $name            Payment Method name.
	 *   @type string $nicename        Payment Method "nice name" for display.
	 *   @type string[] $licenses      Payment Method license level availability.
	 *   @type string $flow            Payment Method flow. Accepts `none`, `redirect`, `receiver`. Default `none`.
	 *   @type string $scope           Payment Method scope. Accepts `standard` or `popular`. Default `standard`.
	 *   @type string[] $countries     Payment Method country availability. Default all countries.
	 *   @type string[] $currencies    Payment Method available currencies. Default all currencies.
	 *   @type bool   $recurring       Payment Method recurring support. Default false.
	 *   @type bool   $stripe_checkout Payment Method Stripe Checkout support. Default false.
	 *   @type bool   $bnpl            Payment Method "buy now pay later" support. Default false.
	 *   @type string $external_docs   Payment Method external documentation URL. Default empty.
	 *   @type string $internal_docs   Payment Method internal documentation URL. Default empty.
	 *   @type string $icon            Payment Method icon. Default empty.
	 *   @type string $icon_sm         Payment Method icon (small). Default Payment Method icon.
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'id'              => '',
			'name'            => '',
			'nicename'        => '',
			'licenses'        => array(),
			'flow'            => 'none',
			'scope'           => 'standard',
			'countries'       => array_map(
				'strtolower',
				array_keys( i18n\get_stripe_countries() )
			),
			'currencies'      => array_map(
				'strtolower',
				array_keys( i18n\get_stripe_currencies() )
			),
			'recurring'       => false,
			'stripe_checkout' => false,
			'bnpl'            => false,
			'internal_docs'   => '',
			'external_docs'   => '',
			'icon'            => '',
			'icon_sm'         => '',
			'config'          => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$this->id = sanitize_text_field( $args['id'] );

		$this->name     = sanitize_text_field( $args['name'] );
		$this->nicename = isset( $args['nicename'] )
			? sanitize_text_field( $args['nicename'] )
			: $this->name;

		$this->licenses = array_map( 'sanitize_text_field', $args['licenses'] );

		if ( in_array( $args['flow'], array( 'none', 'redirect', 'receiver' ), true ) ) {
			$this->flow = sanitize_text_field( $args['flow'] );
		} else {
			$this->flow = 'none';
		}

		if ( in_array( $args['scope'], array( 'standard', 'popular' ), true ) ) {
			$this->scope = sanitize_text_field( $args['scope'] );
		} else {
			$this->scope = 'standard';
		}

		if ( is_array( $args['countries'] ) ) {
			$this->countries = array_map( 'sanitize_text_field', $args['countries'] );
			$this->countries = array_map(
				function ( $country ) {
					return is_string( $country ) ? strtolower( $country ) : $country;
				},
				$this->countries
			);
		}

		if ( is_array( $args['currencies'] ) ) {
			$this->currencies = array_map( 'sanitize_text_field', $args['currencies'] );
			$this->currencies = array_map(
				function ( $currency ) {
					return is_string( $currency ) ? strtolower( $currency ) : $currency;
				},
				$this->currencies
			);
		}

		$this->recurring = (bool) $args['recurring'];

		$this->stripe_checkout = is_array( $args['stripe_checkout'] )
			? array_map( 'boolval', $args['stripe_checkout'] )
			: (bool) $args['stripe_checkout'];

		$this->bnpl = (bool) $args['bnpl'];

		if ( ! empty( $args['internal_docs'] ) ) {
			$this->internal_docs = esc_url( $args['internal_docs'] );
		}

		if ( ! empty( $args['external_docs'] ) ) {
			$this->external_docs = esc_url( $args['external_docs'] );
		}

		if ( ! empty( $args['icon'] ) ) {
			$svg_kses = array(
				'svg'  => array(
					'width'   => true,
					'height'  => true,
					'viewbox' => true,
					'xmlns'   => true,
				),
				'path' => array(
					'fill'      => true,
					'fill-rule' => true,
					'd'         => true,
					'transform' => true,
				),
				'g'    => array(
					'fill'      => true,
					'fill-rule' => true,
					'd'         => true,
					'transform' => true,
				),
			);

			// Ensure a full SVG element is used.
			if ( '<svg' === substr( strtolower( $args['icon'] ), 0, 4 ) ) {
				$this->icon = wp_kses( $args['icon'], $svg_kses );
			}

			// Set a small icon, if available.
			if (
				isset( $args['icon_sm'] ) &&
				'<svg' === substr( strtolower( $args['icon_sm'] ), 0, 4 )
			) {
				$this->icon_sm = wp_kses( $args['icon_sm'], $svg_kses );
			} else {
				$this->icon_sm = $this->icon;
			}
		}

		// Set config.
		// @todo Validate individually.
		if ( isset( $args['config'] ) && is_array( $args['config'] ) ) {
			$this->config = $args['config'];
		}
	}

	/**
	 * Determines if the Payment Method is available to use.
	 *
	 * @since 3.8.0
	 *
	 * @return bool True if the Payment Method can be used, otherwise false.
	 */
	public function is_available() {
		$is_available = true;

		if ( false === $this->is_country_supported() ) {
			$is_available = false;
		}

		/**
		 * Filters if the Payment Method is available to use.
		 *
		 * @since 3.8.0
		 *
		 * @param bool                                          $is_available   If the Payment Method is available to use.
		 * @param \SimplePay\Core\PaymentMethods\PaymentMethod $payment_method Payment Method.
		 */
		$is_available = apply_filters(
			'simpay_payment_method_is_available',
			$is_available,
			$this
		);

		return $is_available;
	}

	/**
	 * Determines if the Payment Method's available currencies
	 * include the global currency setting.
	 *
	 * @since 3.8.0
	 * @since 4.1.0 Remove global fallback.
	 *
	 * @param string $currency Optional. Currency to check support for. Default site currency.
	 * @return bool True if the site's currency is supported by the Payment Method, otherwise false.
	 */
	public function is_currency_supported( $currency = '' ) {
		if ( empty( $currency ) ) {
			$currency = simpay_get_setting( 'currency', 'usd' );
		}

		$currencies = $this->currencies;

		return in_array( is_string( $currency ) ? strtolower( $currency ) : $currency, $currencies, true );
	}

	/**
	 * Determines if the Payment Method supports a specific country.
	 *
	 * @since 4.2.0
	 *
	 * @param string $country Optional. Country to check support for. Default site country.
	 * @return bool True if the site's country is supported by the Payment Method, otherwise false.
	 */
	public function is_country_supported( $country = '' ) {
		if ( empty( $country ) ) {
			$country = simpay_get_setting( 'account_country', 'us' );
		}

		$countries = $this->countries;

		return in_array( is_string( $country ) ? strtolower( $country ) : $country, $countries, true );
	}

	/**
	 * Determines if the Payment Method is supported by the current license level.
	 *
	 * @since 4.4.4
	 *
	 * @param 'personal'|'plus'|'professional'|'ultimate'|'elite' $license_level License level to check support for.
	 * @return bool
	 */
	public function is_license_supported( $license_level ) {
		return in_array( $license_level, $this->licenses, true );
	}

	/**
	 * Returns an array of public payment method data to use in JSON.
	 *
	 * @since 4.4.4
	 *
	 * @return array<string, mixed>
	 */
	public function to_array_json() {
		$data = clone $this;

		unset( $data->external_docs );
		unset( $data->internal_docs );
		unset( $data->icon );
		unset( $data->icon_sm );

		return (array) $data;
	}

	/**
	 * Returns data to use inside payment form settings.
	 *
	 * @since 4.4.5
	 *
	 * @return \SimplePay\Core\PaymentMethods\PaymentMethod
	 */
	public function get_data_for_payment_form() {
		$data = clone $this;

		switch ( $data->id ) {
			case 'ach-debit':
				$data->id = 'us_bank_account';
				break;
			default:
				$data->id = str_replace( '-', '_', $data->id );
		}

		unset( $data->external_docs );
		unset( $data->internal_docs );
		unset( $data->icon );
		unset( $data->icon_sm );

		return $data;
	}
}
