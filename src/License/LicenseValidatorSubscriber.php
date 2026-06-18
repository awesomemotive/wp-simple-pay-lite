<?php
/**
 * License: Validator
 *
 * Periodically check the license key to make sure it's still valid.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\License;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Scheduler\SchedulerInterface;

/**
 * LicenseValidatorSubscriber class.
 *
 * @since 4.4.5
 */
class LicenseValidatorSubscriber implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Hook used by the scheduled, non-admin license check.
	 *
	 * @since 4.17.3
	 */
	const SCHEDULED_HOOK = '__unstable_simpay_validate_license';

	/**
	 * License management.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\License\LicenseManager
	 */
	private $manager;

	/**
	 * Scheduler.
	 *
	 * @since 4.17.3
	 * @var \SimplePay\Core\Scheduler\SchedulerInterface
	 */
	private $scheduler;

	/**
	 * LicenseValidatorSubscriber.
	 *
	 * @since 4.4.5
	 * @since 4.17.3 Added the `$scheduler` argument.
	 *
	 * @param \SimplePay\Core\License\LicenseManager       $manager License manager.
	 * @param \SimplePay\Core\Scheduler\SchedulerInterface $scheduler Scheduler.
	 * @return void
	 */
	public function __construct( $manager, SchedulerInterface $scheduler ) {
		$this->manager   = $manager;
		$this->scheduler = $scheduler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'admin_init'           => 'validate_license',
			'init'                 => 'schedule_validation_check',
			self::SCHEDULED_HOOK   => 'validate_license',
		);
	}

	/**
	 * Schedules a recurring license validation check.
	 *
	 * The license cache is otherwise only refreshed on `admin_init`. If the site
	 * admin never loads wp-admin (e.g. on sites managed entirely via email),
	 * `simpay_license_data` can stay stuck on an old `expired` value after a
	 * renewal, which then bleeds into outbound emails. Scheduling a daily
	 * Action Scheduler job keeps the cache fresh independent of wp-admin
	 * traffic. The job is a no-op when `simpay_license_next_check` is still
	 * in the future, so this does not bypass the existing rate limit.
	 *
	 * @since 4.17.3
	 *
	 * @return void
	 */
	public function schedule_validation_check() {
		$this->scheduler->schedule_recurring(
			time() + DAY_IN_SECONDS,
			DAY_IN_SECONDS,
			self::SCHEDULED_HOOK
		);
	}

	/**
	 * Validates an existing license key.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function validate_license() {
		$simpay_license_next_check = get_option( 'simpay_license_next_check', false );

		if (
			is_numeric( $simpay_license_next_check ) &&
			( $simpay_license_next_check > current_time( 'timestamp' ) )
		) {
			return;
		}

		$key = $this->license->get_key();

		if ( empty( $key ) ) {
			return;
		}

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $key,
			'item_id'    => SIMPLE_PAY_ITEM_ID, // @phpstan-ignore-line
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post(
			SIMPLE_PAY_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) ) {
			update_option(
				'simpay_license_next_check',
				current_time( 'timestamp' ) + ( HOUR_IN_SECONDS * 2 )
			);

			return;
		}

		// Decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		/** @var \stdClass $license_data */

		// Ensure license is activated.
		if ( isset( $license_data->license ) ) {
			$this->manager->activate( $key );
		}

		update_option(
			'simpay_license_next_check',
			current_time( 'timestamp' ) + DAY_IN_SECONDS
		);
	}

}
