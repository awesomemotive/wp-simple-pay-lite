<?php
/**
 * Notification inbox: Notification importer abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox\NotificationImporter;

use Exception;
use SimplePay\Core\NotificationInbox\Notification;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use SimplePay\Core\NotificationInbox\NotificationRuleProcessor;

/**
 * AbstractNotificationImporter class.
 *
 * @since 4.4.5
 */
abstract class AbstractNotificationImporter implements NotificationImporterInterface {

	/**
	 * Notification repository.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\NotificationInbox\NotificationRepository
	 */
	protected $notifications;

	/**
	 * Notification rule processor.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\NotificationInbox\NotificationRuleProcessor
	 */
	protected $rule_processor;

	/**
	 * NotificationImporter.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\NotificationInbox\NotificationRepository $notifications Notification repository.
	 * @param \SimplePay\Core\NotificationInbox\NotificationRuleProcessor $rule_processor Notification rule processor.
	 */
	public function __construct(
		NotificationRepository $notifications,
		NotificationRuleProcessor $rule_processor
	) {
		$this->notifications  = $notifications;
		$this->rule_processor = $rule_processor;
	}

	/**
	 * {@inhertidoc}
	 */
	public function import() {
		try {
			$notifications = $this->fetch();

			foreach ( $notifications as $notification ) {
				if ( empty( $notification['remote_id'] ) ) {
					$this->notifications->add( $notification );
				} else {
					$existing_notification = $this->notifications->get_by(
						'remote_id',
						$notification['remote_id'] // @phpstan-ignore-line
					);

					if ( $existing_notification instanceof Notification ) {
						$this->notifications->update(
							$existing_notification->id,
							$notification
						);
					} else {
						$this->notifications->add( $notification );
					}
				}
			}
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	abstract public function fetch();

	/**
	 * Parses notification data returned from a fetch.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $notification_data Notification data.
	 * @return array<string, array<array<string>|string>|string> Parsed notification data.
	 */
	protected function parse_notification( $notification_data ) {
		$notification = array();

		/** @var string $remote_id */
		$remote_id = ! empty( $notification_data['id'] )
			? $notification_data['id']
			: '0';

		$notification['remote_id'] = sanitize_text_field( $remote_id );

		/** @var string $type */
		$type = ! empty( $notification_data['notification_type'] )
			? $notification_data['notification_type']
			: 'info';

		$notification['type'] = sanitize_text_field( $type );

		/** @var string $source */
		$source                 = $this->get_source();
		$notification['source'] = $source;

		/** @var string $title */
		$title = ! empty( $notification_data['title'] )
			? $notification_data['title']
			: '';

		$notification['title'] = esc_html( $title );

		/** @var string $slug */
		$slug = ! empty( $notification_data['slug'] )
			? $notification_data['slug']
			: $notification['title'];

		$notification['slug'] = sanitize_title( $slug );

		/** @var string $content */
		$content = ! empty( $notification_data['content'] )
			? $notification_data['content']
			: '';

		$notification['content'] = wp_kses_post( $content );

		/** @var array<array<string>> $actions */
		$actions = array();
		/** @var array<array<string>> $buttons */
		$buttons = ! empty( $notification_data['btns'] ) && is_array( $notification_data['btns'] )
			? $notification_data['btns']
			: array();

		foreach ( $buttons as $type => $btn ) {
			switch ( $type ) {
				case 'main':
					$button_type = 'primary';
					break;
				default:
					$button_type = 'secondary';
			}

			$actions[] = array(
				'type' => sanitize_text_field( $button_type ),
				'url'  => esc_url_raw( $btn['url'] ),
				'text' => esc_html( $btn['text'] ),
			);
		}

		$notification['actions'] = $actions;

		/** @var array<string> $rules */
		$rules = ! empty( $notification_data['type'] ) && is_array( $notification_data['type'] )
			? $notification_data['type']
			: array();

		$notification['conditions'] = array_map(
			'sanitize_text_field',
			$rules
		);

		/** @var string $start */
		$start = ! empty( $notification_data['start'] )
			? $notification_data['start']
			: date( 'Y-m-d H:i:s', time() ); // @todo why are some startless?

		$notification['start'] = sanitize_text_field( $start );

		/** @var string $end */
		$end = ! empty( $notification_data['end'] )
			? $notification_data['end']
			: date( 'Y-m-d H:i:s', time() + ( YEAR_IN_SECONDS * 1 ) ); // @todo why are some endless?

		$notification['end'] = $end;

		return $notification;
	}

	/**
	 * Validates notification data returned from the API.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $notification Notification.
	 * @return bool
	 */
	protected function can_import_notification( $notification ) {
		// Not valid if it has already expired.
		if (
			! empty( $notification['end'] ) &&
			is_string( $notification['end'] ) &&
			time() > strtotime( $notification['end'] )
		) {
			return false;
		}

		// Not valid if the start date is prior to plugin installation.
		$installation_date = get_option( 'simpay_installed', time() );

		if (
			! empty( $notification['start'] ) &&
			is_string( $notification['start'] ) &&
			$installation_date > strtotime( $notification['start'] )
		) {
			return false;
		}

		// Not valid if the current environment does not meet notification conditions.
		if (
			is_array( $notification['conditions'] ) &&
			! $this->rule_processor->is_valid( $notification['conditions'] )
		) {
			return false;
		}

		return true;
	}

}
