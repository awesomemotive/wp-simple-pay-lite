<?php
/**
 * Emails: Manage Subscriptions
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.8.0
 */

namespace SimplePay\Core\Emails\Email;

use SimplePay\Pro\Settings;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ManageSubscriptionsEmail class
 *
 * @since 4.8.0
 */
class ManageSubscriptionsEmail extends AbstractEmail {
	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'manage-subscriptions';
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
	public function get_licenses() {
		return array(
			'plus',
			'professional',
			'ultimate',
			'elite',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Manage Subscriptions', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Allow customers to retrieve a password-free secure URL to update their subscription.',
			'stripe'
		);
	}

	/**
	 * Returns the subject of the email.
	 *
	 * @since 4.8.0
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
					__( 'Manage your subscription(s) for %s', 'stripe' ),
					get_bloginfo( 'name' )
				)
			)
		);

		return $subject;
	}

	/**
	 * Returns the body (content) of the email.
	 *
	 * @since 4.8.0
	 *
	 * @param string $subscriptions_managment_links list of subscriptions link.
	 * @return string
	 */
	public function get_body( $subscriptions_managment_links ) {
		return Settings\Emails\ManageSubscriptions\get_body_setting_or_default() . $subscriptions_managment_links;
	}
}
