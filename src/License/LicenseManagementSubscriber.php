<?php
/**
 * License: Management subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\License;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Settings;

/**
 * LicenseManagementSubscriber class.
 *
 * @since 4.4.5
 */
class LicenseManagementSubscriber implements SubscriberInterface {

	/**
	 * License management.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\License\LicenseManager
	 */
	private $manager;

	/**
	 * LicenseAjaxSubscriber.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\License\LicenseManager $manager License manager.
	 * @return void
	 */
	public function __construct( $manager ) {
		$this->manager = $manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			// Standard admin requests.
			'admin_init'                        => array(
				array( 'request_activate' ),
				array( 'request_deactivate' ),
			),

			// AJAX admin requests.
			'wp_ajax_simpay_activate_license'   => 'ajax_activate',
			'wp_ajax_simpay_deactivate_license' => 'ajax_deactivate',
		);
	}

	/**
	 * Activates a plugin license via a standard request.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function request_activate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset(
			$_REQUEST['simpay-action'],
			$_REQUEST['simpay-license-key'],
			$_REQUEST['simpay-license-nonce']
		) ) {
			return;
		}

		$action = sanitize_text_field( $_REQUEST['simpay-action'] );

		if ( 'simpay-activate-license' !== $action ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['simpay-license-nonce'], 'simpay-manage-license' ) ) {
			return;
		}

		$this->manager->activate(
			trim(
				sanitize_text_field( $_REQUEST['simpay-license-key'] )
			)
		);

		// Redirect back to the settings page (removing query arguments) when refreshing.
		if ( isset( $_REQUEST['simpay-license-refresh'] ) ) {
			wp_safe_redirect( $this->get_license_setting_url() );
			exit;
		}
	}

	/**
	 * Deactivates a plugin license via a standard request.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function request_deactivate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset(
			$_REQUEST['simpay-action'],
			$_REQUEST['simpay-license-key'],
			$_REQUEST['simpay-license-nonce']
		) ) {
			return;
		}

		$action = sanitize_text_field( $_REQUEST['simpay-action'] );

		if ( 'simpay-deactivate-license' !== $action ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['simpay-license-nonce'], 'simpay-manage-license' ) ) {
			return;
		}

		$this->manager->deactivate(
			trim(
				sanitize_text_field( $_REQUEST['simpay-license-key'] )
			)
		);
	}

	/**
	 * Activates a plugin license via an AJAX request.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function ajax_activate() {
		$unknown_error = array(
			'message' => esc_html__(
				'An unknown error has occurred. Please try again.',
				'stripe'
			),
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $unknown_error );
		}

		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( $_POST['nonce'], 'simpay-manage-license' )
		) {
			wp_send_json_error( $unknown_error );
		}

		$license = sanitize_text_field( $_POST['license'] );
		$license = $this->manager->activate( $license );

		if ( false === $license ) {
			wp_send_json_error( $unknown_error );
		}

		/** @var \SimplePay\Core\License\License $license */

		if ( true === $license->is_valid() ) {
			wp_send_json_success(
				array(
					'message' => esc_html__(
						'License activated.',
						'stripe'
					),
					'license' => $license->to_array(),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Unable to activate license.',
						'stripe'
					),
					'license' => $license->to_array(),
				)
			);
		}
	}

	/**
	 * Deactivates a plugin license via an AJAX request.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function ajax_deactivate() {
		$unknown_error = array(
			'message' => esc_html__(
				'An unknown error has occurred. Please try again.',
				'stripe'
			),
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( $unknown_error );
		}

		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( $_POST['nonce'], 'simpay-manage-license' )
		) {
			wp_send_json_error( $unknown_error );
		}

		$license = sanitize_text_field( $_POST['license'] );
		$license = $this->manager->deactivate( $license );

		if ( false === $license ) {
			wp_send_json_error( $unknown_error );
		}

		/** @var \SimplePay\Core\License\License $license */

		if ( false === $license->is_valid() ) {
			wp_send_json_success(
				array(
					'message' => esc_html__(
						'License deactivated.',
						'stripe'
					),
					'license' => $license->to_array(),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Unable to deactivate license.',
						'stripe'
					),
					'license' => $license->to_array(),
				)
			);
		}
	}

	/**
	 * Returns the URL to the license settings page.
	 *
	 * @since 4.4.5
	 *
	 * @return string
	 */
	private function get_license_setting_url() {
		return Settings\get_url(
			array(
				'section'    => 'general',
				'subsection' => 'license',
			)
		);
	}

}
