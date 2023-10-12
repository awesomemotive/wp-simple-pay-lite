<?php
/**
 * Telemetry: Abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\CustomerSuccess\TelemetryData;

/**
 * AbstractTelemetryData class.
 *
 * @since 4.7.10
 */
abstract class AbstractTelemetryData {

	/**
	 * Retruns the telemetry data.
	 *
	 * @since 4.7.10
	 *
	 * @return array<int|string, mixed>
	 */
	abstract public function get();

}
