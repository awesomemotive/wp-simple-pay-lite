<?php
/**
 * Customer Success: Telemetry
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\CustomerSuccess;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Scheduler\SchedulerInterface;
use SimplePay\Core\Transaction\TransactionRepository;

/**
 * TelemetrySubscriber class.
 *
 * @since 4.7.10
 */
class TelemetrySubscriber implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * @since 4.7.10
	 * @var \SimplePay\Core\Scheduler\SchedulerInterface
	 */
	private $scheduler;

	/**
	 * TelemetrySubscriber
	 *
	 * @since 4.7.10
	 *
	 * @param \SimplePay\Core\Scheduler\SchedulerInterface $scheduler Scheduler.
	 */
	public function __construct( SchedulerInterface $scheduler ) {
		$this->scheduler = $scheduler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		// If Lite, only send if opted in.
		if ( true === $this->license->is_lite() ) {
			$opted_in = simpay_get_setting( 'usage_tracking_opt_in', 'no' );

			if ( 'no' === $opted_in ) {
				return array(
					'admin_init' => 'unschedule',
				);
			}
		}

		return array(
			'init'                  => 'schedule_send',
			'simpay_send_telemetry' => 'send',
		);
	}

	/**
	 * Unschedules sending telemetry data once a week, if it's scheduled.
	 *
	 * @since 4.7.10
	 *
	 * @return void
	 */
	public function unschedule() {
		if ( ! $this->scheduler->has_next( 'simpay_send_telemetry' ) ) {
			return;
		}

		$this->scheduler->unschedule( 'simpay_send_telemetry' );
	}

	/**
	 * Schedules sending telemetry data once a week.
	 *
	 * @since 4.7.10
	 *
	 * @return void
	 */
	public function schedule_send() {
		if ( $this->scheduler->has_next( 'simpay_send_telemetry' ) ) {
			return;
		}

		$this->scheduler->schedule_recurring(
			time() + rand( 0, WEEK_IN_SECONDS ),
			WEEK_IN_SECONDS,
			'simpay_send_telemetry'
		);
	}

	/**
	 * Sends telemetry data to the telemetry server.
	 *
	 * @since 4.7.10
	 *
	 * @return void
	 */
	public function send() {
		$data = array(
			'id'           => $this->get_id(),
			'env'          => ( new TelemetryData\EnvironmentTelemetryData() )->get(),
			'plugin'       => ( new TelemetryData\PluginTelemetryData( $this->license ) )->get(),
			'journey'      => ( new TelemetryData\CustomerJourneyTelemetryData() )->get(),
			'integrations' => ( new TelemetryData\IntegrationTelemetryData() )->get(),
			'stats'        => ( new TelemetryData\StatTelemetryData() )->get(),
			'transactions' => ( new TelemetryData\TransactionTelemetryData() )->get(),
			'forms'        => ( new TelemetryData\PaymentFormTelemetryData() )->get(),
		);

		wp_remote_post(
			'https://telemetry.wpsimplepay.com/v1/checkin/',
			array(
				'method'      => 'POST',
				'timeout'     => 8,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => false,
				'body'        => $data,
				'user-agent'  => 'WPSP/' . SIMPLE_PAY_VERSION . '; ' . $data['id'], // @phpstan-ignore-line
			)
		);

	}

	/**
	 * Gets the unique site ID.
	 *
	 * This is generated from the home URL and two random pieces of data
	 * to create a hashed site ID that anonymizes the site data.
	 *
	 * @since 4.7.10
	 *
	 * @return string
	 */
	private function get_id() {
		/** @var string $id */
		$id = get_option( 'simpay_telemetry_uuid', '' );

		if ( '' !== $id ) {
			return $id;
		}

		$home_url = get_home_url();
		$uuid     = wp_generate_uuid4();
		$today    = gmdate( 'now' );
		$id       = md5( $home_url . $uuid . $today );

		update_option( 'simpay_telemetry_uuid', $id, false );

		return $id;
	}

}
