<?php
/**
 * Admin notice: License missing
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\AdminNotice;

use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Settings;

/**
 * LicenseMissingNotice class.
 *
 * @since 4.4.1
 */
class LicenseMissingNotice extends AbstractAdminNotice implements LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'missing-license';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type() {
		return 'error';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_dismissible() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_dismissal_length() {
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function should_display() {
		// Lite, no license key.
		if ( true === $this->license->is_lite() ) {
			return false;
		}

		// License key is already set, show nothing.
		if ( ! empty( $this->license->get_key() ) ) {
			return false;
		}

		// Default settings screen.
		if (
			isset( $_GET['page'] ) &&
			! isset( $_GET['tab'] ) &&
			'simpay_settings' === sanitize_text_field( $_GET['page'] )
		) {
			return false;
		}

		// License settings screen.
		if (
			isset( $_GET['page'], $_GET['tab'], $_GET['subsection'] ) &&
			'general' === sanitize_text_field( $_GET['tab'] ) &&
			'license' === sanitize_text_field( $_GET['subsection'] ) &&
			'simpay_settings' === sanitize_text_field( $_GET['page'] )
		) {
			return false;
		}

		// Add or edit payment form (full page notice is shown instead).
		$screen = get_current_screen();

		if (
			null !== $screen &&
			'simple-pay' === $screen->id &&
			in_array( $screen->base, array( 'post', 'post-new' ), true )
		) {
			return false;
		}

		// Current user cannot update plugins, show nothing.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_data() {
		$license_url = Settings\get_url(
			array(
				'section'    => 'general',
				'subsection' => 'license',
			)
		);

		return array(
			'license_url' => $license_url,
		);
	}

}
