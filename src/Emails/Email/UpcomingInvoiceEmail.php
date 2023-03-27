<?php
/**
 * Emails: Upcoming Invoice
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
 * UpcomingInvoiceEmail class.
 *
 * @since 4.7.3
 */
class UpcomingInvoiceEmail extends AbstractEmail {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'upcoming-invoice';
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
		return __( 'Upcoming Invoice', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Send an email to the customer for upcoming invoice payments',
			'stripe'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_licenses() {
		return array(
			'plus',
			'professional',
			'ultimate',
			'elite',
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
			esc_html(
				sprintf(
				/* translators: %s Site name */
					__( 'Upcoming Invoice for %s', 'stripe' ),
					get_bloginfo( 'name' )
				)
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
		return Settings\Emails\UpcomingInvoice\get_body_setting_or_default();
	}

}
