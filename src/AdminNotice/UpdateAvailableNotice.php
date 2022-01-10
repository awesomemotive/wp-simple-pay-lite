<?php
/**
 * Admin notice: Update available
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\AdminNotice;

/**
 * UpdateAvailableNotice class.
 *
 * @since 4.4.1
 */
class UpdateAvailableNotice extends AbstractAdminNotice {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		/** @var \stdClass $current */
		$current = get_site_transient( 'update_plugins' );

		if (
			isset( $current->response ) &&
			isset( $current->response[ $this->get_plugin_file() ] )
		) {
			$version = $current->response[ $this->get_plugin_file() ]->new_version;

			// Provide a fallback even though it will not show unless there is an update.
		} else {
			$version = SIMPLE_PAY_VERSION; // @phpstan-ignore-line
		}

		return 'update-available-' . $version;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_view() {
		return SIMPLE_PAY_DIR . 'views/admin-notice-update-available.php'; // @phpstan-ignore-line
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type() {
		return 'info';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_dismissible() {
		return true;
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
		// Current user cannot update plugins, show nothing.
		if ( ! current_user_can( 'update_plugins' ) ) {
			return false;
		}

		// Plugin update is not available, show nothing.
		/** @var \stdClass $current */
		$current = get_site_transient( 'update_plugins' );

		if ( ! (
			isset( $current->response ) &&
			isset( $current->response[ $this->get_plugin_file() ] )
		) ) {
			return false;
		}

		// On the plugin update page, show nothing (transient hasn't been cleared yet).
		if ( isset( $_GET['action'] ) && 'upgrade-plugin' === $_GET['action'] ) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_data() {
		$file       = $this->get_plugin_file();
		$update_url = wp_nonce_url(
			self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file,
			'upgrade-plugin_' . $file
		);

		return array(
			'update_url' => $update_url,
		);
	}

	/**
	 * Returns the plugin file name depending on which version in installed.
	 *
	 * @since 4.4.1
	 *
	 * @return string
	 */
	private function get_plugin_file() {
		if ( true === $this->license->is_lite() ) {
			return 'stripe/stripe-checkout.php';
		} else {
			return 'wp-simple-pay-pro-3/simple-pay.php';
		}
	}

}
