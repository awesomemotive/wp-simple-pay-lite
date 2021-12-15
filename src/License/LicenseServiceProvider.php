<?php
/**
 * License: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\License;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * LicenseServiceProvider class.
 *
 * @since 4.4.0
 */
class LicenseServiceProvider extends AbstractPluginServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'license',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// License.
		$container->share( 'license', License::class )
			->withArgument( $this->get_license_key() );
	}

	/**
	 * Returns the install's license key; set via constant or option.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	private function get_license_key() {
		if ( true === defined( 'SIMPLE_PAY_LICENSE_KEY' ) ) {
			$key = SIMPLE_PAY_LICENSE_KEY;
		} else {
			$key = get_option( 'simpay_license_key', '' );
		}

		/** @var string $key */
		return trim( $key );
	}

}
