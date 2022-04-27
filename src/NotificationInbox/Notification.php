<?php
/**
 * Notification inbox: Notification
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox;

use SimplePay\Core\Model\AbstractModel;

/**
 * Notification class.
 *
 * @since 4.4.5
 */
class Notification extends AbstractModel {

	/**
	 * Notification ID.
	 *
	 * @since 4.4.5
	 * @var int
	 */
	public $id;

	/**
	 * Notification remote ID.
	 *
	 * @since 4.4.5
	 * @var int
	 */
	public $remote_id;

	/**
	 * Notification source.
	 *
	 * @since 4.4.5
	 * @var string
	 */
	public $source;

	/**
	 * Notification title.
	 *
	 * @since 4.4.5
	 * @var string
	 */
	public $title;

	/**
	 * Notification slug.
	 *
	 * @since 4.4.5
	 * @var string
	 */
	public $slug;

	/**
	 * Notification content.
	 *
	 * @since 4.4.5
	 * @var string
	 */
	public $content;

	/**
	 * Notification actions.
	 *
	 * @since 4.4.5
	 * @var array<array<string, string>>
	 */
	public $actions;

	/**
	 * Notification type.
	 *
	 * @since 4.4.5
	 * @var string
	 */
	public $type;

	/**
	 * Notification conditions.
	 *
	 * @since 4.4.5
	 * @var array<string>
	 */
	public $conditions;

	/**
	 * Notification start date timestamp.
	 *
	 * @since 4.4.5
	 * @var int
	 */
	public $start;

	/**
	 * Notification end date timestamp.
	 *
	 * @since 4.4.5
	 * @var int
	 */
	public $end;

	/**
	 * Notification dismissed.
	 *
	 * @since 4.4.5
	 * @var bool
	 */
	public $dismissed;

	/**
	 * Notification dismissibility.
	 *
	 * @since 4.4.5
	 * @var bool
	 */
	public $is_dismissible;

	/**
	 * Notification creation date timestamp.
	 *
	 * @since 4.4.5
	 * @var int
	 */
	public $date_created;

	/**
	 * Notification modification date timestamp.
	 *
	 * @since 4.4.5
	 * @var int
	 */
	public $date_modified;

	/**
	 * Notification.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $data Data to create an model from.
	 */
	public function __construct( $data ) {
		parent::__construct( $data );

		// Cast values.
		if ( ! empty( $this->id ) ) {
			$this->id = (int) $this->id;
		}

		if ( ! empty( $this->remote_id ) ) {
			$this->remote_id = (int) $this->remote_id;
		}

		if ( ! empty( $this->title ) ) {
			$this->title = (string) $this->title;
		}

		if ( ! empty( $this->content ) ) {
			$this->content = (string) $this->content;
		}

		/** @var string $actions */
		$actions = $this->actions;

		if ( ! empty( $actions ) && is_array( json_decode( $actions, true ) ) ) {
			$this->actions = json_decode( $actions, true );
		} else {
			$this->actions = array();
		}

		if ( ! empty( $this->type ) ) {
			$this->type = (string) $this->type;
		}

		/** @var string $conditions */
		$conditions = $this->conditions;

		if ( ! empty( $conditions ) && is_array( json_decode( $conditions, true ) ) ) {
			$this->conditions = json_decode( $conditions, true );
		}

		/** @var string $start */
		$start = $this->start;

		if ( ! empty( $start ) && false !== strtotime( $start ) ) {
			$this->start = strtotime( $start );
		}

		/** @var string $end */
		$end = $this->end;

		if ( ! empty( $end ) && false !== strtotime( $end ) ) {
			$this->end = strtotime( $end );
		}

		$this->dismissed = $this->dismissed !== null
			? (bool) $this->dismissed
			: true;

		$this->is_dismissible = $this->is_dismissible !== null
			? (bool) $this->is_dismissible
			: true;

		/** @var string $date_created */
		$date_created = $this->date_created;

		if ( ! empty( $date_created ) && false !== strtotime( $date_created ) ) {
			$this->date_created = strtotime( $date_created );
		}

		/** @var string $date_modified */
		$date_modified = $this->date_modified;

		if ( ! empty( $date_modified ) && false !== strtotime( $date_modified ) ) {
			$this->date_modified = strtotime( $date_modified );
		}
	}

}
