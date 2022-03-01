<?php
/**
 * Integration: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * IntegrationServiceProvider class.
 *
 * @since 4.4.3
 */
class IntegrationServiceProvider extends AbstractPluginServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array();
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
	public function get_service_providers() {
		$providers = array(
			new Divi\DiviIntegration,
			new Elementor\ElementorIntegration,
		);

		return array_filter(
			$providers,
			function( $integration ) {
				return $integration->is_active();
			}
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		// Nothing to register.
	}

}
