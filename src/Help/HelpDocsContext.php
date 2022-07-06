<?php
/**
 * Admin help: Documentation context
 *
 * Helps return relevant documentation for the current context.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Help;

use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Settings;

/**
 * HelpDocsContext class.
 *
 * @since 4.4.6
 */
class HelpDocsContext implements LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Returns all documentation articles.
	 *
	 * @since 4.4.6
	 *
	 * @return array<array<string, string>>
	 */
	public function get_all_docs() {
		$cache_dir  = $this->get_cache_dir();
		$cache_file = $cache_dir . 'wpsp-docs.json';

		if ( ! file_exists( $cache_file ) ) {
			return array();
		}

		$cache_file_contents = file_get_contents( $cache_file );

		if ( false === $cache_file_contents ) {
			return array();
		}

		$docs = json_decode( $cache_file_contents, true );

		if ( ! is_array( $docs ) ) {
			return array();
		}

		return $docs;
	}

	/**
	 * Returns a list of documentation categories.
	 *
	 * @since 4.4.6
	 *
	 * @return array<string, string>
	 */
	public function get_search_categories() {
		return array(
			'getting-started'  => __( 'Getting Started', 'stripe' ),
			'common-problems'  => __( 'Common Problems', 'stripe' ),
			'stripe-dashboard' => __( 'Stripe Dashboard', 'stripe' ),
			'integrations'     => __( 'Integrations', 'stripe' ),
			'faqs'             => __( 'Frequently Asked Questions', 'stripe' ),
			'functionality'    => __( 'Functionality', 'stripe' ),
			'payment-methods'  => __( 'Payment Methods', 'stripe' ),
			'walkthroughs'     => __( 'Walkthroughs', 'stripe' ),
		);
	}

	/**
	 * Returns a search term for the given context.
	 *
	 * @return string
	 */
	public function get_search_term() {
		$context = $this->get_context();

		$map = array(
			// Coupons.
			'coupons'                                     => 'coupons',
			'coupons/add'                                 => 'add coupons',

			// Settings.
			'settings/general/license'                    => 'activate license',
			'settings/general/currency'                   => 'currency locale',
			'settings/general/taxes'                      => 'taxes',
			'settings/general/recaptcha'                  => 'recaptcha',
			'settings/stripe/account'                     => 'stripe account',
			'settings/stripe/locale'                      => 'currency locale',
			'settings/stripe/webhooks'                    => 'webhooks',
			'settings/payment-confirmations/pages'        => 'confirmation',
			'settings/payment-confirmations/one-time'     => 'confirmation',
			'settings/payment-confirmations/subscription' => 'confirmation',
			'settings/payment-confirmations/subscription-with-trial' =>
				'confirmation',
			'settings/emails/general'                     => 'email settings',
			'settings/emails/payment-confirmation'        => 'email settings',
			'settings/emails/payment-notification'        => 'email settings',
			'settings/emails/upcoming-invoice'            => 'email settings',
			'settings/emails/invoice-confirmation'        => 'email settings',
			'settings/customers/subscription-management'  => 'subscription management',

			// Form builder.
			'form-builder/add'                            => 'first form',
			'form-builder/edit'                           => 'form settings',
		);

		return isset( $map[ $context ] ) ? $map[ $context ] : '';
	}

	/**
	 * Returns the current page context.
	 *
	 * @since 4.4.6
	 *
	 * @return string
	 */
	private function get_context() {
		$screen  = null;
		$context = '';

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
		}

		if ( null !== $screen ) {
			switch ( $screen->base ) {
				case 'simple-pay_page_simpay_coupons':
					$context = $this->get_coupon_context();
					break;
				case 'simple-pay_page_simpay_settings':
					$context = $this->get_settings_context();
					break;
			}

			switch ( $screen->id ) {
				case 'edit-simple-pay':
					$context = 'form-builder/list';
					break;
				case 'simple-pay':
					$context = isset( $_GET['action'] ) && 'edit' === $_GET['action']
						? 'form-builder/edit'
						: 'form-builder/add';
					break;
			}
		}

		return $context;
	}

	/**
	 * Returns context for coupons.
	 *
	 * @return string
	 */
	private function get_coupon_context() {
		$context = 'coupons';

		if (
			isset( $_GET['simpay-action'] ) &&
			'add-coupon' === $_GET['simpay-action']
		) {
			$context = 'coupons/add';
		}

		return $context;
	}

	/**
	 * Returns context for settings.
	 *
	 * @return string
	 */
	private function get_settings_context() {
		$context = 'settings';

		// Default tab.
		if ( ! isset( $_GET['tab'] ) ) {
			if ( $this->license->is_lite() ) {
				$context = 'settings/general/currency';
			} else {
				$context = 'settings/general/license';
			}
		} else {
			$section    = sanitize_text_field( $_GET['tab'] );
			$subsection = isset( $_GET['subsection'] )
				? sanitize_text_field( $_GET['subsection'] )
				: '';

			if ( empty( $subsection ) ) {
				$subsection = Settings\get_main_subsection_id( $section );
			}

			$context = "settings/$section/$subsection";
		}

		return $context;
	}

	/**
	 * Returns the path to the cache directory.
	 *
	 * @since 4.4.6
	 *
	 * @return string
	 */
	private function get_cache_dir() {
		$upload_dir = wp_upload_dir();

		return trailingslashit( $upload_dir['basedir'] );
	}

}
