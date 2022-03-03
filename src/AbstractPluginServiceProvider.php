<?php
/**
 * Plugin: Service provider abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core;

use SimplePay\Vendor\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * AbstractPluginServiceProvider class.
 *
 * @since 4.4.0
 */
abstract class AbstractPluginServiceProvider extends AbstractServiceProvider {

	/**
	 * Works around Container 2.x looking in $this->provides property.
	 *
	 * Once we can use Container 4.x we can simply override ::provides().
	 *
	 * @todo Remove when using Container 4.x
	 * @since 4.4.0
	 *
	 * @param string $property Property name to retrieve.
	 * @return mixed
	 */
	public function __get( $property ) {
		switch ( $property ) {
			// Retrieves a merged list of services and subscribers.
			case 'provides':
				return $this->get_provides();
			default:
				return $this->$property;
		}
	}

	/**
	 * Returns a list of services and subscribers that are provided.
	 *
	 * @since 4.4.0
	 *
	 * @return string[]
	 */
	private function get_provides() {
		return array_merge(
			$this->get_services(),
			$this->get_subscribers()
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool|string[]
	 */
	public function provides( $id = null ) {
		$provides = $this->get_provides();

		// @todo Remove when using Container 4.x.
		if ( null === $id ) {
			return $provides;
		}

		return in_array( $id, $provides, true );
	}

	/**
	 * Returns a list of services the service provider provides.
	 *
	 * @since 4.4.0
	 *
	 * @return string[]
	 */
	abstract public function get_services();

	/**
	 * Returns a list of subscribers the service provider provides.
	 *
	 * A subscriber is a subset of a standard service that is automatically resolved
	 * during each request. These are used for the WordPress plugin API.
	 *
	 * @since 4.4.0
	 *
	 * @return string[]
	 */
	abstract public function get_subscribers();

	/**
	 * Returns a list of child service providers.
	 *
	 * @since 4.4.3
	 *
	 * @return array<\SimplePay\Core\AbstractPluginServiceProvider>
	 */
	public function get_service_providers() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	abstract public function register();

}
