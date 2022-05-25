<?php
/**
 * Notification inbox: Notification repository
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox;

use SimplePay\Core\Repository\BerlinDbRepository;

/**
 * NotificationRepository class.
 *
 * @since 4.4.5
 */
class NotificationRepository extends BerlinDbRepository {

	/**
	 * Notification rule processor.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\NotificationInbox\NotificationRuleProcessor
	 */
	private $rule_processor;

	/**
	 * NotificationImporter.
	 *
	 * @since 4.4.5
	 *
	 * @param \SimplePay\Core\NotificationInbox\NotificationRuleProcessor $rule_processor Notification rule processor.
	 */
	public function __construct( NotificationRuleProcessor $rule_processor ) {
		$this->rule_processor = $rule_processor;

		parent::__construct(
			Notification::class,
			Database\Query::class
		);
	}

	/**
	 * Restores an existing notification (updating arguments), or creates a new one.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $args Arguments to create the notification with.
	 * @param null|callable $callback Callback if the notification is actually restored.
	 * @return null|\SimplePay\Core\Model\ModelInterface
	 */
	public function restore( $args, $callback = null ) {
		if ( empty( $args['slug'] ) || ! is_string( $args['slug'] ) ) {
			return null;
		}

		$notification = $this->get_by_slug( $args['slug'] );

		// Add a new one if it does not exist already.
		if ( ! $notification instanceof Notification ) {
			return $this->add( $args );

			// Undismiss and update if previously dismissed.
		} else {
			if ( false === $notification->dismissed ) {
				return $notification;
			}

			// Reset start time.
			$start = isset( $args['start'] )
				? $args['start']
				: date( 'Y-m-d H:i:s', time() );

			// Move end time forward the same amount as the previous difference.
			$end = isset( $args['end'] )
				? $args['end']
				: date(
					'Y-m-d H:i:s',
					time() + ( $notification->end - $notification->start )
				);

			$notification = $this->update(
				$notification->id,
				array_merge(
					$args,
					array(
						'dismissed' => false,
						'start'     => $start,
						'end'       => $end,
					)
				)
			);

			if (
				$notification instanceof Notification &&
				is_callable( $callback )
			) {
				call_user_func( $callback, $notification );
			}

			return $notification;
		}
	}

	/**
	 * Adds a notification.
	 *
	 * Converts array values to (JSON) strings to avoid a PHP notice in array_diff_assoc.
	 * @link https://github.com/berlindb/core/blob/bdea8cb238b71248d714e9c46bd8596fdbb4c9e7/src/Database/Query.php#L1943
	 * @link https://bugs.php.net/bug.php?id=62115
	 *
	 * @since 4.4.6
	 *
	 * @param array<mixed> $args Arguments to update the notification with.
	 */
	public function add( $args = array() ) {
		if ( isset( $args['conditions'] ) && is_array( $args['conditions'] ) ) {
			$args['conditions'] = wp_json_encode( $args['conditions'] );
		}

		if ( isset( $args['actions'] ) && is_array( $args['actions'] ) ) {
			$args['actions'] = wp_json_encode( $args['actions'] );
		}

		return parent::add( $args );
	}

	/**
	 * Updates a notification.
	 *
	 * Converts array values to (JSON) strings to avoid a PHP notice in array_diff_assoc.
	 * @link https://github.com/berlindb/core/blob/bdea8cb238b71248d714e9c46bd8596fdbb4c9e7/src/Database/Query.php#L1943
	 * @link https://bugs.php.net/bug.php?id=62115
	 *
	 * @since 4.4.5
	 *
	 * @param int $notification_id Notification ID.
	 * @param array<mixed> $args Arguments to update the notification with.
	 */
	public function update( $notification_id, $args = array() ) {
		if ( isset( $args['conditions'] ) && is_array( $args['conditions'] ) ) {
			$args['conditions'] = wp_json_encode( $args['conditions'] );
		}

		if ( isset( $args['actions'] ) && is_array( $args['actions'] ) ) {
			$args['actions'] = wp_json_encode( $args['actions'] );
		}

		return parent::update( $notification_id, $args );
	}

	/**
	 * Dismisses a notification.
	 *
	 * @since 4.4.5
	 *
	 * @param int|string $id_or_slug Notification ID or slug.
	 * @return null|\SimplePay\Core\Model\ModelInterface
	 */
	public function dismiss( $id_or_slug ) {
		if ( is_int( $id_or_slug ) ) {
			$notification = $this->get_by( 'id', $id_or_slug );
		} else {
			$notification = $this->get_by( 'slug', $id_or_slug );
		}

		if ( ! $notification instanceof Notification ) {
			return null;
		}

		return $this->update(
			$notification->id,
			array(
				'dismissed' => true
			)
		);
	}

	/**
	 * Retrieves a notification by slug.
	 *
	 * @since 4.4.5
	 *
	 * @param string $slug Notification slug.
	 * @return null|\SimplePay\Core\Model\ModelInterface
	 */
	public function get_by_slug( $slug ) {
		return $this->get_by( 'slug', $slug );
	}

	/**
	 * Returns the number of unread notifications.
	 *
	 * This does not use COUNT(*) syntax because we want to revalidate unread
	 * notifications for the current environment.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $args Query arguments.
	 * @return int
	 */
	public function get_unread_count( $args = array() ) {
		/** @var string|bool $count */
		$count = wp_cache_get( 'simpay_unread_notification_count', 'simpay' );

		if ( false === $count ) {
			/** @var int $count */
			$count = count( $this->get_unread( $args ) );

			wp_cache_set(
				'simpay_unread_notification_count',
				$count,
				'simpay'
			);
		}

		return (int) $count;
	}

	/**
	 * Returns the number of read notifications.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $args Query arguments.
	 * @return int
	 */
	public function get_read_count( $args = array() ) {
		return $this->count(
			array_merge( $this->get_read_query_args(), $args )
		);
	}

	/**
	 * Returns all unread (not dismissed) notifications.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $args Query arguments.
	 * @return array<\SimplePay\Core\Model\ModelInterface>
	 */
	public function get_unread( $args = array() ) {
		$notifications = $this->query(
			array_merge( $this->get_unread_query_args(), $args )
		);

		$notifications = array_filter(
			$notifications,
			function( $notification ) {
				/** @var \SimplePay\Core\NotificationInbox\Notification $notification */
				return $this->rule_processor->is_valid(
					$notification->conditions
				);
			}
		);

		return array_values( $notifications );
	}

	/**
	 * Returns all read (dismissed) notifications.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $args Query arguments.
	 * @return array<\SimplePay\Core\Model\ModelInterface>
	 */
	public function get_read( $args = array()) {
		return $this->query(
			array_merge( $this->get_read_query_args(), $args )
		);
	}

	/**
	 * Returns query arguments for retrieving unread notifications.
	 *
	 * @since 4.4.5
	 *
	 * @return array<mixed>
	 */
	private function get_unread_query_args() {
		return array(
			'dismissed'  => false,
			'date_query' => array(
				'relation' => 'AND',
				array(
					'column'  => 'start',
					'compare' => '<=',
					'value'   => date( 'Y-m-d H:i:s', time() ),
				),
				array(
					'column'  => 'end',
					'compare' => '>=',
					'value'   => date( 'Y-m-d H:i:s', time() ),
				),
			)
		);
	}

	/**
	 * Returns query arguments for retrieving read notifications.
	 *
	 * @since 4.4.5
	 *
	 * @return array<mixed>
	 */
	private function get_read_query_args() {
		return array(
			'dismissed'  => true,
			'date_query' => array(
				'relation' => 'AND',
				array(
					'column'  => 'start',
					'compare' => '<=',
					'value'   => date( 'Y-m-d H:i:s', time() ),
				),
				array(
					'column'  => 'end',
					'compare' => '>=',
					'value'   => date( 'Y-m-d H:i:s', time() ),
				),
			)
		);
	}
}
