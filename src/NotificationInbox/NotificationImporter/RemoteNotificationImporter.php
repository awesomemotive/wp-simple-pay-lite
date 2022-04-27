<?php
/**
 * Notification inbox: Remote notification importer
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox\NotificationImporter;

use Exception;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use SimplePay\Core\NotificationInbox\NotificationRuleProcessor;
use SimplePay\Core\Scheduler\SchedulerInterface;
use stdClass;

/**
 * RemoteNotificationImporter class.
 *
 * @since 4.4.5
 */
class RemoteNotificationImporter extends AbstractNotificationImporter implements SubscriberInterface, NotificationImporterInterface {

	/**
	 * Remote API URL.
	 *
	 * @since 4.4.5
	 * @var string
	 */
	private $api_endpoint_url;

	/**
	 * Scheduler.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\Scheduler\SchedulerInterface
	 */
	private $scheduler;

	/**
	 * NotificationImporter.
	 *
	 * @since 4.4.5
	 *
	 * @param string                                       $api_endpoint_url Remote API endpoint URL.
	 * @param \SimplePay\Core\Scheduler\SchedulerInterface $scheduler Scheduler.
	 * @param \SimplePay\Core\NotificationInbox\NotificationRepository $notifications Notification repository.
	 * @param \SimplePay\Core\NotificationInbox\NotificationRuleProcessor $rule_processor Notification rule processor.
	 */
	public function __construct(
		$api_endpoint_url,
		SchedulerInterface $scheduler,
		NotificationRepository $notifications,
		NotificationRuleProcessor $rule_processor
	) {
		$this->api_endpoint_url = $api_endpoint_url;
		$this->scheduler        = $scheduler;

		parent::__construct( $notifications, $rule_processor );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'init' => 'schedule_imports',
			'__unstable_simpay_import_notification_inbox_notifications' =>
				'import',
		);
	}

	/**
	 * Schedules importing notification inbox notifications twice a day.
	 *
	 * @todo A single schedule should be setup that pulls any registered importers.
	 * Currently this is only used for the remote notification importer.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function schedule_imports() {
		$this->scheduler->schedule_recurring(
			time(),
			( DAY_IN_SECONDS / 2 ), // twicedaily
			'__unstable_simpay_import_notification_inbox_notifications'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_source() {
		$url = parse_url( $this->api_endpoint_url );

		if ( false !== $url && ! empty( $url['host']) ) {
			$source = $url['host'];
		} else {
			$source = 'unknown';
		}

		return $source;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fetch() {
		$request = wp_remote_get(
			$this->api_endpoint_url,
			array(
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $request ) ) {
			throw new Exception( $request->get_error_message() );
		}

		$response      = wp_remote_retrieve_body( $request );
		$notifications = ! empty( $response )
			? json_decode( $response, true )
			: array();

		if ( ! is_array( $notifications ) ) {
			return array();
		}

		$valid_notifications = array();

		/** @var array<array<mixed>> $notifications */
		foreach ( $notifications as $notification_data ) {
			$notification = $this->parse_notification( $notification_data );

			if ( false === $this->can_import_notification( $notification ) ) {
				continue;
			}

			$valid_notifications[] = $notification;
		}

		return $valid_notifications;
	}

}
