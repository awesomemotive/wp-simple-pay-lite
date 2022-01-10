<?php
/**
 * Routines: Prices API pricing schema.
 *
 * Migrates legacy Payment Form  "Amount Type" and "Subscription Type"
 * settings to an updated data schema that can be used with
 * Stripe Products and Prices.
 *
 * @package SimplePay\Core\Utils\Migrations
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\Utils\Migrations\Routines;

use SimplePay\Core\API;
use SimplePay\Core\Utils\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prices_API class.
 *
 * @since 4.1.0
 */
class Prices_API extends Migrations\Single_Migration {

	/**
	 * Migrates legacy "Amount Type" and "Subscription Type" settings to the
	 * updated data schema that can be used with Stripe's Products and Prices.
	 *
	 * Only adds migrated Prices to the Payment Forms's current Payment Mode.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
	 */
	public function run( $form ) {
		// Form is invalid, do nothing.
		if ( false === $form ) {
			return;
		}

		// Do nothing if the form's current mode has no API keys.
		$api_request_args = $form->get_api_request_args();

		if ( empty( $api_request_args['api_key'] ) ) {
			return;
		}

		try {
			// Attach the Product to the Payment Form.
			$product = $this->create_product( $form );

			update_post_meta(
				$form->id,
				$form->is_livemode()
					? '_simpay_product_live'
					: '_simpay_product_test',
				$product->id
			);

			// Attach the pricing data to the Payment Form.
			$prices = $this->migrate_prices( $form, $product );

			update_post_meta(
				$form->id,
				$form->is_livemode()
					? '_simpay_prices_live'
					: '_simpay_prices_test',
				$prices
			);

			update_post_meta(
				$form->id,
				$form->is_livemode()
					? '_simpay_prices_live_modified'
					: '_simpay_prices_test_modified',
				time()
			);

			$this->add_missing_custom_fields( $form );
		} catch ( \Exception $e ) {
			wp_die( $e->getMessage() );
		}
	}

	/**
	 * Creates a container Product in Stripe for the Payment Form's pricing.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
	 * @return \SimplePay\Vendor\Stripe\Product
	 */
	public function create_product( $form ) {
		$existing_product = get_post_meta(
			$form->id,
			$form->is_livemode()
				? '_simpay_product_live'
				: '_simpay_product_test',
			true
		);

		if ( ! empty( $existing_product ) ) {
			$product = API\Products\retrieve(
				$existing_product,
				$form->get_api_request_args()
			);

			return $product;
		}

		// Name.
		$name = ! empty( $form->company_name )
			? $form->company_name
			: get_bloginfo( 'name' );

		// https://github.com/wpsimplepay/wp-simple-pay-pro/issues/1598
		if ( empty( $name ) ) {
			$name = sprintf(
				__( 'WP Simple Pay - Form %d', 'stripe' ),
				$form->id
			);
		}

		$product_args['name'] = esc_html( $name );

		// Description. Optional.
		$description = ! empty( $form->item_description )
			? $form->item_description
			: get_bloginfo( 'tagline' );

		if ( ! empty( $description) ) {
			$product_args['description'] = $description;
		}

		$product = API\Products\create(
			$product_args,
			$form->get_api_request_args()
		);

		return $product;
	}

	/**
	 * Migrates existing "Amount Type" and "Subscription Type" settings to
	 * the Stripe Product and Prices pricing schema.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstracts\Form   $form Payment Form.
	 * @param \SimplePay\Vendor\Stripe\Product $product Stripe Product.
	 * @return array[] Price data.
	 */
	public function migrate_prices( $form, $product ) {
		if ( false === $product ) {
			throw new \Exception(
				/* translators: %s Payment Form ID. */
				__(
					'Unable to create Stripe Product container for %s.',
					'stripe'
				),
				$form->ID
			);
		}

		// Storage for the updated pricing schema.
		$prices = array_merge(
			$this->migrate_one_time_set( $form, $product ),
			$this->migrate_one_time_custom( $form, $product ),
			$this->migrate_single_set_subscription( $form, $product ),
			$this->migrate_single_custom_subscription( $form, $product ),
			$this->migrate_multi_subscription( $form, $product )
		);

		// Remove empty items.
		$prices = array_filter( $prices );

		// Ensure one item is marked as default.
		$defaults = wp_list_pluck( $prices, 'default' );
		if ( false === array_search( true, $defaults, true ) ) {
			$prices[0]['default'] = true;
		}

		return $prices;
	}

	/**
	 * Migrates legacy settings for "One Time Set" "Amount Type" Payment Forms.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form    $form Payment Form.
	 * @param \SimplePay\Vendor\Stripe\Product $product Stripe Product.
	 * @return array[] Price data.
	 */
	public function migrate_one_time_set( $form, $product ) {
		$prices   = array();
		$currency = strtolower( simpay_get_setting( 'currency', 'USD' ) );

		$amount_type       = get_post_meta( $form->id, '_amount_type', true );
		$subscription_type = get_post_meta(
			$form->id,
			'_subscription_type',
			true
		);

		if (
			(
				! empty( $amount_type ) &&
				'one_time_set' !== $amount_type
			) ||
			(
				! empty( $subscription_type ) &&
				'disabled' !== $subscription_type
			)
		) {
			return $prices;
		}

		// If using a predefined "Amounts" chooser, populate price data with those amounts.
		$predefined_amounts = $this->get_predefined_amounts( $form );

		// Return only the defined custom amounts.
		if ( ! empty( $predefined_amounts ) ) {
			return $this->migrate_predefined_amounts( $form, $product );
		}

		// Create a single defined price.
		$unit_amount = simpay_convert_amount_to_cents(
			get_post_meta( $form->id, '_amount', true )
		);

		$recurring_amount_toggle = $this->get_recurring_amount_toggle( $form );
		$can_recur_args          = $this->get_recurring_amount_toggle_args( $form );

		// A filter is being used to set a custom amount or currency.
		// Migrate to a custom amount.
		if (
			has_filter( 'simpay_form_' . $form->id . '_amount' ) ||
			has_filter( 'simpay_form_' . $form->id . '_currency' )
		) {
			add_post_meta(
				$form->id,
				'_simpay_has_legacy_filtered_custom_amount',
				true
			);

			$price = array(
				'id'              => 'simpay_' . wp_generate_uuid4(),
				'unit_amount'     => $unit_amount,
				'unit_amount_min' => $unit_amount,
				'currency'        => $currency,
				'default'         => true,
				'can_recur'       => false !== $recurring_amount_toggle,
			);

			if ( ! empty( $can_recur_args ) ) {
				$price['recurring'] = $can_recur_args;
			}

			return array( $price );
		}

		$price = API\Prices\create(
			array(
				'unit_amount' => $unit_amount,
				'currency'    => $currency,
				'product'     => $product->id,
			),
			$form->get_api_request_args()
		);

		$price = array(
			'id'        => $price->id,
			'default'   => true,
			'can_recur' => false !== $recurring_amount_toggle
		);

		if ( ! empty( $can_recur_args ) ) {
			$recurring_price = API\Prices\create(
				array(
					'unit_amount' => $unit_amount,
					'currency'    => $currency,
					'product'     => $product->id,
					'recurring'   => array(
						'interval'       => $can_recur_args['interval'],
						'interval_count' => $can_recur_args['interval_count'],
					)
				),
				$form->get_api_request_args()
			);

			$price['recurring'] = array(
				'id' => $recurring_price->id,
			);

			if ( isset( $can_recur_args['invoice_limit'] ) ) {
				$price['recurring']['invoice_limit'] =
					$can_recur_args['invoice_limit'];
			}
		}

		return array( $price );
	}

	/**
	 * Migrates legacy settings for "One Time Custom" "Amount Type" Payment Forms.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form    $form Payment Form.
	 * @param \SimplePay\Vendor\Stripe\Product $product Stripe Product.
	 * @return array[] Price data.
	 */
	public function migrate_one_time_custom( $form, $product ) {
		$prices   = array();
		$currency = strtolower( simpay_get_setting( 'currency', 'USD' ) );

		$amount_type       = get_post_meta( $form->id, '_amount_type', true );
		$subscription_type = get_post_meta(
			$form->id,
			'_subscription_type',
			true
		);

		if (
			'one_time_custom' !== $amount_type ||
			( ! empty( $subscription_type ) && 'disabled' !== $subscription_type )
		) {
			return $prices;
		}

		// If using a predefined "Amounts" chooser, populate price data with those amounts.
		$predefined_amounts = $this->get_predefined_amounts( $form );

		// Add converted predefined amounts to pricing data.
		if ( ! empty( $predefined_amounts ) ) {
			$prices = array_merge(
				$prices,
				$this->migrate_predefined_amounts( $form, $product )
			);
		}

		$currency_minimum = simpay_get_currency_minimum( $currency );

		$unit_amount = simpay_convert_amount_to_cents(
			get_post_meta( $form->id, '_custom_amount_default', true )
		);

		if ( empty( $unit_amount ) || $unit_amount < $currency_minimum ) {
			$unit_amount = $currency_minimum;
		}

		$unit_amount_min = simpay_convert_amount_to_cents(
			get_post_meta( $form->id, '_minimum_amount', true )
		);

		if ( empty( $unit_amount_min ) || $unit_amount_min < $currency_minimum ) {
			$unit_amount_min = $currency_minimum;
		}

		$recurring_amount_toggle = $this->get_recurring_amount_toggle( $form );
		$can_recur_args          = $this->get_recurring_amount_toggle_args( $form );

		$price = array(
			'id'              => 'simpay_' . wp_generate_uuid4(),
			'unit_amount'     => $unit_amount,
			'unit_amount_min' => $unit_amount_min,
			'currency'        => $currency,
			'default'         => empty( $predefined_amounts ),
			'can_recur'       => false !== $recurring_amount_toggle,
		);

		if ( ! empty( $can_recur_args ) ) {
			$price['recurring'] = $can_recur_args;
		}

		$label = get_post_meta( $form->id, '_custom_amount_label', true );

		if ( ! empty( $label ) ) {
			$price['label'] = $label;
		}

		$prices[] = $price;

		return $prices;
	}

	/**
	 * Migrates legacy settings for "Single Set" "Subscription Type" Payment Forms.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form    $form Payment Form.
	 * @param \SimplePay\Vendor\Stripe\Product $product Stripe Product.
	 * @return array[] Price data.
	 */
	public function migrate_single_set_subscription( $form, $product ) {
		$prices = array();

		$subscription_type = get_post_meta(
			$form->id,
			'_subscription_type',
			true
		);

		$subscription_custom = get_post_meta(
			$form->id,
			'_subscription_custom_amount',
			true
		);

		if ( ! (
			'single' === $subscription_type &&
			'disabled' === $subscription_custom
		) ) {
			return $prices;
		}

		$plan = get_post_meta(
			$form->id,
			$form->is_livemode() ? '_single_plan' : '_single_plan_test',
			true
		);

		if ( empty( $plan ) || 'empty' === $plan ) {
			$plan = get_post_meta( $form->id, '_single_plan', true );
		}

		if ( empty( $plan ) || 'empty' === $plan ) {
			return $prices;
		}

		$price = array(
			'id'      => $plan,
			'default' => true,
		);

		$recurring = array();

		// Attach "Invoice Limit" to existing Plan metadata if required.
		$invoice_limit = get_post_meta( $form->id, '_max_charges', true );

		if ( ! empty( $invoice_limit ) ) {
			$recurring['invoice_limit'] = (int) $invoice_limit;
		}

		$plan_obj = API\Plans\retrieve(
			$plan,
			$form->get_api_request_args()
		);

		// Attach "Trial Period Days" to pricing data if required.
		$trial_period_days = $plan_obj->trial_period_days;

		if ( $trial_period_days ) {
			$recurring['trial_period_days'] = $trial_period_days;
		}

		// Attach line items to pricing data if required.
		$setup_fee = get_post_meta( $form->id, '_setup_fee', true );

		if ( ! empty( $setup_fee ) ) {
			$setup_fee_amount = simpay_convert_amount_to_cents( $setup_fee );

			$line_items = array(
				array(
					'unit_amount' => $setup_fee_amount,
					'currency'    => $plan_obj->currency,
				),
			);

			$price['line_items'] = $line_items;
		}

		// Attach recurring data if needed.
		if ( ! empty( $recurring ) ) {
			$price['recurring'] = $recurring;
		}

		$price['can_recur'] = $this->get_recurring_amount_toggle( $form );

		$prices[] = $price;

		return $prices;
	}

	/**
	 * Migrates legacy settings for "Single Custom" "Subscription Type" Payment Forms.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form    $form Payment Form.
	 * @param \SimplePay\Vendor\Stripe\Product $product Stripe Product.
	 * @return array[] Price data.
	 */
	public function migrate_single_custom_subscription( $form, $product ) {
		$prices = array();

		$currency = strtolower(
			simpay_get_setting( 'currency', 'USD' )
		);

		$subscription_type = get_post_meta(
			$form->id,
			'_subscription_type',
			true
		);

		$subscription_custom = get_post_meta(
			$form->id,
			'_subscription_custom_amount',
			true
		);

		if ( ! (
			'single' === $subscription_type &&
			'enabled' === $subscription_custom
		) ) {
			return $prices;
		}

		$unit_amount = simpay_convert_amount_to_cents(
			simpay_unformat_currency(
				get_post_meta( $form->id, '_multi_plan_default_amount', true )
			)
		);

		if ( 0 === $unit_amount ) {
			$unit_amount = simpay_get_currency_minimum( $currency );
		}

		$unit_amount_min = simpay_convert_amount_to_cents(
			simpay_unformat_currency(
				get_post_meta( $form->id, '_multi_plan_minimum_amount', true )
			)
		);

		if ( 0 === $unit_amount_min ) {
			$unit_amount_min = simpay_get_currency_minimum( $currency );
		}

		if ( $unit_amount_min > $unit_amount ) {
			$unit_amount = $unit_amount_min;
		}

		$price = array(
			'id'              => 'simpay_' . wp_generate_uuid4(),
			'unit_amount'     => $unit_amount,
			'unit_amount_min' => $unit_amount_min,
			'currency'        => $currency,
			'default'         => true,
			'can_recur'       => false,
		);

		// Custom label.
		$label = get_post_meta( $form->id, '_custom_plan_label', true );

		if ( ! empty( $label ) ) {
			$price['label'] = $label;
		}

		// Recurring.
		$interval       = get_post_meta( $form->id, '_plan_frequency', true );
		$interval_count = get_post_meta( $form->id, '_plan_interval', true );

		$recurring = array(
			'interval'       => $interval,
			'interval_count' => (int) $interval_count,
		);

		// Attach "Invoice Limit" if required.
		$invoice_limit = get_post_meta( $form->id, '_max_charges', true );

		if ( ! empty( $invoice_limit ) ) {
			$recurring['invoice_limit'] = (int) $invoice_limit;
		}

		// Attach line items to pricing data if required.
		$setup_fee = get_post_meta( $form->id, '_setup_fee', true );

		if ( ! empty( $setup_fee ) ) {
			$setup_fee_amount = simpay_convert_amount_to_cents( $setup_fee );

			$line_items = array(
				array(
					'unit_amount' => $setup_fee_amount,
					'currency'    => $currency,
				),
			);

			$price['line_items'] = $line_items;
		}

		// Attach recurring data if needed.
		if ( ! empty( $recurring ) ) {
			$price['recurring'] = $recurring;
		}

		$prices[] = $price;

		return $prices;
	}

	/**
	 * Migrates legacy settings for "User Select (no custom)"
	 * "Subscription Type" Payment Forms.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form    $form Payment Form.
	 * @param \SimplePay\Vendor\Stripe\Product $product Stripe Product.
	 * @return array[] Price data.
	 */
	public function migrate_multi_subscription( $form, $product ) {
		$prices = array();

		$currency = strtolower(
			simpay_get_setting( 'currency', 'USD' )
		);

		$subscription_type = get_post_meta(
			$form->id,
			'_subscription_type',
			true
		);

		if ( 'user' !== $subscription_type ) {
			return $prices;
		}

		// Retrieve existing Plans.
		$plans = get_post_meta(
			$form->id,
			$form->is_livemode() ? '_multi_plan' : '_multi_plan_test',
			true
		);

		if ( empty( $plans ) ) {
			$plans = get_post_meta( $form->id, '_multi_plan', true );
		}

		if ( empty( $plans ) ) {
			return $prices;
		}

		$default_plan = get_post_meta(
			$form->id,
			$form->is_livemode()
				? '_multi_plan_default_value'
				: '_multi_plan_default_value_test',
			true
		);

		if ( empty( $default_plan ) ) {
			$default_plan = get_post_meta(
				$form->id,
				'_multi_plan_default_value',
				true
			);
		}

		$setup_fee = get_post_meta( $form->id, '_setup_fee', true );

		foreach ( $plans as $plan ) {
			$price = array(
				'id'        => $plan['select_plan'],
				'default'   => $plan['select_plan'] === $default_plan,
				'can_recur' => false,
			);

			// Add custom label if set.
			$custom_label = isset( $plan['custom_label'] )
				? $plan['custom_label']
				: '';

			if ( ! empty( $custom_label ) ) {
				$price['label'] = $plan['custom_label'];
			}

			$recurring  = array();
			$line_items = array();

			$plan_obj = API\Plans\retrieve(
				$plan['select_plan'],
				$form->get_api_request_args()
			);

			// Attach "Invoice Limit" to existing Plan metadata if required.
			$invoice_limit = isset( $plan['max_charges'] )
				? $plan['max_charges']
				: 0;

			if ( ! empty( $invoice_limit ) ) {
				$recurring['invoice_limit'] = (int) $invoice_limit;
			}

			// Attach "Trial Period Days" to pricing data if required.
			$trial_period_days = $plan_obj->trial_period_days;

			if ( $trial_period_days ) {
				$recurring['trial_period_days'] = $trial_period_days;
			}

			// Add Subscription Setup fee if set.
			if ( ! empty( $setup_fee ) ) {
				$setup_fee_amount = simpay_convert_amount_to_cents( $setup_fee );

				$line_items[] = array(
					'unit_amount' => $setup_fee_amount,
					'currency'    => $currency,
				);
			}

			// Add Plan Setup fee if set.
			$plan_setup_fee = isset( $plan['setup_fee'] )
				? $plan['setup_fee']
				: 0;

			if ( ! empty( $plan_setup_fee ) ) {
				$plan_setup_fee_amount = simpay_convert_amount_to_cents(
					$plan_setup_fee
				);

				$line_items[] = array(
					'unit_amount' => $plan_setup_fee_amount,
					'currency'    => $currency,
				);
			}

			// Attach recurring data if needed.
			if ( ! empty( $recurring ) ) {
				$price['recurring'] = $recurring;
			}

			// Attach lien items if needed.
			if ( ! empty( $line_items ) ){
				$price['line_items'] = $line_items;
			}

			$prices[] = $price;
		}

		// Custom amount.
		$subscription_custom = get_post_meta(
			$form->id,
			'_subscription_custom_amount',
			true
		);

		if ( 'enabled' === $subscription_custom ) {
			$unit_amount = simpay_convert_amount_to_cents(
				simpay_unformat_currency(
					get_post_meta( $form->id, '_multi_plan_default_amount', true )
				)
			);

			if ( 0 === $unit_amount ) {
				$unit_amount = simpay_get_currency_minimum( $currency );
			}

			$unit_amount_min = simpay_convert_amount_to_cents(
				simpay_unformat_currency(
					get_post_meta( $form->id, '_multi_plan_minimum_amount', true )
				)
			);

			if ( 0 === $unit_amount_min ) {
				$unit_amount_min = simpay_get_currency_minimum( $currency );
			}

			if ( $unit_amount_min > $unit_amount ) {
				$unit_amount = $unit_amount_min;
			}

			$custom = array(
				'id'              => 'simpay_' . wp_generate_uuid4(),
				'unit_amount'     => $unit_amount,
				'unit_amount_min' => $unit_amount_min,
				'currency'        => $currency,
				'default'         => false,
				'can_recur'       => false,
			);

			// Add custom label if set.
			$label = get_post_meta( $form->id, '_custom_plan_label', true );

			if ( ! empty( $label ) ) {
				$custom['label'] = $label;
			}

			$interval       = get_post_meta( $form->id, '_plan_frequency', true );
			$interval_count = get_post_meta( $form->id, '_plan_interval', true );

			$recurring = array(
				'interval'       => $interval,
				'interval_count' => (int) $interval_count,
			);

			// Attach "Invoice Limit" if required.
			$invoice_limit = get_post_meta( $form->id, '_max_charges', true );

			if ( ! empty( $invoice_limit ) ) {
				$recurring['invoice_limit'] = (int) $invoice_limit;
			}

			$custom['recurring'] = $recurring;

			// Attach line items to pricing data if required.
			$setup_fee = get_post_meta( $form->id, '_setup_fee', true );

			if ( ! empty( $setup_fee ) ) {
				$setup_fee_amount = simpay_convert_amount_to_cents( $setup_fee );

				$line_items = array(
					array(
						'unit_amount' => $setup_fee_amount,
						'currency'    => $currency,
					),
				);

				$custom['line_items'] = $line_items;
			}

			$prices[] = $custom;
		}

		return $prices;
	}

	/**
	 * Migrates legacy settings for "Amount" custom fields.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form    $form Payment Form.
	 * @param \SimplePay\Vendor\Stripe\Product $product Stripe Product.
	 * @return array[] Price data.
	 */
	public function migrate_predefined_amounts( $form, $product ) {
		$predefined_amounts = $this->get_predefined_amounts( $form );
		$currency           = strtolower(
			simpay_get_setting( 'currency', 'USD' )
		);

		$filtered = (
			has_filter( 'simpay_form_' . $form->id . '_amount' ) ||
			has_filter( 'simpay_form_' . $form->id . '_currency' )
		);

		$recurring_amount_toggle = $this->get_recurring_amount_toggle( $form );
		$can_recur_args          = $this->get_recurring_amount_toggle_args( $form );

		foreach ( $predefined_amounts as $amount ) {
			$unit_amount = $amount['unit_amount'];

			// A filter is being used to set a custom amount or currency.
			// Migrate to a custom amount.
			if ( $filtered ) {
				$price = array(
					'id'              => 'simpay_' . wp_generate_uuid4(),
					'unit_amount'     => $unit_amount,
					'unit_amount_min' => $unit_amount,
					'currency'        => $currency,
					'default'         => true,
					'can_recur'       => false !== $recurring_amount_toggle,
				);

				if ( ! empty( $can_recur_args ) ) {
					$price['recurring'] = $can_recur_args;
				}

				$prices[] = $price;
			} else {
				$price = API\Prices\create(
					array(
						'unit_amount' => $unit_amount,
						'currency'    => $currency,
						'product'     => $product->id,
					),
					$form->get_api_request_args()
				);

				$price = array(
					'id'        => $price->id,
					'default'   => $amount['default'],
					'can_recur' => false !== $recurring_amount_toggle,
				);

				if ( ! empty( $can_recur_args ) ) {
					$recurring_price = API\Prices\create(
						array(
							'unit_amount' => $unit_amount,
							'currency'    => $currency,
							'product'     => $product->id,
							'recurring'   => array(
								'interval'       => $can_recur_args['interval'],
								'interval_count' => $can_recur_args['interval_count'],
							)
						),
						$form->get_api_request_args()
					);

					$price['recurring'] = array(
						'id' => $recurring_price->id,
					);

					if ( isset( $can_recur_args['invoice_limit'] ) ) {
						$price['recurring']['invoice_limit'] =
							$can_recur_args['invoice_limit'];
					}
				}

				$prices[] = $price;
			}
		}

		// If these prices were filtered and converted to custom amounts make
		// a note of that here, so we can hide the Custom Amount field on the frontend.
		if ( true === $filtered ) {
			add_post_meta(
				$form->id,
				'_simpay_has_legacy_filtered_custom_amount',
				true
			);
		}

		return $prices;
	}

	/**
	 * Finds and converts "Amount" custom field data for use with the Prices schema.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form $form Payment Form.
	 * @return array[] Price data.
	 */
	public function get_predefined_amounts( $form ) {
		$custom_fields = get_post_meta(
			$form->id,
			'_custom_fields',
			true
		);

		if ( empty( $custom_fields ) ) {
			return array();
		}

		$default        = false;
		$amounts        = array();
		$_custom_fields = array();

		// Flatten fields.
		foreach ( $custom_fields as $type => $fields ) {
			foreach ( $fields as $field ) {
				$_custom_fields[] = $field;
			}
		}

		foreach ( $_custom_fields as $field ) {
			if (
				! isset( $field['amount_quantity'] ) ||
				'amount' !== $field['amount_quantity']
			) {
				continue;
			}

			if ( ! is_array( $field['amounts'] ) ) {
				$amounts = array_map(
					'trim',
					explode( ',', $field['amounts'] )
				);
			} else {
				$amounts = $field['amounts'];
			}

			$default = $field['default'];
		}

		// Convert to base decimal and set default.
		return array_map(
			function( $amount ) use( $default ) {
				return array(
					'unit_amount' => simpay_convert_amount_to_cents(
						$amount
					),
					'default'     => $default === $amount,
				);
			},
			$amounts
		);
	}

	/**
	 * Determines if the "Recurring Amount Toggle" field is being used.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form $form Payment Form.
	 * @return bool
	 */
	public function get_recurring_amount_toggle( $form ) {
		$custom_fields = get_post_meta( $form->id, '_custom_fields', true );

		if ( empty( $custom_fields ) ) {
			return false;
		}

		return isset( $custom_fields['recurring_amount_toggle'] )
			? current( $custom_fields['recurring_amount_toggle'] )
			: false;
	}

	/**
	 * Returns the settings for the "Recurring Amount Toggle" field.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form $form Payment Form.
	 * @return array
	 */
	public function get_recurring_amount_toggle_args( $form ) {
		$recurring_amount_toggle = $this->get_recurring_amount_toggle( $form );
		$can_recur_args          = array();

		if ( false === $recurring_amount_toggle ) {
			return $can_recur_args;
		}

		if ( ! empty( $recurring_amount_toggle['plan_frequency'] ) ) {
			$can_recur_args['interval'] =
				$recurring_amount_toggle['plan_frequency'];
		}

		if ( ! empty( $recurring_amount_toggle['plan_interval'] ) ) {
			$can_recur_args['interval_count'] =
				(int) $recurring_amount_toggle['plan_interval'];
		}

		if ( ! empty( $recurring_amount_toggle['max_charges'] ) ) {
			$can_recur_args['invoice_limit'] =
				(int) $recurring_amount_toggle['max_charges'];
		}

		return $can_recur_args;
	}

	/**
	 * Adjust custom fields based on new prices.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstract\Form $form Payment Form.
	 */
	public function add_missing_custom_fields( $form ) {
		$custom_fields = get_post_meta( $form->id, '_custom_fields', true );

		if ( empty( $custom_fields ) ) {
			$custom_fields = array();
		}

		$form_display_type = get_post_meta(
			$form->id,
			'_form_display_type',
			'stripe_checkout'
		);

		// Set "Plan Select" display type.
		$legacy_display_type = get_post_meta(
			$form->id,
			'_multi_plan_display',
			true
		);

		$display_type = ! empty( $legacy_display_type )
			? $legacy_display_type
			: 'radio';

		$custom_fields = simpay_payment_form_add_missing_custom_fields(
			$custom_fields,
			$form->id,
			$form_display_type
		);

		// Find out if Predefined Amounts were used, and of which field type.
		foreach ( $custom_fields as $type => $fields ) {
			foreach ( $fields as $k => $field ) {
				$_custom_fields[ $type ] = array_merge(
					$field,
					array(
						'key' => $k,
					)
				);
			}
		}

		foreach ( $_custom_fields as $type => $field ) {
			if (
				! isset( $field['amount_quantity'] ) ||
				'amount' !== $field['amount_quantity']
			) {
				continue;
			}

			// Find the type to migrate to Price Selector.
			$display_type = $type;

			// Remove custom field that is no longer used.
			unset( $custom_fields[ $type ][ $field['key'] ] );
		}

		$custom_fields['plan_select'][0]['display_type'] = $display_type;

		// A filter is being used to set a custom amount or currency.
		// Migrate to a custom amount.
		if (
			has_filter( 'simpay_form_' . $form->id . '_amount' ) ||
			has_filter( 'simpay_form_' . $form->id . '_currency' )
		) {
			$custom_fields['custom_amount'][0] = array(
				'prefill_amount' => true,
			);
		}

		update_post_meta( $form->id, '_custom_fields', $custom_fields );
	}

	/**
	 * Determines if the migration has been run by looking for price options via
	 * the new storage keys.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
	 */
	public function is_complete( $form ) {
		// Legacy information doesn't exist, completed.
		$legacy_amount = get_post_meta( $form->id, '_amount', true );

		if ( empty( $legacy_amount ) ) {
			return true;
		}

		// Prices API exists in one mode, completed.
		$test_prices = get_post_meta( $form->id, '_simpay_prices_test', true );
		$live_prices = get_post_meta( $form->id, '_simpay_prices_live', true );

		return ( ! empty( $test_prices ) || ! empty( $live_prices ) );
	}

}
