<?php
/**
 * Admin: Admin notice subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 */

namespace SimplePay\Core\Admin;

use SimplePay\Core\AdminNotice\AdminNoticeInterface;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * AdminNoticeSubscriber class.
 *
 * @since 4.4.1
 */
class AdminNoticeSubscriber implements SubscriberInterface {

	/**
	 * Admin notices.
	 *
	 * @since 4.4.1
	 * @var \SimplePay\Core\AdminNotice\AdminNoticeInterface[] $notices Admin notices.
	 */
	private $notices;

	/**
	 * AdminNoticeSubscriber.
	 *
	 * @since 4.4.1
	 *
	 * @param \SimplePay\Core\AdminNotice\AdminNoticeInterface[] $notices Admin notices.
	 */
	public function __construct( $notices ) {
		$this->notices = array();

		foreach ( $notices as $notice ) {
			$this->add_admin_notice( $notice );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'admin_notices' => 'output_notices',
		);
	}

	/**
	 * Adds an admin notice to be registered.
	 *
	 * @since 4.4.1
	 *
	 * @param \SimplePay\Core\AdminNotice\AdminNoticeInterface $notice Admin notice.
	 * @return void
	 */
	private function add_admin_notice( AdminNoticeInterface $notice ) {
		$this->notices[] = $notice;
	}

	/**
	 * Outputs registered admin notices.
	 *
	 * @since 4.4.1
	 *
	 * @return void
	 */
	public function output_notices() {
		foreach ( $this->notices as $notice ) {
			if ( false === $notice->should_display() ) {
				continue;
			}

			if ( true === $notice->is_dismissed() ) {
				continue;
			}

			$notice->render();
		}
	}

}
