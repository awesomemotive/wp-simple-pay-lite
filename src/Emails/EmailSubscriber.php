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
use SimplePay\Core\API;

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

		// Invoice payment created.
		$subscribers['simpay_webhook_invoice_paid'] = array(
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

		// Payment processing.
		$subscribers['simpay_webhook_payment_intent_processing'] = array(
			'payment_processing',
			10,
			3,
		);

		// Refunded payment confirmation.
		$subscribers['simpay_webhook_charge_refunded'] = array(
			'payment_refunded_confirmation',
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

		$subscribers['simpay_webhook_subscription_cancel'] = array(
			'subscription_cancel',
			10,
			3,
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

		$form_id = $object->metadata->simpay_form_id;

		// Retrieve the payment confirmation data.
		if ( $object->customer instanceof \SimplePay\Vendor\Stripe\Customer ) {
			/** @var \SimplePay\Vendor\Stripe\Customer $customer */
			$customer = $object->customer;

		} else {
			$form     = simpay_get_form( $form_id );
			$customer = API\Customers\retrieve(
				$object->customer, // @phpstan-ignore-line
				$form->get_api_request_args() // @phpstan-ignore-line
			);
		}

		// Ensure we have data before proceeding.
		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
			$customer->id,
			false,
			$form_id
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

		$body = '';

		// If there is a per-form custom mesasge, use it.
		$form = simpay_get_form( $form_id );

		if ( false !== $form ) {
			$confirmation_message = $form->get_email_confirmation_message();

			if ( ! empty( $confirmation_message ) ) {
				$body = $confirmation_message;
			}
		}

		// Otherwise use the default message.
		if ( empty( $body ) ) {
			$body = $email->get_body( $type );
		}

		$mailer->set_body( $body );

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

		$form_id = $object->metadata->simpay_form_id;

		// Retrieve the payment confirmation data.
		if ( $object->customer instanceof \SimplePay\Vendor\Stripe\Customer ) {
			/** @var \SimplePay\Vendor\Stripe\Customer $customer */
			$customer = $object->customer;

		} else {
			$form     = simpay_get_form( $form_id );
			$customer = API\Customers\retrieve(
				$object->customer, // @phpstan-ignore-line
				$form->get_api_request_args() // @phpstan-ignore-line
			);
		}

		// Ensure we have data before proceeding.
		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
			$customer->id,
			false,
			$form_id
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
		$to = $email->get_to();

		/**
		 * Filters the email address the payment notification is sent to.
		 *
		 * @since 4.7.12
		 *
		 * @param string               $to The email address the payment notification is sent to.
		 * @param array<string, mixed> $payment_confirmation_data The payment confirmation data.
		 */
		$to = apply_filters(
			'simpay_email_payment_notification_to',
			$to,
			$payment_confirmation_data
		);

		$mailer->set_to( $to );

		// ...then set the subject.
		$mailer->set_subject( $email->get_subject() );

		$body = '';

		// If there is a per-form custom mesasge, use it.
		$form = simpay_get_form( $form_id );

		if ( false !== $form ) {
			$notification_message = $form->get_email_notification_message();

			if ( ! empty( $notification_message ) ) {
				$type = 'one_time';

				if ( ! empty( $payment_confirmation_data['subscriptions'] ) ) {
					$type = 'subscription';
				}

				/**
				 * Filters the email notification message.
				 *
				 * @since 4.12.0
				 *
				 * @param string  $notification_message The email notification message.
				 * @param int     $form_id The form ID.
				 * @param string  $type The type of payment.
				 */
				$body = apply_filters(
					'simpay_email_payment_notification_message',
					$notification_message,
					$form_id,
					$type
				);
			}
		}

		// Otherwise use the default message.
		if ( empty( $body ) ) {
			$body = $email->get_body();
		}

		$mailer->set_body( $body );

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

	/**
	 * Mails the "Payment Refunded Confirmation" email.
	 *
	 * @since 4.10.0
	 * @param \SimplePay\Vendor\Stripe\Event  $event Stripe Event object.
	 * @param \SimplePay\Vendor\Stripe\Charge $charge Stripe Charge object.
	 * @param \SimplePay\Core\Abstracts\Form  $form   Form object.
	 *
	 * @return void
	 */
	public function payment_refunded_confirmation( $event, $charge, $form ) {
		/** @var \SimplePay\Core\Emails\Email\PaymentRefundedConfirmationEmail $email */
		$email = $this->emails['payment-refunded-confirmation'];

		// If the email is not enabled, do nothing.
		if ( ! $email->is_enabled() ) {
			return;
		}

		// Ensure we have data before proceeding.
		$payment_refund_data = Payment_Confirmation\get_confirmation_data(
			$charge->customer->id, // @phpstan-ignore-line
			false,
			$form->id
		);

		$payment_refund_data['charge'] = $charge;

		// Setup the mailer.
		$mailer = new Mailer( $email );

		// Set data.

		$mailer->set_data( $payment_refund_data );

		// Register smart tags.
		$email->register_smart_tags();

		// ...then set the address(es).
		$mailer->set_to( $payment_refund_data['customer']->email );

		// ...then set the subject.
		$mailer->set_subject( $email->get_subject() );

		// ...then parse and set the body.
		$mailer->set_body( $email->get_body() );

		// Finally, send the email.
		$mailer->send();
	}

	/**
	 * Mails the 'Subscription Cancel' email .
	 *
	 * @since 4.10.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Event        $event Stripe Event object .
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object .
	 * @return void
	 */
	public function subscription_cancel( $event, $subscription ) {
		if ( null === $subscription->canceled_at ) {
			return;
		}

		if ( 'customer.subscription.deleted' === $event->type
		&& $subscription->cancel_at_period_end ) {
			return;
		}

		/** @var \SimplePay\Core\Emails\Email\SubscriptionCancellationNotification $subscription_cancel_confirmation_email */
		$subscription_cancel_confirmation_email = $this->emails['subscription-cancel-confirmation'];

		/** @var \SimplePay\Core\Emails\Email\SubscriptionCancellationNotification $subscription_cancel_notification_email */
		$subscription_cancel_notification_email = $this->emails['subscription-cancel-notification'];

		$notification_mailer = new Mailer( $subscription_cancel_notification_email );
		$confirmation_mailer = new Mailer( $subscription_cancel_confirmation_email );

		// Retrieve the payment confirmation data.
		/** @var \SimplePay\Vendor\Stripe\Customer $customer */
		$customer = $subscription->customer;

		// Ensure we have data before proceeding.
		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
			$customer->id,
			false,
			$subscription->metadata->simpay_form_id // @phpstan-ignore-line
		);

		// Set email for merchant.
		$notification_mailer->set_data( $payment_confirmation_data );
		$notification_mailer->set_to( $subscription_cancel_notification_email->get_to() );
		$notification_mailer->set_subject( $subscription_cancel_notification_email->get_subject() );
		$notification_mailer->set_body( $subscription_cancel_notification_email->get_body() );

		// Set email for customer.
		$confirmation_mailer->set_data( $payment_confirmation_data );
		$confirmation_mailer->set_to( $subscription->customer->email ); /** @phpstan-ignore-line */
		$confirmation_mailer->set_subject( $subscription_cancel_confirmation_email->get_subject() );
		$confirmation_mailer->set_body( $subscription_cancel_confirmation_email->get_body() );

		// Send emails.
		if ( $subscription_cancel_notification_email->is_enabled() ) {
			$notification_mailer->send();
		}

		if ( $subscription_cancel_confirmation_email->is_enabled() ) {
			$confirmation_mailer->send();
		}
	}

	/**
	 * Mails the "Payment Processing" email.
	 *
	 * @since 4.10.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Event         $event Stripe webhook event.
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent Stripe PaymentIntent.
	 * @param \SimplePay\Core\Abstracts\Form         $form Form object.
	 * @return void
	 */
	public function payment_processing( $event, $payment_intent, $form ) {
		// Retrieve the payment confirmation data.
		/** @var \SimplePay\Vendor\Stripe\Customer $customer */
		$customer = $payment_intent->customer;

		// Ensure we have data before proceeding.
		$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
			$customer->id,
			false,
			$form->id
		);

		// if there is no data, do nothing.
		if ( empty( $payment_confirmation_data ) ) {
			return;
		}

		/** @var \SimplePay\Core\Emails\Email\PaymentProcessingConfirmationEmail $payment_processing_confirmation_email */
		$payment_processing_confirmation_email = $this->emails['payment-processing-confirmation'];

		/** @var \SimplePay\Core\Emails\Email\PaymentProcessingNotificationEmail $payment_processing_notification_email */
		$payment_processing_notification_email = $this->emails['payment-processing-notification'];

		// Send confirmation email if enabled.
		if ( $payment_processing_confirmation_email->is_enabled() ) {
			$confirmation_mailer = new Mailer( $payment_processing_confirmation_email );
			$confirmation_mailer->set_data( $payment_confirmation_data );
			$confirmation_mailer->set_to( $customer->email ); // @phpstan-ignore-line
			$confirmation_mailer->set_subject( $payment_processing_confirmation_email->get_subject() );
			$confirmation_mailer->set_body( $payment_processing_confirmation_email->get_body() );
			$confirmation_mailer->send();
		}

		// Send notification email if enabled.
		if ( $payment_processing_notification_email->is_enabled() ) {
			$notification_mailer = new Mailer( $payment_processing_notification_email );
			$notification_mailer->set_data( $payment_confirmation_data );
			$notification_mailer->set_to( $payment_processing_notification_email->get_to() );
			$notification_mailer->set_subject( $payment_processing_notification_email->get_subject() );
			$notification_mailer->set_body( $payment_processing_notification_email->get_body() );
			$notification_mailer->send();
		}
	}
}
