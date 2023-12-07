<?php
/**
 * Anti-Spam: Require Authentication
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
use SimplePay\Core\Settings;

/**
 * RequireAuthentication class.
 *
 * @since 4.6.0
 */
class RequireAuthentication implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		$subscribers = array(
			'simpay_register_settings' => 'add_settings',
		);

		if ( 'yes' === simpay_get_setting( 'fraud_require_authentication', 'no' ) ) {
			$actions = array(
				'simpay_before_customer_from_payment_form_request',
				'simpay_before_paymentintent_from_payment_form_request',
				'simpay_before_setupintent_from_payment_form_request',
				'simpay_before_subscription_from_payment_form_request',
				'simpay_before_charge_from_payment_form_request',
			);

			foreach ( $actions as $action ) {
				$subscribers[ $action ] = array( 'requre_authentication', 0 );
			}

			$subscribers['simpay_rate_limiting_id'] =
				array( 'set_rate_limiting_id', 10, 2 );
		}

		return $subscribers;
	}

	/**
	 * Adds the settings UI for requring a login for fraud prevention.
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
					'id'          => 'fraud_require_authentication',
					'section'     => 'general',
					'subsection'  => 'recaptcha',
					'label'       => esc_html_x(
						'Require User Authentication',
						'setting label',
						'stripe'
					),
					'input_label' => esc_html_x(
						'Require users to be logged in to submit on-site payments.',
						'setting input label',
						'stripe'
					),
					'description' => sprintf(
						'<p class="description>%s</p>',
						esc_html__(
							'On-site payments will not process for guests. Payment forms will not be hidden from guests, but they will not be able to submit the form.',
							'stripe'
						)
					),
					'value'       => simpay_get_setting(
						'fraud_require_authentication',
						'no'
					),
					'priority'    => 70,
					'schema'      => array(
						'type' => 'string',
						'enum' => array( 'yes', 'no' ),
					),
				)
			)
		);
	}

	/**
	 * Ensures a the user is logged in before continuing.
	 *
	 * @since 4.6.0
	 *
	 * @throws \Exception If the user is not logged in.
	 * @return void
	 */
	public function requre_authentication() {
		/**
		 * Filters the capability needed to submit a payment form when
		 * authentication is required.
		 *
		 * @since 4.6.0
		 *
		 * @param string $capability The capability needed to submit a payment form.
		 */
		$capability = apply_filters(
			'simpay_require_authentication_capability',
			'read'
		);

		if (
			is_user_logged_in() &&
			current_user_can( $capability )
		) {
			return;
		}

		throw new Exception(
			__( 'Please log in to make a payment.', 'stripe' )
		);
	}

	/**
	 * Updates the rate limiting ID to use the user ID if the user is logged in.
	 *
	 * @since 4.7.0
	 *
	 * @param string           $id The rate limiting ID.
	 * @param \WP_REST_Request $request The payment request.
	 * @return int|string The user ID if the user is logged in, otherwise the rate limiting ID.
	 */
	public function set_rate_limiting_id( $id, $request ) {
		if ( is_user_logged_in() ) {
			$id = get_current_user_id();
		}

		return $id;
	}
}
