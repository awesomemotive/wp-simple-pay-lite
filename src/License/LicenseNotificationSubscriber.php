<?php
/**
 * License: Notification subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\License;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareTrait;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use SimplePay\Core\Settings;

/**
 * LicenseNotificationSubscriber class.
 *
 * @since 4.4.5
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

		// Alert via Notification Inbox if availble. Global admin notice is shown as well.
		if ( ! $this->notifications instanceof NotificationRepository ) {
			return array();
		}

		return array(
			'init'                                  =>
				array(
					array( 'add_missing_license_notification', 0 ),
					array( 'add_expired_license_notification', 0 ),
				),
			'pre_update_option_simpay_license_data' =>
				'dismiss_license_notifications',
		);
	}

	/**
	 * Adds a notification to the inbox if a license is missing.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function add_missing_license_notification() {
		// License key is not empty.
		if ( ! empty( $this->license->get_key() ) ) {
			return;
		}

		$this->notifications->restore(
			array(
				'type'           => 'error',
				'source'         => 'internal',
				'title'          => __(
					'WP Simple Pay Pro is Not Fully Activated!',
					'stripe'
				),
				'slug'           => 'missing-license',
				'content'        => esc_html__(
					'Add your WP Simple Pay Pro license key to start creating payment forms, enable automatic updates, and complete activation.',
					'stripe'
				),
				'actions'        => array(
					array(
						'type' => 'primary',
						'text' => __( 'Complete Activation', 'stripe' ),
						'url'  => Settings\get_url(
							array(
								'section'    => 'general',
								'subsection' => 'license',
							)
						),
					),
					array(
						'type' => 'secondary',
						'text' => __( 'Learn More', 'stripe' ),
						'url'  => 'https://wpsimplepay.com/doc/activate-wp-simple-pay-pro-license/',
					),
				),
				'conditions'     => array(),
				'start'          => gmdate( 'Y-m-d H:i:s', time() ),
				'end'            => gmdate( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ),
				'is_dismissible' => false,
			),
			function() {
				$this->notifications->dismiss( 'expired-license' );
				$this->notifications->dismiss( 'expiring-license' );
			}
		);
	}

	/**
	 * Adds a notification to the inbox if a license is expired.
	 *
	 * @since 4.4.6
	 *
	 * @return void
	 */
	public function add_expired_license_notification() {
		// License key is empty, another notification is already showing.
		if ( empty( $this->license->get_key() ) ) {
			return;
		}

		// License key is not expired, another notification is already showing.
		if ( 'expired' !== $this->license->get_status() ) {
			return;
		}

		/** @var string $expiration */
		$expiration = $this->license->get_expiration();

		if ( $this->license->is_in_grace_period() ) {
			/** @var string $date_format */
			$date_format = get_option( 'date_format', 'Y-m-d' );

			$content = sprintf(
				/* translators: License extension date. */
				__(
					'We have extended WP Simple Pay Pro functionality until %s, at which point functionality will become limited. Renew your license to continue receiving automatic updates, technical support, and access to WP Simple Pay Pro features and functionality.',
					'stripe'
				),
				gmdate(
					$date_format,
					strtotime( $expiration ) + ( DAY_IN_SECONDS * 14 )
				)
			);
		} else {
			$content = __(
				'Renew your license to continue receiving automatic updates, technical support, and access to WP Simple Pay Pro features and functionality.',
				'stripe'
			);
		}

		$renew_url = add_query_arg(
			array(
				'edd_license_key' => $this->license->get_key(),
				'discount'        => 'SAVE50',
			),
			sprintf( '%s/checkout', untrailingslashit( SIMPLE_PAY_STORE_URL ) ) // @phpstan-ignore-line
		);

		$renew_url = simpay_ga_url( $renew_url, 'notification-inbox' );

		$this->notifications->restore(
			array(
				'type'           => 'error',
				'source'         => 'internal',
				'title'          => __(
					'[IMPORTANT] Your WP Simple Pay Pro License Has Expired!',
					'stripe'
				),
				'slug'           => 'expired-license',
				'content'        => $content,
				'actions'        => array(
					array(
						'type' => 'primary',
						'text' => __( 'Renew License for 50% Off!', 'stripe' ),
						'url'  => $renew_url,
					),
					array(
						'type' => 'secondary',
						'text' => __( 'Learn More', 'stripe' ),
						'url'  => simpay_docs_link(
							'',
							'what-happens-if-my-license-expires',
							'admin-notice-expired-license',
							true
						),
					),
				),
				'conditions'     => array(),
				'start'          => gmdate( 'Y-m-d H:i:s', time() ),
				'end'            => gmdate( 'Y-m-d H:i:s', time() + YEAR_IN_SECONDS ),
				'is_dismissible' => false,
			),
			function() {
				$this->notifications->dismiss( 'missing-license' );
				$this->notifications->dismiss( 'expiring-license' );
			}
		);
	}

	/**
	 * Dismisses all license notifications when the license data is updated.
	 * Allows individual notifications to restore themselves on the next page load.
	 *
	 * @since 4.4.6
	 *
	 * @param \stdClass $value New license data.
	 * @return \stdClass License data.
	 */
	public function dismiss_license_notifications( $value ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $value;
		}

		$this->notifications->dismiss( 'missing-license' );
		$this->notifications->dismiss( 'expiring-license' );
		$this->notifications->dismiss( 'expired-license' );

		return $value;
	}

}
