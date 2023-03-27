<?php
/**
 * Emails: Subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Payments\Payment_Confirmation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EmailSubscriber class.
 *
 * @since 4.7.3
 */
class EmailSubscriber implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Registered emails.
	 *
	 * This should probably be handled by a more advanced service/registery. For now it's just a list.
	 *
	 * @since 4.7.3
	 * @var array<string, \SimplePay\Core\Emails\Email\EmailInterface>
	 */
	private $emails;

	/**
	 * EmailSubscriber.
	 *
	 * @since 4.7.3
	 *
	 * @param array<string, \SimplePay\Core\Emails\Email\EmailInterface> $emails Emails.
	 */
	public function __construct( $emails ) {
		$this->emails = $emails;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		$subscribers = array(
			// Summary report.
			'simpay_send_summary_report_email' => 'summary_report',
		);

		if ( $this->license->is_lite() ) {
			return $subscribers;
		}

		$payment_confirmation = array(
			'payment_confirmation',
			10,
			2,
		);

		$payment_notification = array(
			'payment_notification',
			10,
			2,
		);

		// Subscription created.
		$subscribers['simpay_webhook_subscription_created'] = array(
			$payment_notification,
			$payment_confirmation,
		);

		// One-time payment created.
		$subscribers['simpay_webhook_payment_intent_succeeded'] = array(
			$payment_notification,
			$payment_confirmation,
		);

		// Payment confirmation resent.
		$subscribers['simpay_resend_payment_confirmation'] = $payment_confirmation;

		// Upcoming invoice.
		$subscribers['simpay_webhook_invoice_upcoming'] = array(
			'upcoming_invoice',
			10,
			3,
		);

		// Invoice confirmations.
		$subscribers['simpay_webhook_invoice_payment_succeeded'][] = array(
			'invoice_confirmation',
			10,
			3,
		);

		// Legacy.
		// These aren't used anymore, but we'll keep it here for backwards compatibility.
		// `simpay_webhook_payment_intent_succeeded` is used instead instead.
		$subscribers['simpay_webhook_charge_succeeded'] = array(
			$payment_notification,
			$payment_confirmation,
		);

		return $subscribers;
	}

	/**
	 * Mails the "Payment Confirmation" email.
	 *
	 * @since 4.7.3
	 *
	 * @param \SimplePay\Vendor\Stripe\Event                                               $event Stripe Event.
	 * @param \SimplePay\Vendor\Stripe\Subscription|\SimplePay\Vendor\Stripe\PaymentIntent $object Stripe object.
	 * @return void
	 */
	public function payment_confirmation( $event, $object ) {
		/** @var \SimplePay\Core\Emails\Email\PaymentConfirmationEmail $email */
		$email = $this->emails['payment-confirmation'];

		// If the email is not enabled, do nothing.
		if ( ! $email->is_enabled() ) {
			return;
		}

		// If the object was not created by WP Simple Pay, do nothing.
		if ( ! isset( $object->metadata->simpay_form_id ) ) {
			return;
		}

		// Retrieve the payment confirmation data.
		/** @var \SimplePay\Vendor\Stripe\Customer $customer */
		$customer = $object->customer;

		// Ensure we have data before proceeding.
		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
			$customer->id,
			false,
			$object->metadata->simpay_form_id
		);

		// @todo log why the email didn't send.
		if ( empty( $payment_confirmation_data ) ) {
			return;
		}

		// Setup the mailer.
		$mailer = new Mailer( $email );

		// ...hydrate with the payment confirmation data.
		$mailer->set_data( $payment_confirmation_data );

		// ...then set the payment mode.
		$mailer->set_livemode(
			$payment_confirmation_data['form']->is_livemode()
		);

		// ...then set the address(es).
		/** @var string $to_address WP Simple Pay requires an email. */
		$to_address = $customer->email;
		$mailer->set_to( trim( $to_address ) );

		// ...then set subject.
		$mailer->set_subject( $email->get_subject() );

		// ...then set the body content.
		$type = 'one_time';

		if ( ! empty( $payment_confirmation_data['subscriptions'] ) ) {
			$type = 'subscription';

			if ( 'trialing' === $object->status ) {
				$type = 'trial';
			}
		}

		$mailer->set_body( $email->get_body( $type ) );

		// Finally, send the email.
		$mailer->send();
	}

	/**
	 * Mails the "Payment Notification" email.
	 *
	 * @since 4.7.3
	 *
	 * @param \SimplePay\Vendor\Stripe\Event                                               $event Stripe Event.
	 * @param \SimplePay\Vendor\Stripe\Subscription|\SimplePay\Vendor\Stripe\PaymentIntent $object Stripe object.
	 * @return void
	 */
	function payment_notification( $event, $object ) {
		/** @var \SimplePay\Core\Emails\Email\PaymentNotificationEmail $email */
		$email = $this->emails['payment-notification'];

		// If the email is not enabled, do nothing.
		if ( ! $email->is_enabled() ) {
			return;
		}

		// If the object was not created by WP Simple Pay, do nothing.
		if ( ! isset( $object->metadata->simpay_form_id ) ) {
			return;
		}

		// Retrieve the payment confirmation data.
		/** @var \SimplePay\Vendor\Stripe\Customer $customer */
		$customer = $object->customer;

		// Ensure we have data before proceeding.
		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
			$customer->id,
			false,
			$object->metadata->simpay_form_id
		);

		// @todo log why the email didn't send.
		if ( empty( $payment_confirmation_data ) ) {
			return;
		}

		// Setup the mailer.
		$mailer = new Mailer( $email );

		// ...hydrate with the payment confirmation data.
		$mailer->set_data( $payment_confirmation_data );

		// ...then set the payment mode.
		$mailer->set_livemode(
			$payment_confirmation_data['form']->is_livemode()
		);

		// ...then set the address(es).
		$mailer->set_to( $email->get_to() );

		// ...then set the subject.
		$mailer->set_subject( $email->get_subject() );

		// ... then parse and set the body content.
		$mailer->set_body( $email->get_body() );

		// Finally, send the email.
		$mailer->send();
	}

	/**
	 * Mails the "Upcoming Invoice" email.
	 *
	 * @since 4.7.3
	 *
	 * @param \SimplePay\Vendor\Stripe\Event        $event        Stripe Event object.
	 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice      Stripe Invoice object.
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
	 * @return void
	 */
	function upcoming_invoice( $event, $invoice, $subscription ) {
		/** @var \SimplePay\Core\Emails\Email\UpcomingInvoiceEmail $email */
		$email = $this->emails['upcoming-invoice'];

		// If the email is not enabled, do nothing.
		if ( ! $email->is_enabled() ) {
			return;
		}

		$send_upcoming_invoice_email = true;

		/**
		 * Determines if the "Upcoming Invoice" email should be sent.
		 *
		 * @since 3.9.0
		 * @since 4.0.0 Deprecated. Use email settings.
		 *
		 * @param bool                                  $send_upcoming_invoice_email If the email should be sent.
		 * @param \SimplePay\Vendor\Stripe\Event        $event Stripe Event object.
		 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice Stripe Invoice object.
		 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
		 */
		$send_upcoming_invoice_email = apply_filters(
			'simpay_send_upcoming_invoice_email',
			$send_upcoming_invoice_email,
			$event,
			$invoice,
			$subscription
		);

		// If the email is disabled via legacy filter, do nothing.
		if ( false === $send_upcoming_invoice_email ) {
			return;
		}

		// If the Subscription was not created by WP Simple Pay, do nothing.
		if ( ! isset( $subscription->metadata->simpay_form_id ) ) {
			return;
		}

		// If Subscription was created before 3.7.0, or is missing a key, do nothing.
		if ( ! isset( $subscription->metadata->simpay_subscription_key ) ) {
			return;
		}

		// Retrieve the payment confirmation data.
		/** @var \SimplePay\Vendor\Stripe\Customer $customer */
		$customer = $subscription->customer;

		// Ensure we have data before proceeding.
		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
			$customer->id,
			false,
			$subscription->metadata->simpay_form_id
		);

		// @todo log why the email didn't send.
		if ( empty( $payment_confirmation_data ) ) {
			return;
		}

		$mailer = new Mailer( $email );

		// ...hydrate with the payment confirmation data.
		$mailer->set_data( $payment_confirmation_data );

		// ...then set the payment mode.
		$mailer->set_livemode(
			$payment_confirmation_data['form']->is_livemode()
		);

		// ...then set the address(es).
		/** @var string $to_address */
		$to_address = $invoice->customer_email;
		$mailer->set_to( trim( $to_address ) );

		// ...then set subject.
		$mailer->set_subject( $email->get_subject() );

		// ...then parse and set the body.
		$mailer->set_body( $email->get_body() );

		// Finally, send the email.
		$mailer->send();
	}

	/**
	 * Mails the "Invoice Confirmation" email.
	 *
	 * @since 4.7.3
	 *
	 * @param \SimplePay\Vendor\Stripe\Event        $event Stripe Event object.
	 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice Stripe Invoice object.
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
	 * @return void
	 */
	function invoice_confirmation( $event, $invoice, $subscription ) {
		/** @var \SimplePay\Core\Emails\Email\InvoiceConfirmationEmail $email */
		$email = $this->emails['invoice-confirmation'];

		// If the email is not enabled, do nothing.
		if ( ! $email->is_enabled() ) {
			return;
		}

		// If this is the first invoice of the Subscription, do nothing.
		if ( 'subscription_create' === $invoice->billing_reason ) {
			return;
		}

		// If the Subscription was not created by WP Simple Pay, do nothing.
		if ( ! isset( $subscription->metadata->simpay_form_id ) ) {
			return;
		}

		// If Subscription was created before 3.7.0, or is missing a key, do nothing.
		if ( ! isset( $subscription->metadata->simpay_subscription_key ) ) {
			return;
		}

		// Retrieve the payment confirmation data.
		/** @var \SimplePay\Vendor\Stripe\Customer $customer */
		$customer = $subscription->customer;

		// Ensure we have data before proceeding.
		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
			$customer->id,
			false,
			$subscription->metadata->simpay_form_id
		);

		// @todo log why the email didn't send.
		if ( empty( $payment_confirmation_data ) ) {
			return;
		}

		// Setup the mailer.
		$mailer = new Mailer( $email );

		// ...hydrate with the payment confirmation data.
		$mailer->set_data( $payment_confirmation_data );

		// ...then set the address(es).
		/** @var string $to_address */
		$to_address = $invoice->customer_email;
		$mailer->set_to( trim( $to_address ) );

		// ...then set the subject.
		$mailer->set_subject( $email->get_subject() );

		// ...then parse and set the body.
		$mailer->set_body( $email->get_body() );

		// Finally, send the email.
		$mailer->send();
	}

	/**
	 * Mails the "Summary Report" email.
	 *
	 * @since 4.7.3
	 *
	 * @return void
	 */
	public function summary_report() {
		/** @var \SimplePay\Core\Emails\Email\SummaryReportEmail $email */
		$email = $this->emails['summary-report'];

		// If the email is not enabled, do nothing.
		if ( ! $email->is_enabled() ) {
			return;
		}

		add_filter( 'simpay_emails_autop', '__return_false' );

		// Setup the mailer.
		$mailer = new Mailer( $email );

		// ...then set the address(es).
		$mailer->set_to( $email->get_to() );

		// ...then set the subject.
		$mailer->set_subject( $email->get_subject() );

		// ...then parse and set the body.
		$mailer->set_body( $email->get_body() );

		// Finally, send the email.
		$mailer->send();

		add_filter( 'simpay_emails_autop', '__return_true' );
	}

}
