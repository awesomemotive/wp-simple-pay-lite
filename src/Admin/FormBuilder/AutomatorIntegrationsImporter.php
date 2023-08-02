<?php
/**
 * Form builder: Uncanny Automator integrations list importer
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.8
 */

namespace SimplePay\Core\Admin\FormBuilder;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Scheduler\SchedulerInterface;

/**
 * AutomatorIntegrationsImporter class.
 *
 * @since 4.7.8
 */
class AutomatorIntegrationsImporter implements SubscriberInterface {

	/**
	 * Remote API URL.
	 *
	 * @since 4.7.8
	 * @var string
	 */
	private $api_endpoint_url;

	/**
	 * Scheduler.
	 *
	 * @since 4.7.8
	 * @var \SimplePay\Core\Scheduler\SchedulerInterface
	 */
	private $scheduler;

	/**
	 * HelpDocsImporter.
	 *
	 * @since 4.7.8
	 *
	 * @param string                                       $api_endpoint_url Remote API endpoint URL.
	 * @param \SimplePay\Core\Scheduler\SchedulerInterface $scheduler Scheduler.
	 */
	public function __construct(
		$api_endpoint_url,
		SchedulerInterface $scheduler
	) {
		$this->api_endpoint_url = $api_endpoint_url;
		$this->scheduler        = $scheduler;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'init' => 'schedule_import',
			'__unstable_simpay_import_uncanny_automator_integrations' => 'import',
		);
	}

	/**
	 * Schedules importing help docs every two days.
	 *
	 * @since 4.7.8
	 *
	 * @return void
	 */
	public function schedule_import() {
		// Run an initial import.
		/** @var bool $imported */
		$imported = get_option( 'simpay_automator_integrations_imported', false );

		if ( ! $imported ) {
			update_option( 'simpay_automator_integrations_imported', true );

			$this->import();
		}

		// Schedule recurring imports.
		$this->scheduler->schedule_recurring(
			time(),
			( WEEK_IN_SECONDS * 2 ), // every two weeks.
			'__unstable_simpay_import_uncanny_automator_integrations'
		);
	}

	/**
	 * Imports documentation articles to local JSON file.
	 *
	 * @return void
	 */
	public function import() {
		$request = wp_remote_get(
			$this->api_endpoint_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $request ) ) {
			return;
		}

		$response = wp_remote_retrieve_body( $request );
		$docs     = ! empty( $response )
			? json_decode( $response, true )
			: array();

		// JSON could not be decoded.
		if ( ! is_array( $docs ) ) {
			return;
		}

		$cache_dir  = $this->get_cache_dir();
		$cache_file = $cache_dir . 'wpsp-uncanny-automator-integrations.json';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		file_put_contents( $cache_file, $response );
	}

	/**
	 * Returns the path to the cache directory.
	 *
	 * @since 4.7.8
	 *
	 * @return string
	 */
	private function get_cache_dir() {
		$upload_dir = wp_upload_dir();

		return trailingslashit( $upload_dir['basedir'] );
	}

}
