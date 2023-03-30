<?php
/**
 * Emails: Invoice confirmation
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails\Email;

use SimplePay\Pro\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * InvoiceConfirmationEmail class.
 *
 * @since 4.7.3
 */
class InvoiceConfirmationEmail extends AbstractEmail {

	/**
	 * License.
	 *
	 * This class is sometimes loaded outside of the plugin container
	 * (e.g when registering the email settings) so we cannot use LicenseAwareTrait.
	 *
	 * Instead we will set the license when the class is instantiated. Not great.
	 *
	 * @since 4.7.3
	 * @var \SimplePay\Core\License\License
	 */
	private $license;

	/**
	 * InvoiceConfirmationEmail.
	 *
	 * @since 4.7.3
	 */
	public function __construct() {
		$this->license = simpay_get_license();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'invoice-confirmation';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type() {
		return AbstractEmail::EXTERNAL_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Invoice Confirmation', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Send an email to the customer upon successful invoice payment.',
			'stripe'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_licenses() {
		$licenses = array(
			'professional',
			'ultimate',
			'elite',
		);

		if ( $this->license->is_enhanced_subscriptions_enabled() ) {
			array_unshift( $licenses, 'plus' );
		}

		return $licenses;
	}

	/**
	 * {@inheritdoc}
	 *
	 * Overrides the default determination and uses the Payment Receipt email
	 * enabled status as the fallback value.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (
			$this->license->is_enhanced_subscriptions_enabled() &&
			'yes' === simpay_get_setting(
				sprintf(
					'email_%s',
					$this->get_id()
				),
				simpay_get_setting( 'email_payment-confirmation', 'yes' )
			) &&
			self::is_available()
		);
	}

	/**
	 * Returns the email subject.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_subject() {
		/** @var string $subject */
		$subject = simpay_get_setting(
			sprintf( 'email_%s_subject', $this->get_id() ),
			sprintf(
				/* translators: %s Site name */
				__( 'Payment Received for %s', 'stripe' ),
				get_bloginfo( 'name' )
			)
		);

		return $subject;
	}

	/**
	 * Returns the body (content) of the email.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_body() {
		return Settings\Emails\InvoiceConfirmation\get_body_setting_or_default();
	}

}
