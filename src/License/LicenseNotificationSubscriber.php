<?php
/**
 * License: Notification subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.x.x
 */

namespace SimplePay\Core\License;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\NotificationInbox\Notification;
use SimplePay\Core\NotificationInbox\NotificationAwareInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareTrait;
use SimplePay\Core\Settings;

/**
 * LicenseNotificationSubscriber class.
 *
 * @since 4.x.x
 */
class LicenseNotificationSubscriber implements SubscriberInterface, LicenseAwareInterface, NotificationAwareInterface {

	use LicenseAwareTrait;
	use NotificationAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'admin_init'                            =>
				'add_missing_license_notification',
			'pre_update_option_simpay_license_data' =>
				'dismiss_missing_license_notification',
		);
	}

	/**
	 * Adds a notification to the inbox if a license is missing.
	 *
	 * @since 4.x.x
	 *
	 * @return void
	 */
	public function add_missing_license_notification() {
		if ( ! empty( $this->license->get_key() ) ) {
			return;
		}

		$this->notifications->restore(
			array(
				'type'           => 'info',
				'source'         => 'internal',
				'title'          => __(
					'Activate WP Simple Pay Pro',
					'stripe'
				),
				'slug'           => 'missing-license',
				'content'        => __(
					'Enter and activate your license key to enable automatic updates.',
					'stripe'
				),
				'actions'        => array(
					array(
						'type' => 'primary',
						'text' => __( 'Add License Key', 'stripe' ),
						'url'  => Settings\get_url(
							array(
								'section'    => 'general',
								'subsection' => 'license',
							)
						)
					),
					array(
						'type' => 'secondary',
						'text' => __( 'Learn More', 'stripe' ),
						'url'  => 'https://docs.wpsimplepay.com/articles/activate-wp-simple-pay-pro-license/',
					),
				),
				'conditions'     => array(),
				'start'          => date( 'Y-m-d H:i:s', time() ),
				'end'            => date( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ),
				'is_dismissible' => false,
			)
		);
	}

	/**
	 * Dismisses the missing license notification when the license data is updated and valid.
	 *
	 * @since 4.x.x
	 *
	 * @param \stdClass $value New license data.
	 * @return \stdClass License data.
	 */
	public function dismiss_missing_license_notification( $value ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $value;
		}

		// Interacting with the license data object, not the License class.
		if ( 'valid' !== $value->license ) {
			return $value;
		}

		$this->notifications->dismiss( 'missing-license' );

		return $value;
	}

}
