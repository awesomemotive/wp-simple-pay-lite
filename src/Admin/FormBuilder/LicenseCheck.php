<?php
/**
 * Form builder: License check
 *
 * Ensures a license key has been entered, and is valid, before allowing payment
 * forms to be created or edited. If the license key is expired there is a two week
 * grace period before preventing edits.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Admin\FormBuilder;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Settings;

/**
 * LicenseCheck class.
 *
 * @since 4.4.6
 */
class LicenseCheck implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if (
			true === $this->license->is_lite() ||
			true === $this->license->is_valid()
		) {
			return array();
		}

		return array(
			'add_meta_boxes' => 'maybe_remove_meta_boxes',
			'edit_form_top'  => 'maybe_show_license_notice',
		);
	}

	/**
	 * Shows a license notice if the license is missing, invalid, or expired.
	 *
	 * @since 4.4.6
	 *
	 * @return void
	 */
	public function maybe_show_license_notice() {
		$adding = (
			isset( $_GET['post_type'] ) &&
			'simple-pay' === sanitize_text_field( $_GET['post_type'] )
		);

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$editing = 'simple-pay' === get_post_type( $post_id );

		// Not adding or editing.
		if ( ! ( $adding || $editing ) ) {
			return;
		}

		$is_missing = empty( $this->license->get_key() );
		$installed  = get_option( 'simpay_installed', time() );

		// If the license key has not been entered, give a 24 hour grace period.
		if ( $is_missing && ( time() - $installed < ( HOUR_IN_SECONDS * 24 ) ) ) {
			return;
		}

		$license_url = Settings\get_url(
			array(
				'section'    => 'general',
				'subsection' => 'license',
			)
		);

		$renew_url = simpay_ga_url(
			'https://wpsimplepay.com/my-account/licenses/',
			'payment-form-license-renewal'
		);

		$learn_more_url = simpay_ga_url(
			'https://wpsimplepay.com/lite-vs-pro/',
			'payment-form-license-renewal'
		);

		// License is missing.
		if ( $is_missing ) {
			include_once SIMPLE_PAY_DIR . '/views/admin-payment-forms-license-missing.php'; // @phpstan-ignore-line
			return;
		}

		switch ( $this->license->get_status() ) {
			// License is expired...
			case 'expired':
				// ...and not in the grace period.
				if ( ! $this->license->is_in_grace_period() ) {
					$action = isset( $_GET['action'] ) && 'edit' === $_GET['action']
						? _x( 'Editing', 'payment form action', 'stripe' )
						: _x( 'Creation', 'payment form action', 'stripe' );

					include_once SIMPLE_PAY_DIR . '/views/admin-payment-forms-license-expired.php'; // @phpstan-ignore-line
					return;
				}
				break;

			// License is invalid for some other reason, treat it as missing.
			case 'invalid':
				include_once SIMPLE_PAY_DIR . '/views/admin-payment-forms-license-missing.php'; // @phpstan-ignore-line
				return;
		}
	}

	/**
	 * Removes all metaboxes from the page if a license is missing or expired.
	 *
	 * @since 4.4.6
	 *
	 * @return void
	 */
	public function maybe_remove_meta_boxes() {
		$remove = false;

		switch ( $this->license->get_status() ) {
			case 'expired':
				if ( ! $this->license->is_in_grace_period() ) {
					$remove = true;
				}

				break;
			case 'invalid':
				$remove = true;
				break;
		}

		$is_missing = empty( $this->license->get_key() );
		$installed  = get_option( 'simpay_installed', time() );

		// If the license key has not been entered, give a 24 hour grace period.
		if ( $is_missing && ! ( time() - $installed < ( HOUR_IN_SECONDS * 24 ) ) ) {
			$remove = true;
		}

		if ( ! $remove ) {
			return;
		}

		global $wp_meta_boxes;

		foreach ( $wp_meta_boxes['simple-pay'] as $context_id => $contexts ) {
			foreach ( $contexts as $priorities ) {
				foreach ( $priorities as $metabox ) {
					if ( ! $metabox ) {
						continue;
					}

					remove_meta_box( $metabox['id'], 'simple-pay', $context_id );
				}
			}
		}
	}

}
