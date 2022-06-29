<?php
/**
 * Payment Form: Price Option
 *
 * @package SimplePay\Core\PaymentForm
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\PaymentForm;

use SimplePay\Core\API;
use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PriceOption class.
 *
 * @since 4.1.0
 */
class PriceOption {

	/**
	 * Parent Payment Form.
	 *
	 * @since 4.1.0
	 * @var \SimplePay\Core\Abstracts\Form
	 */
	public $form;

	/**
	 * Parent Payment Form Product ID.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public $product_id;

	/**
	 * Optional. Stripe Price or Plan (legacy) ID.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public $id;

	/**
	 * Determines if the option should be considered the default selection.
	 *
	 * @since 4.1.0
	 * @var bool
	 */
	public $default;

	/**
	 * Optional. User-set label representing the PriceOption.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public $label;

	/**
	 * Three-letter ISO currency code, in lowercase.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public $currency;

	/**
	 * Currency symbol.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public $currency_symbol;

	/**
	 * Zero decimal currency.
	 *
	 * @since 4.1.0
	 * @var bool
	 */
	public $is_zero_decimal;

	/**
	 * Unit amount in the currency's lowest possible unit.
	 *
	 * @since 4.1.0
	 * @var int
	 */
	public $unit_amount;

	/**
	 * Optional. Unit amount minimum in the currency's lowest possible unit.
	 *
	 * @since 4.1.0
	 * @var int
	 */
	public $unit_amount_min;

	/**
	 * Determines if a one-time amount can optionally be purchased as a subscription.
	 *
	 * @since 4.1.0
	 * @var bool
	 */
	public $can_recur;

	/**
	 * Optional. Determines if a one-time amount can optionally be purchased
	 * as a subscription.
	 *
	 * @since 4.1.0
	 * @var array {
	 *   @type string $id                Stripe Price ID when "Recurring amount toggle"
	 *                                   is enabled and custom amount is disabled.
	 *                                   and "Custom amount" is disabled.
	 *   @type string $interval          Billing frequency. `year`, `month`, `week`, or `day`.
	 *   @type int    $interval_count    The number of intervals between Subscription
	 *                                   billings.
	 *   @type int    $invoice_limit     Optional. Maximum number of invoices to complete
	 *                                   before cancelling the Subscription.
	 *   @type int    $trial_period_days Optional. The number of trial period days before the
	 *                                   customer is charged for the first time.
	 * }
	 */
	public $recurring;

	/**
	 * Optional. Additional line items.
	 *
	 * @since 4.1.0
	 * @var array {
	 *   @type string $id          Line item ID.
	 *   @type stirng $name        Line item name.
	 *   @type int    $unit_amount Line item amount.
	 *   @type string $currency    Line item currency.
	 * }
	 */
	public $line_items;

	/**
	 * Optional. Stripe Price or Plan (legacy) for legacy filter usage.
	 *
	 * @since 4.1.0
	 * @var \SimplePay\Vendor\Stripe\Price|\SimplePay\Vendor\Stripe\Plan
	 */
	public $__unstable_stripe_object;

	/**
	 * Constructs a PriceOption.
	 *
	 * @since 4.1.0
	 *
	 * @param array                         $price_data {
	 *                           Price data.
	 *
	 *   @type int    $id              Optional. Stripe Price ID if using a defined amount.
	 *   @type bool   $default         Determines if the price should be preselected
	 *                                 when output.
	 *   @type string $label           A label to use when output.
	 *   @type string $currency        Three-letter ISO currency code, in lowercase.
	 *   @type int    $unit_amount     A positive integer in cents representing
	 *                                 how much to charge.
	 *   @type int    $unit_amount_min Optional. A positive integer in cents
	 *                                 representing the minimum acceptable amount
	 *                                 that can be charged.
	 *   @type bool  $can_recur        If the one-time amount can optionally recur.
	 *   @type array $recurring {
	 *     Optional. Recurring components of the price.
	 *
	 *     @type string $id                Stripe Price ID when "Recurring amount toggle"
	 *                                     is enabled and custom amount is disabled.
	 *                                     and "Custom amount" is disabled.
	 *     @type string $interval          Billing frequency. `year`, `month`, `week`, or `day`.
	 *     @type int    $interval_count    The number of intervals between Subscription
	 *                                     billings.
	 *     @type int    $invoice_limit     Optional. Maximum number of invoices to complete
	 *                                     before cancelling the Subscription.
	 *     @type int    $trial_period_days Optional. The number of trial period days before the
	 *                                     customer is charged for the first time.
	 *   }
	 *   @type array[] $line_items {
	 *     Optional. Additional line items.
	 *
	 *     @type int    $unit_amount Line item amount.
	 *     @type string $currency    Line item currency.
	 *   }
	 * }
	 * @param \SimplePay\Core\Abstract\Form $form Payment Form.
	 */
	public function __construct( $price_data, $form ) {
		if ( false === $form ) {
			throw new Exception(
				__(
					'Unable to create PriceOption. Invalid form.',
					'stripe'
				)
			);
		}

		// Attach Payment Form.
		$this->form = $form;

		// Attach parent Product.
		$product_key = true === $form->is_livemode()
			? '_simpay_product_live'
			: '_simpay_product_test';

		$product = get_post_meta( $form->id, $product_key, true );

		$this->product_id = $product;

		// Attach price data.
		if ( isset( $price_data['label'] ) ) {
			$this->label = $price_data['label'];
		}

		$this->default = isset( $price_data['default'] ) &&
			true === $price_data['default'];

		$this->can_recur = isset( $price_data['can_recur'] ) &&
			true === $price_data['can_recur'];

		$this->id = isset( $price_data['id'] )
			? $price_data['id']
			: 'simpay_' . wp_generate_uuid4();

		// Predefined amount.
		if ( $this->is_defined_amount() ) {
			$price_obj = API\Prices\retrieve(
				$price_data['id'],
				$this->form->get_api_request_args(),
				array(
					'cached' => true,
				)
			);

			// Store full object for legacy filters.
			$this->__unstable_stripe_object = $price_obj;

			// Base currency and amount.
			$this->currency = $price_obj->currency;

			$this->unit_amount = $price_obj->unit_amount;

			// Recurring information.
			if ( 'recurring' === $price_obj->type ) {
				$this->recurring = array();

				// Interval.
				$this->recurring['interval'] =
					$price_obj->recurring->interval;

				$this->recurring['interval_count'] =
					$price_obj->recurring->interval_count;

				// Fill trial data from legacy plans.
				if ( 'plan' === $price_obj->object ) {
					if (
						$price_obj->trial_period_days &&
						$price_obj->trial_period_days !== $recurring_data['trial_period_days']
					) {
						$this->recurring['trial_period_days'] =
							$price_obj->trial_period_days;
					}
				}
			}

			// Custom amount.
		} else {
			$this->currency = 'usd';

			if ( isset( $price_data['currency'] ) ) {
				$this->currency = $price_data['currency'];
			}

			if ( isset( $price_data['unit_amount'] ) ) {
				$unit_amount = $price_data['unit_amount'];

				// Backwards compatibility filter.
				if ( has_filter( 'simpay_form_' . $this->form->id . '_amount' ) ) {
					$unit_amount = simpay_get_filtered(
						'amount',
						simpay_convert_amount_to_dollars( $unit_amount ),
						$this->form->id
					);

					$unit_amount = simpay_convert_amount_to_cents( $unit_amount );
				}

				if ( has_filter( 'simpay_form_'. $this->form->id . '__default_amount') ) {
					$unit_amount = simpay_get_filtered(
						'_default_amount',
						simpay_convert_amount_to_dollars( $unit_amount ),
						$this->form->id
					);

					$unit_amount = simpay_convert_amount_to_cents( $unit_amount );
				}

				$this->unit_amount = $unit_amount;
			}

			if ( isset( $price_data['unit_amount_min'] ) ) {
				$this->unit_amount_min = $price_data['unit_amount_min'];
			}

			if (
				isset( $price_data['recurring'] ) &&
				! isset( $price_data['recurring']['id'] )
			) {
				$this->recurring = array(
					'interval'       => 'month',
					'interval_count' => 1,
				);

				if ( isset( $price_data['recurring']['interval'] ) ) {
					$this->recurring['interval'] = $price_data['recurring']['interval'];
				}

				if ( isset( $price_data['recurring']['interval_count'] ) ) {
					$this->recurring['interval_count'] = $price_data['recurring']['interval_count'];
				}
			}
		}

		// Currency symbol.
		$this->currency_symbol = simpay_get_currency_symbol( $this->currency );

		// Zero decimal.
		$this->is_zero_decimal = simpay_is_zero_decimal( $this->currency );

		// Recurring interval from defined Subscription via optiona recurring toggle.
		if ( isset( $price_data['recurring']['id'] ) ) {
			$stripe_recurring_obj = API\Prices\retrieve(
				$price_data['recurring']['id'],
				$this->form->get_api_request_args(),
				array(
					'cached' => true,
				)
			);

			$this->recurring = array(
				'id'             => $stripe_recurring_obj->id,
				'interval'       => $stripe_recurring_obj->recurring->interval,
				'interval_count' =>
					$stripe_recurring_obj->recurring->interval_count,
			);
		}

		// Append additional recurring data.
		if ( is_array( $this->recurring ) ) {
			if ( isset( $price_data['recurring']['invoice_limit'] ) ) {
				$this->recurring['invoice_limit'] =
					$price_data['recurring']['invoice_limit'];
			}

			if (
				! isset( $this->recurring['trial_period_days'] ) &&
				isset( $price_data['recurring']['trial_period_days'] )
			) {
				$this->recurring['trial_period_days'] =
					$price_data['recurring']['trial_period_days'];
			}
		}

		// Line items.
		if ( isset( $price_data['line_items'] ) ) {
			$line_items = array();

			foreach ( $price_data['line_items'] as $k => $line_item ) {
				// Attempt to name and ID based on order.
				switch ( $k ) {
					case 0:
						$id   = 'subscription-setup-fee';
						$name = 'Subscription Setup Fee';
						break;
					case 1:
						$id   = 'plan-setup-fee';
						$name = 'Plan Setup Fee';
						break;
				};

				$line_items[] = array(
					'id'          => $id,
					'name'        => $name,
					'unit_amount' => $line_item['unit_amount'],
					'currency'    => $this->currency,
				);
			}

			if ( ! empty( $line_items ) ) {
				$this->line_items = $line_items;
			}
		}
	}

	/**
	 * Determines if the price has been defined in Stripe.
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	public function is_defined_amount() {
		return simpay_payment_form_prices_is_defined_price( $this->id );
	}

	/**
	 * Returns a label for display.
	 *
	 * If a custom label is set, use that. Otherwise build a label based on price data.
	 *
	 * @since 4.1.0
	 *
	 * @return string Custom label, derived label, or empty string if price data is invalid.
	 */
	public function get_display_label() {
		if ( null !== $this->label ) {
			return $this->label;
		}

		return $this->get_generated_label();
	}

	/**
	 * Returns a generated label using the price data.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	public function get_generated_label( $args = array() ) {
		if (
			null === $this->unit_amount ||
			null === $this->currency
		) {
			return '';
		}

		$defaults = array(
			'include_trial'      => true,
			'include_line_items' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		$amount = $this->unit_amount_min
			? $this->unit_amount_min
			: $this->unit_amount;

		// Create a label.
		$display_price = simpay_format_currency( $amount, $this->currency );

		if ( ! empty( $this->recurring ) && false == $this->can_recur ) {
			$intervals = simpay_get_recurring_intervals();
			$count     = $this->recurring['interval_count'];

			$label = sprintf(
				/* translators: %1$s Price option amount. %2$s Recurring interval count. %3$s Recurring interval. */
				esc_html__( '%1$s every %2$s %3$s', 'stripe' ),
				$display_price,
				$count,
				$intervals[ $this->recurring['interval'] ][ 1 === $count ? 0 : 1 ]
			);

			if (
				isset( $this->recurring['trial_period_days'] ) &&
				true === $args['include_trial']
			) {
				$label = sprintf(
					/* translators: %1$s Trial length. %2$s Generated price label */
					esc_html__( '%1$s day free trial then %2$s', 'stripe' ),
					$this->recurring['trial_period_days'],
					$label
				);
			}

			if (
				null !== $this->line_items &&
				true === $args['include_line_items']
			) {
				$setup_fee_amount = array_reduce(
					$this->line_items,
					function( $amount, $line_item ) {
						return $amount + $line_item['unit_amount'];
					},
					0
				);

				$label = sprintf(
					/* translators: %1$s Generated label. %2$s Setup fee amount. */
					esc_html__( '%1$s with a %2$s one-time fee', 'stripe' ),
					$label,
					simpay_format_currency( $setup_fee_amount, $this->currency )
				);
			}
		} else {
			$label = $display_price;
		}

		if ( null !== $this->unit_amount_min ) {
			$label = sprintf(
				/* translators: %s Minimum price. */
				__( 'starting at %s', 'stripe' ),
				$label
			);
		}

		if ( null !== $this->id && null !== $this->recurring ) {
			/**
			 * Filters the Price/Plan label name.
			 *
			 * @since 3.0.0
			 *
			 * @param string                     $label Price/Plan label for Price selector.
			 * @param \SimplePay\Vendor\Stripe\Price|\SimplePay\Vendor\Stripe\Plan $price Stripe Price or Plan object.
			 */
			$label = apply_filters(
				'simpay_plan_name_label',
				$label,
				$this->__unstable_stripe_object
			);
		}

		return $label;
	}

	/**
	 * Returns a simplified generated label using the price data.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	public function get_simplified_label() {
		if (
			null === $this->unit_amount ||
			null === $this->currency
		) {
			return '';
		}

		if ( null !== $this->label ) {
			return $this->label;
		}

		$amount = $this->unit_amount_min
			? $this->unit_amount_min
			: $this->unit_amount;

		// Create a label.
		$display_price = simpay_format_currency( $amount, $this->currency );

		if ( ! empty( $this->recurring ) && false == $this->can_recur ) {
			$intervals = simpay_get_recurring_intervals();
			$count     = $this->recurring['interval_count'];

			if ( 1 === $count ) {
				$label = sprintf(
					/* translators: %1$s Price option amount. %2$s Recurring interval. */
					esc_html__( '%1$s/%2$s', 'stripe' ),
					$display_price,
					$intervals[ $this->recurring['interval'] ][0]
				);
			} else {
				$label = sprintf(
					/* translators: %1$s Price option amount. %2$s Price option currency code. %3$s Recurring interval count. %4$s Recurring interval. */
					esc_html__( '%1$s every %2$s %3$s', 'stripe' ),
					$display_price,
					$count,
					$intervals[ $this->recurring['interval'] ][ 1 === $count ? 0 : 1 ]
				);
			}
		} else {
			$label = $display_price;
		}

		if ( null !== $this->id && null !== $this->recurring ) {
			/**
			 * Filters the Price/Plan label name.
			 *
			 * @since 3.0.0
			 *
			 * @param string                     $label Price/Plan label for Price selector.
			 * @param \SimplePay\Vendor\Stripe\Price|\SimplePay\Vendor\Stripe\Plan $price Stripe Price or Plan object.
			 */
			$label = apply_filters(
				'simpay_plan_name_label',
				$label,
				$this->__unstable_stripe_object
			);
		}

		return $label;
	}

	/**
	 * Returns an array containing the representation of the public properties.
	 *
	 * @since 4.1.0
	 *
	 * @return array
	 */
	public function to_array() {
		$price_data = get_object_vars( $this );
		unset( $price_data['__unstable_stripe_object'] );
		unset( $price_data['form'] );

		return $price_data;
	}

}
