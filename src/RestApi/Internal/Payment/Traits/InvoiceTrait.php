<?php
/**
 * Invoice trait
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.11.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Traits;

use SimplePay\Core\API\Invoices;
use SimplePay\Core\API\InvoiceItems;
use SimplePay\Core\API\PaymentIntents;
use SimplePay\Core\PaymentForm\PriceOption;
use SimplePay\Core\RestApi\Internal\Payment\Utils\FeeRecoveryUtils;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;

trait InvoiceTrait {

	/**
	 * Application fee handling.
	 *
	 * @since 4.11.0
	 *
	 * @var \SimplePay\Core\StripeConnect\ApplicationFee
	 */
	protected $application_fee;

	/**
	 * Create an invoice for the customer.
	 *
	 * @since 4.11.0
	 *
	 * @param \WP_REST_Request                  $request The payment request.
	 * @param \SimplePay\Vendor\Stripe\Customer $customer The customer.
	 *
	 * @return null|string|\SimplePay\Vendor\Stripe\PaymentIntent
	 */
	private function create_invoice( $request, $customer ) {
		$items        = (array) $request->get_param( 'price_ids' );
		$form         = PaymentRequestUtils::get_form( $request );
		$fields       = $form->custom_fields;
		$form_values  = PaymentRequestUtils::get_form_values( $request );
		$tax_rates    = simpay_get_payment_form_tax_rates( $form );
		$tax_status   = get_post_meta( $form->id, '_tax_status', true );
		$tax_rate_ids = ! empty( $tax_rates )
			? wp_list_pluck( $tax_rates, 'id' )
			: array();

		$invoice_args = array(
			'customer'         => $customer->id,
			'description'      => $form->item_description,
			'metadata'         => PaymentRequestUtils::get_payment_metadata( $request ),
			'payment_settings' => array(
				'payment_method_types'   => PaymentRequestUtils::get_payment_method_types( $request ),
				'payment_method_options' => PaymentRequestUtils::get_payment_method_options( $request ),
			),
		);

		if ( 'fixed-global' === $tax_status ) {
			$invoice_args['default_tax_rates'] = $tax_rate_ids;
		}

		if ( 'automatic' === $tax_status ) {
			$invoice_args['automatic_tax'] = array(
				'enabled' => true,
			);
		}

		$invoice = Invoices\create(
			$invoice_args,
			$form->get_api_request_args()
		);

		$invoice_total = 0;

		foreach ( $items as $item ) {
			/** @var array{
			 *     price_id: string,
			 *     custom_amount: int,
			 *     quantity: int,
			 *     price_data: array{
			 *         label: string,
			 *         currency: string,
			 *         instance_id: string,
			 *     }
			 * } $item
			 */

			$price_data = new PriceOption( $item['price_data'], $form, $item['price_data']['instance_id'] );

			$invoice_item_args = array(
				'customer'    => $customer->id,
				'quantity'    => $item['quantity'],
				'description' => count( $items ) === 1
					? html_entity_decode( $form->company_name )
					: html_entity_decode( $price_data->get_display_label() ),
				'currency'    => $item['price_data']['currency'],
				'invoice'     => $invoice->id,
				'metadata'    => array(
					'simpay_price_instance_id' => $item['price_data']['instance_id'],
				),
			);

			// if price_id start with 'simpay_' then it is a custom price.
			if ( ! simpay_payment_form_prices_is_defined_price( $item['price_id'] ) ) {
				$invoice_item_args['unit_amount'] = $item['custom_amount'];

				$invoice_total += $item['custom_amount'] * $item['quantity'];
			} else {
				$invoice_item_args['price'] = $item['price_id'];

				$invoice_total += $price_data->unit_amount * $item['quantity'];
			}

			InvoiceItems\create(
				$invoice_item_args,
				$form->get_api_request_args()
			);
		}

		// Add the fee recovery amount, if avilable.
		if ( $form->has_fee_recovery() ) {
			$fee_recovery = FeeRecoveryUtils::get_fee_recovery_unit_amount(
				$request,
				$invoice_total
			);

			$fee_recovery_description = (
				isset( $fields['fee_recovery_label'] ) &&
				! empty( $fields['fee_recovery_label'] )
			)
				? $fields['fee_recovery_label']
				: esc_html__( 'Processing fee', 'stripe' );

			InvoiceItems\create(
				array(
					'customer'    => $customer->id,
					'quantity'    => 1,
					'description' => $fee_recovery_description,
					'invoice'     => $invoice->id,
					'unit_amount' => $fee_recovery,
				),
				$form->get_api_request_args()
			);
		}

		// Add the application fee, if needed.
		if ( $this->application_fee->has_application_fee() ) {
			$invoice_args['application_fee_amount'] =
				$this->application_fee->get_application_fee_amount( $invoice_total );
		}

		$invoice = $invoice->finalizeInvoice();

		/**
		 * Allow further processing after a Invoice is created from a posted form.
		 *
		 * @since 4.11.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Invoice $invoice Invoice.
		 * @param \SimplePay\Core\Abstracts\Form        $form Form instance.
		 * @param array<mixed>                          $form_data Deprecated.
		 * @param array<string, mixed>                  $form_values Form values.
		 * @param string                                $customer Customer ID.
		 */
		do_action(
			'simpay_after_invoice_from_payment_form_request',
			$invoice,
			$form,
			array(),
			$form_values,
			$customer->id
		);

		return PaymentIntents\retrieve(
			(string) $invoice->payment_intent,
			$form->get_api_request_args()
		);
	}
}
