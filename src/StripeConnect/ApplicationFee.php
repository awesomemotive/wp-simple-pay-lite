<?php
/**
 * Stripe Connect: Application fee
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\StripeConnect;

use Exception;
use SimplePay\Core\API;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Scheduler\SchedulerInterface;
use SimplePay\Core\Settings;
use SimplePay\Core\Transaction\TransactionRepository;
use SimplePay\Core\Utils;

/**
 * ApplicationFee class.
 *
 * @since 4.4.1
 */
class ApplicationFee implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Scheduler.
	 *
	 * @since 4.4.6
	 * @var \SimplePay\Core\Scheduler\SchedulerInterface
	 */
	private $scheduler;

	/**
	 * Transactions.
	 *
	 * @since 4.4.6
	 * @var \SimplePay\Core\Transaction\TransactionRepository
	 */
	private $transactions;

	/**
	 * ApplicationFee
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Core\Scheduler\SchedulerInterface $scheduler Scheduler.
	 */
	public function __construct(
		SchedulerInterface $scheduler,
		TransactionRepository $transactions
	) {
		$this->scheduler    = $scheduler;
		$this->transactions = $transactions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		$subscribers = array(
			// Display a notice that an additional fee is being charged.
			'__unstable_simpay_stripe_connect_account_message' =>
				'maybe_show_application_fee',

			// Queues transaction records recorded with an application fee for
			// possible update to remove the application fee if the license is
			// now valid.
			'pre_update_option_simpay_license_data' =>
				'queue_application_fee_removal',

			// Removes application fees from Subscriptions.
			'simpay_remove_application_fees'        =>
				'remove_application_fees',
		);

		return $subscribers;
	}

	/**
	 * Appends a note about additional fees being applied when using a new Lite install.
	 *
	 * @since 4.4.1
	 *
	 * @param string $message Stripe Connect account message.
	 * @return string
	 */
	public function maybe_show_application_fee( $message ) {
		$message .= '<br /><br />';

		if ( false === $this->has_application_fee() ) {
			$message .= sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'Your %1$s license is active. No added transaction fees from WP Simple Pay — some Stripe tools may have their own costs. %2$sLearn more%3$s',
					'stripe'
				),
				'<strong>' . ucfirst( $this->license->get_level() ) . '</strong>',
				'<a href="' . esc_url( simpay_docs_link( 'Learn More', 'stripe-and-wp-simple-pay-fees', 'stripe-account-settings', true ) ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
				Utils\get_external_link_markup() . '</a>'
			);

			return $message;
		}

		if ( $this->license->is_lite() ) {
			$upgrade_url = simpay_pro_upgrade_url( 'stripe-account-settings' );

			$message .= sprintf(
				/* translators: %1$s Opening strong tag, do not translate. %2$s Closing strong tag, do not translate. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
				__(
					'%1$sPay as you go pricing%2$s: 3%% fee per-transaction + Stripe fees. %3$sUpgrade to Pro%4$s for no added fees and priority support.',
					'stripe'
				),
				'<strong>',
				'</strong>',
				'<a href="' . esc_url( $upgrade_url ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
		} else {
			// No license.
			if ( empty( $this->license->get_key() ) ) {
				$activate_url = Settings\get_url(
					array(
						'section'    => 'general',
						'subsection' => 'license',
					)
				);

				$message .= sprintf(
					/* translators: %1$s Opening strong tag, do not translate. %2$s Closing strong tag, do not translate. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
					__(
						'%1$sPay as you go pricing%2$s: 3%% fee per-transaction + Stripe fees. %3$sActivate your license%4$s to remove additional fees and unlock powerful features.',
						'stripe'
					),
					'<strong>',
					'</strong>',
					'<a href="' . esc_url( $activate_url ) . '" rel="noopener noreferrer">',
					'</a>'
				);
			} elseif ( false === $this->license->is_valid() ) {
				$renew_url = add_query_arg(
					array(
						'edd_license_key' => $this->license->get_key(),
						'discount'        => 'SAVE50',
					),
					sprintf( '%s/checkout', untrailingslashit( SIMPLE_PAY_STORE_URL ) ) // @phpstan-ignore-line
				);

				$renew_url = simpay_ga_url( $renew_url, 'stripe-account-settings' );

				$learn_more_url = simpay_docs_link(
					'Learn More',
					'what-happens-if-my-license-expires',
					'stripe-account-settings',
					true
				);

				$message .= sprintf(
					/* translators: %1$s Opening strong tag, do not translate. %2$s Closing strong tag, do not translate. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
					__(
						'%1$sPay as you go pricing%2$s: 3%% fee per-transaction + Stripe fees. %3$sRenew your license (save 50%% off!)%4$s to remove additional fees and unlock powerful features. %5$sLearn more%6$s',
						'stripe'
					),
					'<strong>',
					'</strong>',
					'<a href="' . esc_url( $renew_url ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>',
					'<a href="' . esc_url( $learn_more_url ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
			}
		}

		return $message;
	}

	/**
	 * Returns the application fee percentage.
	 *
	 * @since 4.7.0
	 *
	 * @return int
	 */
	public function get_application_fee_percentage() {
		return 3;
	}

	/**
	 * Returns an application fee amount for a given amount.
	 *
	 * @since 4.7.0
	 *
	 * @param int $amount Amount to calculate the application fee for.
	 * @return float
	 */
	public function get_application_fee_amount( $amount ) {
		return round( $amount * ( $this->get_application_fee_percentage() / 100 ), 0 );
	}

	/**
	 * Adds an application fee to Checkout Session and PaymentIntent arguments.
	 *
	 * @since 4.4.6
	 *
	 * @param array<mixed> $payment_intent_args Payment intent arguments.
	 * @return array<mixed> Payment intent arguments, maybe with an application fee amount.
	 */
	public function maybe_add_one_time_application_fee( $payment_intent_args ) {
		if ( false === $this->has_application_fee() ) {
			return $payment_intent_args;
		}

		$payment_intent_args['application_fee_amount'] = round(
			$payment_intent_args['amount'] * ( $this->get_application_fee_percentage() / 100 ),
			0
		);

		return $payment_intent_args;
	}

	/**
	 * Adds an application fee to Subscription arguments.
	 *
	 * @since 4.4.6
	 *
	 * @param array<mixed> $subscription_args Subscription arguments.
	 * @return array<mixed> Subscription arguments, maybe with an application fee amount.
	 */
	public function maybe_add_subscription_application_fee( $subscription_args ) {
		if ( false === $this->has_application_fee() ) {
			return $subscription_args;
		}

		$subscription_args['application_fee_percent'] = $this->get_application_fee_percentage();

		return $subscription_args;
	}

	/**
	 * Determines if an application fee is being added to payments.
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	public function has_application_fee() {
		// Must be using Stripe Connect.
		if ( false === simpay_get_account_id() ) {
			return false;
		}

		// Pro.
		if ( false === $this->license->is_lite() ) {
			$is_missing = empty( $this->license->get_key() );
			$installed  = get_option( 'simpay_installed', time() );

			// License is valid, do not add a fee.
			if ( $this->license->is_valid() ) {
				return false;

				// License is missing but installed within 24 hours.
			} elseif ( $is_missing && ( time() - $installed < ( HOUR_IN_SECONDS * 24 ) ) ) {
				return false;

				// License is expired but inside of the grace period, do not add a fee.
			} elseif (
				'expired' === $this->license->get_status() &&
				$this->license->is_in_grace_period()
			) {
				return false;
			}

			// Lite.
		} else {

			// Not a new Lite connection, do not add a fee.
			if ( false === $this->is_new_lite_connection() ) {
				return false;
			}

			// Account country does not suppport application fees, do not add a fee.
			if ( false === $this->is_account_country_supported() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Queues the removal of application fees from Subscriptions if the license becomes valid.
	 *
	 * @since 4.4.6
	 *
	 * @param \stdClass $license_data License data.
	 * @return \stdClass License data.
	 */
	public function queue_application_fee_removal( $license_data ) {
		// License is still not valid, do nothing.
		if ( 'valid' !== $license_data->license ) {
			return $license_data;
		}

		// Schedule the first batch of application fee removal.
		$first_batch = $this->get_application_fee_transactions();

		if ( ! empty( $first_batch ) ) {
			$this->scheduler->schedule_once(
				time(),
				'simpay_remove_application_fees',
				array(
					'transactions' => $first_batch,
				)
			);
		}

		return $license_data;
	}

	/**
	 * Attempts to remove application fees from Subscriptions.
	 *
	 * Schedules the next batch if there are more transactions to process.
	 *
	 * @since 4.4.6
	 *
	 * @param array<string, array<string, int|string|null>> $transactions Transactions to process.
	 * @return void
	 */
	public function remove_application_fees( $transactions ) {
		foreach ( $transactions as $k => $transaction ) {
			$txn = $transaction;
			/** @var int $txn_id */
			$txn_id = $txn['id'];
			/** @var int $form_id */
			$form_id = $txn['form_id'];

			// Attempt to retrieve API keys to use. If the form cannot be found
			// use the global unfiltered values.
			$form             = simpay_get_form( $form_id );
			$api_request_args = false !== $form
				? $form->get_api_request_args()
				: array(
					'api_key'  => simpay_get_secret_key(),
					'livemode' => simpay_is_livemode(),
				);

			// We have a Subscription ID already, use it.
			if ( null !== $txn['subscription_id'] ) {
				/** @var string $subscription_id */
				$subscription_id = $txn['subscription_id'];

				// Find the Subscription from the Checkout Session.
			} elseif ( 'checkout_session' === $txn['object'] ) {
				try {
					/** @var string $object_id */
					$object_id = $txn['object_id'];
					$session   = API\CheckoutSessions\retrieve(
						$object_id,
						$api_request_args
					);

					// Session does not have an associated Subscription, consider it processed.
					if ( null === $session->subscription ) {
						$subscription_id = null;

						// Use the Session's associated Subscription.
					} else {
						/** @var string $subscription_id */
						$subscription_id = $session->subscription;
					}

					// Session cannot be retrieved, consider it processed.
				} catch ( Exception $e ) {
					$subscription_id = null;
				}
			} else {
				// Error retrieving Session.
				$subscription_id = null;
			}

			// Unable to find a subscription, consider it processed.
			if ( null === $subscription_id ) {
				// Update internal transaction record.
				$this->transactions->update(
					$txn_id,
					array(
						'application_fee' => false,
					)
				);

				// Remove from list.
				unset( $transactions[ $k ] );
				continue;
			}

			// Remove the applicaiton fee from each Subscription.
			try {
				// Update in Stripe.
				API\Subscriptions\update(
					$subscription_id,
					array(
						'application_fee_percent' => 0,
					),
					$api_request_args
				);

				// Update internal transaction record.
				$this->transactions->update(
					$txn_id,
					array(
						'application_fee' => false,
					)
				);

				unset( $transactions[ $k ] );
			} catch ( Exception $e ) {
				// Keep the transaction to attempt to process in the next batch.
			}
		}

		// Retrieve the next batch.
		$next_batch = $this->get_application_fee_transactions();

		// Merge with any that were unable to process.
		$transactions = array_merge(
			$transactions,
			$next_batch
		);

		// None left, do nothing.
		if ( empty( $transactions ) ) {
			return;
		}

		// Schedule the next batch.

		$this->scheduler->schedule_once(
			time(),
			'simpay_remove_application_fees',
			array(
				'transactions' => $transactions,
			)
		);
	}

	/**
	 * Determines if the latest Stripe Connect connection is "new" (i.e reconnected after 4.4.1).
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	private function is_new_lite_connection() {
		$connect_account_type = get_option(
			'simpay_stripe_connect_type',
			''
		);

		if (
			! empty( $connect_account_type ) &&
			'pro' === $connect_account_type &&
			$this->license->is_lite()
		) {
			return true;
		}

		// Lite has not been reconnected yet, do not add a fee.
		return (
			! empty( $connect_account_type ) &&
			'lite' === $connect_account_type
		);
	}

	/**
	 * Determines if the Stripe account country can use application fees.
	 *
	 * @since 4.4.1
	 *
	 * @return bool
	 */
	private function is_account_country_supported() {
		/** @var string $account_country */
		$account_country = simpay_get_setting( 'account_country', 'US' );

		return ! in_array(
			strtolower( $account_country ),
			$this->get_unavailable_country_codes(),
			true
		);
	}

	/**
	 * Returns a list of country that are not compatible with application fees.
	 *
	 * @since 4.4.1
	 *
	 * @return array<string>
	 */
	private function get_unavailable_country_codes() {
		return array(
			'br',
			'in',
			'mx',
		);
	}

	/**
	 * Returns a list of transactions (with a subset of information) that were
	 * created with application fees.
	 *
	 * @since 4.4.6
	 *
	 * @return array<array<string, int|null|string>>
	 */
	private function get_application_fee_transactions() {
		/** @var array<\SimplePay\Core\Transaction\Transaction> $transactions */
		$transactions = $this->transactions->query(
			array(
				'application_fee' => true,
				'number'          => 10,
			)
		);

		// Return a subset of the data to keep the arguments passed to the scheduler smaller.
		return array_map(
			function ( $transaction ) {
				return array(
					'id'              => $transaction->id,
					'form_id'         => $transaction->form_id,
					'object'          => $transaction->object,
					'object_id'       => $transaction->object_id,
					'subscription_id' => $transaction->subscription_id,
				);
			},
			$transactions
		);
	}
}
