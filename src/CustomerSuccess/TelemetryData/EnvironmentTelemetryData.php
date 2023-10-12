<?php
/**
 * Telemetry: Environment
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\CustomerSuccess\TelemetryData;

/**
 * EnvironmentTelemetryData class.
 *
 * @since 4.7.10
 */
class EnvironmentTelemetryData extends AbstractTelemetryData {

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		return array_merge(
			array(
				'php_version' => phpversion(),
				'wp_version'  => $this->get_wp_version(),
				'sql_version' => $this->get_sql_version(),
			),
			$this->parse_server(),
			array(
				'is_ssl'       => (bool) is_ssl(),
				'locale'       => get_locale(),
				'active_theme' => $this->get_active_theme(),
				'multisite'    => (bool) is_multisite(),
			)
		);
	}

	/**
	 * Adds the server data to the array of data.
	 *
	 * @since 4.7.10
	 *
	 * @return array<string, string>
	 */
	private function parse_server() {
		$server = ( isset( $_SERVER['SERVER_SOFTWARE'] )
			? $_SERVER['SERVER_SOFTWARE']
			: 'unknown' );

		$server = explode( '/', $server );

		$data = array(
			'server' => $server[0],
		);

		if ( isset( $server[1] ) ) {
			$data['server_version'] = $server[1];
		}

		return $data;
	}

	/**
	 * Gets the WordPress version.
	 *
	 * @since 4.7.10
	 *
	 * @return string
	 */
	private function get_wp_version() {
		$version = get_bloginfo( 'version' );
		$version = explode( '-', $version );

		return reset( $version );
	}

	/**
	 * Returns a semi-normalized version of the SQL version.
	 *
	 * @since 4.7.10
	 *
	 * @return string
	 */
	private function get_sql_version() {
		global $wpdb;

		$type    = $wpdb->db_server_info();
		$version = $wpdb->get_var( 'SELECT VERSION()' );

		if ( stristr( $type, 'mariadb' ) ) {
			$type = 'MariaDB';
		} else {
			$type = 'MySQL';
		}

		return $type . ' ' . $version;
	}

	/**
	 * Gets the active theme name.
	 *
	 * @since 4.7.10
	 *
	 * @return string
	 */
	private function get_active_theme() {
		return wp_get_theme()->name;
	}
}
