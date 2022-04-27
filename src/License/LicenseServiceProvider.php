<?php
/**
 * License: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\License;

use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Vendor\League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * LicenseServiceProvider class.
 *
 * @since 4.4.1
 */
class LicenseServiceProvider extends AbstractPluginServiceProvider implements BootableServiceProviderInterface {

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
		return array(
			'license-validator-subscriber',
			'license-management-subscriber',
			'license-setting-susbcriber',
			'license-notification-subscriber',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		$container = $this->getContainer();

		// License.
		$container->share( 'license', License::class )
			->withArgument( $this->get_license_key() );

		$inflector = $container->inflector( LicenseAwareInterface::class );

		if ( $inflector instanceof \SimplePay\Vendor\League\Container\Inflector\Inflector ) {
			$inflector->invokeMethod(
				'set_license',
				array( $this->container->get( 'license' ) )
			);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Activate/deactivate helper.
		$container->share( 'license-manager', LicenseManager::class );

		// Daily license check.
		$container->share(
			'license-validator-subscriber',
			LicenseValidatorSubscriber::class
		)
			->withArgument( $container->get( 'license-manager' ) );

		// AJAX activate/deactivate subscriber.
		$container->share(
			'license-management-subscriber',
			LicenseManagementSubscriber::class
		)
			->withArgument( $container->get( 'license-manager' ) );

		// Setting UI subscriber.
		$container->share(
			'license-setting-susbcriber',
			LicenseSettingSubscriber::class
		);

		// Notification inbox subscriber.
		$container->share(
			'license-notification-subscriber',
			LicenseNotificationSubscriber::class
		);
	}

	/**
	 * Returns the install's license key; set via constant or option.
	 *
	 * @since 4.4.1
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
