<?php
/**
 * REST API: Notifications.
 *
 * Note: This is a temporary solution until the other REST API has been full migrated
 * to the new plugin container.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\RestApi;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\NotificationInbox\Notification;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Notifications class.
 *
 * @since 4.4.5
 */
class __UnstableNotifications implements SubscriberInterface {

	/**
	 * Notifications.
	 *
	 * @since 4.4.5
	 * @var \SimplePay\Core\NotificationInbox\NotificationRepository
	 */
	private $notifications;

	/**
	 * __UnstableNotifications.
	 *
	 * @since 4.4.5
	 */
	public function __construct( NotificationRepository $notifications ) {
		$this->notifications = $notifications;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers the REST API route for GET /wpsp/v2/notifications.
	 *
	 * @since 4.4.5
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			'wpsp/v2',
			'notifications',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_notifications' ),
					'permission_callback' => array( $this, 'can_manage_notifications' ),
					'args'                => array(
						'limit' => array(
							'description'       => __(
								'The number of notifications to return.',
								'stripe'
							),
							'type'              => 'number',
							'default'           => 100,
							'required'          => false,
						),
						'status' => array(
							'description'       => __(
								'Notification dismissisal status.',
								'stripe'
							),
							'type'              => 'string',
							'enum'              => array( 'read', 'unread' ),
							'default'           => 'unread',
							'required'          => false,
						)
					),
				)
			)
		);

		register_rest_route(
			'wpsp/v2',
			'notifications/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'dismiss_notification' ),
					'permission_callback' => array( $this, 'can_manage_notifications' ),
					'args'                => array(
						'id' => array(
							'description'       => __(
								'ID of the notification.',
								'stripe'
							),
							'type'              => 'integer',
							'required'          => true,
							'validate_callback' => function ( $param ) {
								$notification = $this->notifications->get(
									intval( $param )
								);

								return $notification instanceof Notification;
							},
							'sanitize_callback' => function ( $param ) {
								return intval( $param );
							}
						)
					),
				)
			)
		);
	}

	/**
	 * Determines if the current user can manage notifications.
	 *
	 * @since 4.4.5
	 *
	 * @return bool
	 */
	public function can_manage_notifications() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Lists notifications for a given status.
	 *
	 * @since 4.4.5
	 *
	 * @param \WP_REST_Request $request REST API request.
	 * @return \WP_REST_Response REST API response.
	 */
	public function get_notifications( $request ) {
		$args = array(
			'number'    => $request->get_param( 'limit' ),
			'orderby'   => 'start',
			'order'     => 'desc',
		);

		// Use specific helper methods to ensure query arguments are consistent
		// accross consumers. Might not be necessary/pointless because get_unread_count()
		// still manually defines its own query arguments.

		// Unread.
		if ( 'unread' === $request->get_param( 'status' ) ) {
			$notifications = $this->notifications->get_unread( $args );

			// Read.
		} else {
			$notifications = $this->notifications->get_read( $args );
		}

		return new WP_REST_Response(
			array(
				'data' => $notifications,
			)
		);
	}

	/**
	 * Dismisses a notification.
	 *
	 * @since 4.4.5
	 *
	 * @param \WP_REST_Request $request REST API request.
	 * @return \WP_REST_Response REST API response.
	 */
	public function dismiss_notification( $request ) {
		$id = $request->get_param( 'id' );

		if ( null === $id ) {
			return new WP_REST_Response(
				array(
					'error' => __( 'Notification not found.', 'stripe' ),
				)
			);
		}

		/** @var int $id */

		$notification = $this->notifications->update(
			$id,
			array(
				'dismissed' => true,
			)
		);

		if ( ! $notification instanceof Notification ) {
			return new WP_REST_Response(
				array(
					'error' => __( 'Notification not found.', 'stripe' ),
				)
			);
		}

		return new WP_REST_Response( $notification );
	}

}
