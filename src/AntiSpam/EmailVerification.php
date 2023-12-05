<?php
/**
 * Anti-Spam: Email Verification
 *
 * Requires email verification if multiple charge failures due to fraud within
 * a time period.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.0
 */

namespace SimplePay\Core\AntiSpam;

use Exception;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\RestApi\Internal\Payment\Utils\PaymentRequestUtils;
use SimplePay\Core\Scheduler\SchedulerInterface;
use SimplePay\Core\Settings;

/**
 * EmailVerification class.
 *
 * @since 4.6.0
 */
class EmailVerification implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Scheduler.
	 *
	 * @since 4.6.0
	 * @var \SimplePay\Core\Scheduler\SchedulerInterface
	 */
	private $scheduler;

	/**
	 * EmailVerification
	 *
	 * @since 4.6.0
	 *
	 * @param \SimplePay\Core\Scheduler\SchedulerInterface $scheduler Scheduler.
	 */
	public function __construct( SchedulerInterface $scheduler ) {
		$this->scheduler = $scheduler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		$subscribers = array(
			// Add/output settings.
			'simpay_register_settings'                => 'add_settings',
			'__unstable_simpay_after_form_anti_spam_settings' =>
				array( 'add_payment_form_settings' ),

			// Log fraud events.
			'simpay_webhook_charge_failed'            =>
				array( 'log_fraud_event', 10, 2 ),

			// Schedule and cleanup expired verification codes.
			'init'                                    =>
				'schedule_email_verification_code_cleanup',
			'simpay_cleanup_email_verification_codes' =>
				'cleanup_verification_codes',
		);

		// Require verification before processing endpoints if there are more than
		// fraud events than the set threshold, within the set timeframe.
		if (
			'yes' === simpay_get_setting( 'fraud_email_verification', 'yes' ) &&
			$this->get_fraud_event_count() >= $this->get_fraud_event_threshold() &&
			$this->is_latest_fraud_event_in_timeframe()
		) {
			// Send the verification code.
			if ( simpay_is_upe() ) {
				$subscribers['simpay_before_payment_create'] =
					array( 'send_verification_code_upe', 10, 4 );
			} else {
				$subscribers['simpay_before_customer_from_payment_form_request'] =
					array( 'send_verification_code', 10, 4 );
			}

			// ...verify on PaymentIntent.
			$subscribers['simpay_before_paymentintent_from_payment_form_request'] =
				array( 'verify_verification_code_rest', 10, 4 );
			// ...verify on SetupIntent.
			$subscribers['simpay_before_setupintent_from_payment_form_request'] =
				array( 'verify_verification_code_rest', 10, 4 );
			// ...verify Subscription.
			$subscribers['simpay_before_subscription_from_payment_form_request'] =
				array( 'verify_verification_code_rest', 10, 4 );
			// ...verify on Charge.
			$subscribers['simpay_before_charge_from_payment_form_request'] =
				array( 'verify_verification_code_rest', 10, 4 );

			// Remove the verification code after a payment action has been made.

			// ...on PaymentIntent.
			$subscribers['simpay_after_paymentintent_from_payment_form_request'] =
				array( 'remove_verification_code_rest', 10, 4 );
			// ...on Subscription.
			$subscribers['simpay_after_subscription_from_payment_form_request'] =
				array( 'remove_verification_code_rest', 10, 4 );
			// ...on SetupIntent.
			$subscribers['simpay_after_setupintent_from_payment_form_request'] =
				array( 'remove_verification_code_rest', 10, 4 );

			// Use a cleaned version of the submitted email address as the rate
			// limiter ID. spencer+123@gmail.com turns in to spencer@gmail.com.
			$subscribers['simpay_rate_limiting_id'] =
				array( 'set_rate_limiting_id', 10, 2 );

			// ... and extend the rate limiting window.
			$subscribers['simpay_rate_limiting_timeout'] =
				'set_rate_limiting_timeout';
		}

		return $subscribers;
	}

	/**
	 * Adds the settings UI for email verification fraud prevention.
	 *
	 * @since 4.6.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 * @return void
	 */
	public function add_settings( $settings ) {
		// Enable/Disable.
		$settings->add(
			new Settings\Setting_Checkbox(
				array(
					'id'          => 'fraud_email_verification',
					'section'     => 'general',
					'subsection'  => 'recaptcha',
					'label'       => esc_html_x(
						'Email Verification',
						'setting label',
						'stripe'
					),
					'input_label' => sprintf(
						'%s <strong>%s</strong>',
						esc_html_x(
							'Enable email verification for on-site payment forms if multiple payment declines occur.',
							'setting input label',
							'stripe'
						),
						esc_html__( 'Highly recommended.', 'stripe' )
					),
					'value'       => simpay_get_setting(
						'fraud_email_verification',
						'yes'
					),
					'toggles'     => array(
						'value'    => 'yes',
						'settings' => array(
							'fraud_email_verification_threshold',
							'fraud_email_verification_timeframe',
						),
					),
					'priority'    => 60,
					'schema'      => array(
						'type' => 'string',
						'enum' => array( 'yes', 'no' ),
					),
				)
			)
		);

		// Threshold.
		$settings->add(
			new Settings\Setting_Input(
				array(
					'id'          => 'fraud_email_verification_threshold',
					'section'     => 'general',
					'subsection'  => 'recaptcha',
					'type'        => 'number',
					'label'       => esc_html_x(
						'Threshold',
						'setting label',
						'stripe'
					),
					'value'       => simpay_get_setting(
						'fraud_email_verification_threshold',
						3
					),
					'min'         => 1,
					'step'        => 1,
					'description' => sprintf(
						'%s <p class="description>%s</p>',
						esc_html__(
							'fraud declines',
							'stripe'
						),
						esc_html__(
							'Require email verification for on-site payment forms if more than this number of charges fail due to fraud.',
							'stripe'
						)
					),
					'priority'    => 61,
					'schema'      => array(
						'type' => 'number',
					),
				)
			)
		);

		// Timeframe.
		$settings->add(
			new Settings\Setting_Input(
				array(
					'id'          => 'fraud_email_verification_timeframe',
					'section'     => 'general',
					'subsection'  => 'recaptcha',
					'type'        => 'number',
					'label'       => esc_html_x(
						'Timeframe',
						'setting label',
						'stripe'
					),
					'value'       => simpay_get_setting(
						'fraud_email_verification_timeframe',
						6
					),
					'min'         => '0.1',
					'step'        => 'any',
					'description' => sprintf(
						'%s <p class="description>%s</p>',
						esc_html__(
							'hours',
							'stripe'
						),
						esc_html__(
							'Require email verification for on-site payment forms until there are no more failed payments due to fraud for this many hours.',
							'stripe'
						)
					),
					'priority'    => 62,
					'schema'      => array(
						'type' => 'number',
					),
				)
			)
		);
	}

	/**
	 * Displays the global setting value when editing a payment form.
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function add_payment_form_settings() {
		$settings_url = Settings\get_url(
			array(
				'section'    => 'general',
				'subsection' => 'recaptcha',
				'setting'    => 'fraud_email_verification',
			)
		);

		$enabled = simpay_get_setting(
			'fraud_email_verification',
			'yes'
		);
		?>

		<div class="simpay-show-if" data-if="_form_type" data-is="on-site" style="margin: 12px 0 0;">
			<label for="_email_verification" class="simpay-field-bool">
				<input
					name="_email_verification"
					type="checkbox"
					id="_email_verification"
					class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
					<?php checked( true, 'yes' === $enabled ); ?>
					<?php if ( 'yes' === $enabled ) : ?>
						readonly
					<?php endif; ?>
					data-settings-url="<?php echo esc_attr( $settings_url ); ?>"
				/>

				<?php esc_html_e( 'Email Verification', 'stripe' ); ?>
			</label>

			<p class="description">
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %1$s opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
						__(
							'%1$sConfigure email verification settings%2$s to require verification when fraud events are detected.',
							'stripe'
						),
						'<a href="' . esc_url( $settings_url ) . '" target="_blank">',
						'</a>'
					),
					array(
						'a'    => array(
							'href'   => true,
							'target' => true,
						),
						'span' => array(
							'class' => true,
							'style' => true,
						),
					)
				);
				?>

				<?php if ( 'yes' === $enabled ) : ?>
					<span style="color: #15803d;">
						<span class="dashicons dashicons-shield-alt"></span>
						<?php esc_html_e( 'Additional protection enabled!', 'stripe' ); ?>
					</span>
				<?php else : ?>
					<span style="color: #b91c1c;">
						<span class="dashicons dashicons-shield"></span>
						<?php esc_html_e( 'Disabled — missing additional protection!', 'stripe' ); ?>
					</span>
				<?php endif; ?>
			</p>
		</div>

		<?php
	}

	/**
	 * Logs a fraud event on the charge.failed webhook.
	 *
	 * @since 4.6.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Event  $event Stripe Event object.
	 * @param \SimplePay\Vendor\Stripe\Charge $charge Stripe Charge object.
	 * @return void
	 */
	public function log_fraud_event( $event, $charge ) {
		/** @var array<string, int> $fraud_events */
		$fraud_events = get_option( 'simpay_fraud_events', array() );
		$timeframe    = $this->get_fraud_event_timeframe();

		// Nothing logged yet, create the initial state.
		if ( empty( $fraud_events ) ) {
			update_option(
				'simpay_fraud_events',
				array(
					'count'        => 1,
					'latest_event' => $event->created,
				)
			);

			// If the previous event occurred within the timeframe, increment the count.
		} elseif ( ( $event->created - $fraud_events['latest_event'] ) <= $timeframe ) {
			update_option(
				'simpay_fraud_events',
				array(
					'count'        => $fraud_events['count'] + 1,
					'latest_event' => $event->created,
				)
			);

			// If the previous event occurred outside the timeframe, reset the count.
		} elseif ( $event->created - $fraud_events['latest_event'] >= $timeframe ) {
			update_option(
				'simpay_fraud_events',
				array(
					'count'        => 1,
					'latest_event' => $event->created,
				)
			);
		}
	}

	/**
	 * Schedules a cleanup of the email verification codes.
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function schedule_email_verification_code_cleanup() {
		$this->scheduler->schedule_recurring(
			time(),
			( DAY_IN_SECONDS * 2 ), // every two days.
			'simpay_cleanup_email_verification_codes'
		);
	}

	/**
	 * Removes verification codes. This is heavy handed and just removes all
	 * codes to avoid the option from getting too large. It's possible that
	 * this will delete a code while someone is verifying their email, but...
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function cleanup_verification_codes() {
		update_option( 'simpay_email_verification_codes', array() );
	}

	/**
	 * Sends an email containing a verification code to the email address entered in
	 * the payment form and throws an exception to output the relevant HTML.
	 *
	 * @since 4.6.0
	 *
	 * @param array<mixed>                   $customer_args Customer arguments.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form instance.
	 * @param array<mixed>                   $form_data Payment Form state.
	 * @param array<mixed>                   $form_values Payment form values.
	 * @return void
	 */
	public function send_verification_code(
		$customer_args,
		$form,
		$form_data,
		$form_values
	) {
		// Do not show the input if a verification code has been submitted.
		// Verify the code instead.
		if ( isset( $form_values['simpay_email_verification_code'] ) ) {
			$this->verify_verification_code_rest(
				$customer_args,
				$form,
				$form_data,
				$form_values
			);

			return;
		}

		// Find the entered email address, and create a cleaned version to use as
		// the verification code base.
		/** @var string $email */
		$email = $form_values['simpay_email'];
		$email = sanitize_text_field( $email );
		$email = $this->clean_email( $email );

		$this->send_verification_code_email( $email );
	}

	/**
	 * Sends an email containing a verification code to the email address entered in
	 * the payment form and throws an exception to output the relevant HTML.
	 *
	 * @since 4.7.2
	 *
	 * @param \WP_REST_Request $request The payemnt request.
	 * @return void
	 */
	public function send_verification_code_upe( $request ) {
		// Do not show the input if a verification code has been submitted.
		// Verify the code instead.
		$form_values = PaymentRequestUtils::get_form_values( $request );

		if ( isset( $form_values['simpay_email_verification_code'] ) ) {
			$this->verify_verification_code_rest(
				array(),
				array(),
				array(),
				$form_values
			);

			return;
		}

		// Find the entered email address, and create a cleaned version to use as
		// the verification code base.
		/** @var string $email */
		$email = $form_values['simpay_email'];
		$email = sanitize_text_field( $email );
		$email = $this->clean_email( $email );

		$this->send_verification_code_email( $email );
	}

	/**
	 * Sends an email containing a verification code to the email address, and
	 * throws an exception containing an additional field to input the value.
	 *
	 * @since 4.7.2
	 *
	 * @param string $email Email address to send the verification code to.
	 * @return void
	 * @throws \Exception Input field to enter the verification code.
	 */
	private function send_verification_code_email( $email ) {
		// Create a verification code valid for a set lifespan.
		add_filter(
			'nonce_life',
			array( $this, 'get_verification_code_lifespan' )
		);

		$nonce = wp_create_nonce( 'simpay_verify_email_' . $email );

		remove_filter(
			'nonce_life',
			array( $this, 'get_verification_code_lifespan' )
		);

		// Add the verification code to the list of valid codes.
		/** @var array<string> $verification_codes */
		$verification_codes = get_option(
			'simpay_email_verification_codes',
			array()
		);

		$verification_code_exists = array_search(
			$nonce,
			$verification_codes,
			true
		);

		if ( false === $verification_code_exists ) {
			update_option(
				'simpay_email_verification_codes',
				array_merge( $verification_codes, array( $nonce ) )
			);
		}

		// Send an email containing the verification code.
		wp_mail(
			$email,
			sprintf(
				/* translators: Website name. */
				__( '🔐 Your Verification Code for %s', 'stripe' ),
				get_bloginfo( 'name' )
			),
			sprintf(
				/* translators: Verification code */
				__(
					"To verify your identity and complete your payment, please enter the following verification code in the payment form:\n\n%s",
					'stripe'
				),
				$nonce
			)
		);

		// Return the HTML.
		throw new Exception( $this->get_email_verification_input() );
	}

	/**
	 * Ensures a verification code is valid before proceeding with the following
	 * REST API routes:
	 *
	 * /wpsp/v2/customer
	 * /wpsp/v2/paymentintent
	 * /wpsp/v2/subscription
	 * /wpsp/v2/setupintent
	 * /wpsp/v2/charge
	 *
	 * @since 4.6.0
	 *
	 * @param array<mixed>                                $args Object arguments.
	 * @param array<mixed>|\SimplePay\Core\Abstracts\Form $form Payment Form instance.
	 * @param array<mixed>                                $form_data Payment Form state.
	 * @param array<mixed>                                $form_values Payment form values.
	 * @throws \Exception If the verification code is invalid.
	 * @return void
	 */
	public function verify_verification_code_rest( $args, $form, $form_data, $form_values ) {
		if ( ! isset( $form_values['simpay_email_verification_code'] ) ) {
			throw new Exception(
				__( 'Invalid request. Please try again.', 'stripe' ) .
				$this->get_email_verification_input()
			);
		}

		// Retrieve the verification code.
		/** @var string $verification_code */
		$verification_code = $form_values['simpay_email_verification_code'];
		$verification_code = sanitize_text_field( $verification_code );

		// ... and base email.
		/** @var string $email */
		$email       = $form_values['simpay_email'];
		$clean_email = $this->clean_email(
			sanitize_text_field( $email )
		);

		$verified = $this->verify_verification_code(
			$verification_code,
			$clean_email
		);

		// If the code is invalid, throw an error.
		if ( false === $verified ) {
			throw new Exception(
				__( 'Invalid verification code. Please try again.', 'stripe' ) .
				$this->get_email_verification_input()
			);
		}
	}

	/**
	 * Validates a verification code by checking the nonce against a list of
	 * valid codes.
	 *
	 * @since 4.6.0
	 *
	 * @param string $verification_code Verification code.
	 * @param string $email Email address.
	 * @return bool
	 */
	public function verify_verification_code( $verification_code, $email ) {
		// Validate the nonce.
		add_filter(
			'nonce_life',
			array( $this, 'get_verification_code_lifespan' )
		);

		$valid_nonce = wp_verify_nonce(
			$verification_code,
			'simpay_verify_email_' . $email
		);

		remove_filter(
			'nonce_life',
			array( $this, 'get_verification_code_lifespan' )
		);

		if ( false === $valid_nonce ) {
			return false;
		}

		// Ensure the verification code has not been previously used.
		/** @var array<string> $valid_verification_codes */
		$valid_verification_codes = get_option(
			'simpay_email_verification_codes',
			array()
		);

		$found_verification_code = array_search(
			$verification_code,
			$valid_verification_codes,
			true
		);

		return false !== $found_verification_code;
	}

	/**
	 * Removes a verification code from the list of valid codes after a payment
	 * action has been made.
	 *
	 * @since 4.6.0
	 *
	 * @param array<mixed>                   $args Object arguments.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form instance.
	 * @param array<mixed>                   $form_data Payment Form state.
	 * @param array<mixed>                   $form_values Payment form values.
	 * @throws \Exception If the verification code is not available.
	 * @return void
	 */
	public function remove_verification_code_rest( $args, $form, $form_data, $form_values ) {
		if ( ! isset( $form_values['simpay_email_verification_code'] ) ) {
			throw new Exception(
				__( 'Invalid request. Please try again.', 'stripe' ) .
				$this->get_email_verification_input()
			);
		}

		/** @var string $verification_code */
		$verification_code = $form_values['simpay_email_verification_code'];
		$verification_code = sanitize_text_field( $verification_code );

		$this->remove_verification_code( $verification_code );
	}

	/**
	 * Removes a verification code from the list of valid codes.
	 *
	 * @since 4.6.0
	 *
	 * @param string $verification_code Verification code to remove.
	 * @return void
	 */
	public function remove_verification_code( $verification_code ) {
		/** @var array<string> $valid_verification_codes */
		$valid_verification_codes = get_option(
			'simpay_email_verification_codes',
			array()
		);

		$found_verification_code = array_search(
			$verification_code,
			$valid_verification_codes,
			true
		);

		if ( false === $found_verification_code ) {
			return;
		}

		unset( $valid_verification_codes[ $found_verification_code ] );

		update_option(
			'simpay_email_verification_codes',
			$valid_verification_codes
		);
	}

	/**
	 * Adjusts the rate limiting ID to use the email address being submitted.
	 *
	 * Uses the "cleaned" email address to remove "dynamic" email addresses.
	 * spencer+123@gmail.com becomes spencer@gmail.com
	 *
	 * @since 4.6.0
	 *
	 * @param string           $id The rate limiting ID.
	 * @param \WP_REST_Request $request The REST API request.
	 * @return string
	 */
	public function set_rate_limiting_id( $id, $request ) {
		if ( ! empty( $request->get_param( 'form_values' ) ) ) {
			/** @var array<string, string> $form_values */
			$form_values = $request->get_param( 'form_values' );

			if ( ! isset( $form_values['simpay_email_verification_code'] ) ) {
				return $id;
			}

			$email = $form_values['simpay_email'];
		} else {
			if (
				! isset(
					$_POST['form_values'],
					$_POST['form_values']['simpay_email_verification_code']
				)
			) {
				return $id;
			}

			$email = $_POST['form_values']['simpay_email'];
		}

		return $this->clean_email( sanitize_text_field( $email ) );
	}

	/**
	 * Sets the rate limiting timeout to the same as the email verification timeframe.
	 *
	 * @since 4.6.0
	 *
	 * @return int|float
	 */
	public function set_rate_limiting_timeout() {
		return $this->get_fraud_event_timeframe();
	}

	/**
	 * Returns the lifespan of a verification code.
	 *
	 * @since 4.6.0
	 *
	 * @return int
	 */
	public function get_verification_code_lifespan() {
		return MINUTE_IN_SECONDS * 10;
	}

	/**
	 * Returns the timeframe (in seconds) for enabling email verification fraud
	 * protection.
	 *
	 * @since 4.6.0
	 *
	 * @return int|float
	 */
	private function get_fraud_event_timeframe() {
		/** @var int|float $timeframe */
		$timeframe = simpay_get_setting(
			'fraud_email_verification_timeframe',
			6
		);

		return $timeframe * HOUR_IN_SECONDS;
	}

	/**
	 * Returns the number of fraud events threshold for enabling email
	 * verification fraud protection.
	 *
	 * @since 4.6.0
	 *
	 * @return int
	 */
	private function get_fraud_event_threshold() {
		/** @var int $threshold */
		$threshold = simpay_get_setting(
			'fraud_email_verification_threshold',
			3
		);

		return $threshold;
	}

	/**
	 * Determines if the latest fraud event occured within the set timeframe.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	private function is_latest_fraud_event_in_timeframe() {
		/** @var array<string, int> $fraud_events */
		$fraud_events = get_option( 'simpay_fraud_events', array() );

		if ( empty( $fraud_events ) ) {
			return false;
		}

		$latest_event = $fraud_events['latest_event'];

		return time() - $latest_event <= $this->get_fraud_event_timeframe();
	}

	/**
	 * Returns the number of fraud events that have occured within the set
	 * timeframe.
	 *
	 * @since 4.6.0
	 *
	 * @return int
	 */
	private function get_fraud_event_count() {
		/** @var array<string, int> $fraud_events */
		$fraud_events = get_option( 'simpay_fraud_events', array() );

		if ( empty( $fraud_events ) ) {
			return 0;
		}

		return (int) $fraud_events['count'];
	}

	/**
	 * "Cleans" a "dynamic" email address by removing the "+" and everything after it.
	 *
	 * @since 4.6.0
	 *
	 * @param string $email Email address to clean.
	 * @return string
	 */
	private function clean_email( $email ) {
		$parts = explode( '@', $email );

		// Remove + and following characters.
		$parts[0] = preg_replace( '/\+.*/', '', $parts[0] );

		return trim( implode( '@', $parts ) );
	}

	/**
	 * Returns the email verification field markup.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	private function get_email_verification_input() {
		return sprintf(
			'<div class="simpay-form-control simpay-email-verification-code-container" style="">
				<div style="margin-bottom: 10px;">
					%2$s
				</div>
				<div class="simpay-email-verification-code-label">
					<label for="simpay_email_verification_code" class="screen-reader-text">%3$s</label>
				</div>
				<div class="simpay-verification-code-wrap simpay-field-wrap"">
					<input type="text" id="simpay_email_verification_code" name="simpay_email_verification_code" value="" placeholder="••••••••••" />
				</div>
			</div>
			<script id="simpay-email-verification-script-%1$s">
				jQuery( "#simpay-email-verification-script-%1$s" )
					.parents( ".simpay-checkout-form" )
					.find( "> .simpay-email-verification-code-container" )
					.remove();

				var input = jQuery( "#simpay-email-verification-script-%1$s" )
					.parents( ".simpay-errors" )
					.find( ".simpay-email-verification-code-container" )
					.detach();

				var submitButtonEl = jQuery( "#simpay-email-verification-script-%1$s" )
					.parents( ".simpay-checkout-form" )
					.find( ".simpay-checkout-btn-container" );

				if ( submitButtonEl.length !== 0 ) {
					submitButtonEl.before( input );
				} else {
					jQuery( "#simpay-email-verification-script-%1$s" )
						.parents( ".simpay-checkout-form" )
						.find( ".simpay-payment-btn" )
						.parent()
						.before( input );
				}
			</script>',
			wp_rand(),
			sprintf(
				'<div style="font-size: 28px;">⚠️</div><strong style="display: block; margin: 10px 0 5px;">%s</strong>%s',
				esc_html__(
					'Your payment has not been processed!',
					'stripe'
				),
				esc_html__(
					'Additional verification is required. Please enter the verification code sent to your email address and resubmit to complete your payment.',
					'stripe'
				)
			),
			esc_html__( 'Verification Code', 'stripe' )
		);
	}

}
