<?php
/**
 * Telemetry: Customer journey
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\CustomerSuccess\TelemetryData;

use SimplePay\Core\CustomerSuccess\Achievement\FirstLivePayment;
use SimplePay\Core\CustomerSuccess\Achievement\GoLive;
use SimplePay\Core\CustomerSuccess\CustomerAchievements;

/**
 * CustomerJourneyTelemetryData class.
 *
 * @since 4.7.10
 */
class CustomerJourneyTelemetryData extends AbstractTelemetryData {

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		$start = get_option( 'simpay_customer_journey_start', false );

		if ( false === $start ) {
			return array();
		}

		/** @var string $start */

		// Order existing achievements based on when they were earned.
		/** @var array<string, int> $achievements */
		$achievements = get_option( CustomerAchievements::OPTION_NAME, array() );

		$journey = array(
			'start' => gmdate( 'Y-m-d H:i:s', (int) $start ),
		);

		// Determine how long it took for the achievement to be earned after
		// the customer journey started. Create a human readable version of the difference.
		foreach ( $achievements as $achievement_id => $achievement_time ) {
			/** @var int $achievement_time */
			$journey[ $achievement_id ] = gmdate( 'Y-m-d H:i:s', $achievement_time );
		}

		return $journey;
	}

}
