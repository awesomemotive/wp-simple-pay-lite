<?php
/**
 * Telemetry: Integrations/plugins
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\CustomerSuccess\TelemetryData;

/**
 * IntegrationTelemetryData class.
 *
 * @since 4.7.10
 */
class IntegrationTelemetryData extends AbstractTelemetryData {

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		$data = array();

		foreach ( $this->get_all_plugins() as $basename => $details ) {
			if ( ! is_plugin_active( $basename ) ) {
				continue;
			}

			/** @var array{Name: string, Version: string} $details */

			$data[] = array(
				'name'    => $details['Name'],
				'version' => $details['Version'],
			);
		}

		return $data;
	}

	/**
	 * Gets all plugins on the site.
	 *
	 * @since 4.7.10
	 *
	 * @return array<string, mixed>
	 */
	private function get_all_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return get_plugins();
	}

}
