<?php
/**
 * License: License
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\License;

/**
 * License class.
 *
 * @since 4.4.0
 */
class License extends AbstractLicense {

	/**
	 * License key.
	 *
	 * Key is currently passed as a required argument but not currently used.
	 * In the future when admin_init doesn't automatically populate license
	 * data we will need to do so here.
	 *
	 * @see SimplePay\Pro\License_Management\check_license_still_valid()
	 *
	 * @since 4.4.0
	 * @var string|null
	 */
	protected $key;

	/**
	 * License customer email address.
	 *
	 * @since 4.4.0
	 * @var string|null
	 */
	private $email;

	/**
	 * License customer name.
	 *
	 * @since 4.4.0
	 * @var string|null
	 */
	private $name;

	/**
	 * License download/item ID.
	 *
	 * @since 4.4.0
	 * @var int|null
	 */
	private $item_id;

	/**
	 * License price ID.
	 *
	 * @since 4.4.0
	 * @var int|null
	 */
	private $price_id;

	/**
	 * License expiration date.
	 *
	 * 'unlimited' if the license does not expire. Date time otherwise.
	 *
	 * @since 4.4.0
	 * @var 'unlimited'|string|null
	 */
	private $expiration;

	/**
	 * License creation date.
	 *
	 * @since 4.4.4
	 * @var string|null
	 */
	private $date_created;

	/**
	 * License status.
	 *
	 * @since 4.4.0
	 * @var 'empty'|'valid'|'expired'|'disabled'|'revoked'|'invalid'|'inactive'|'deactivated'|'failed'|'site_inactive'|'item_name_mismatch'|'invalid_item_id'|'no_activations_left'|null
	 */
	private $status;

	/**
	 * License.
	 *
	 * @since 4.4.0
	 *
	 * @param string $key License key.
	 */
	public function __construct( $key ) {
		$this->key = $key;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_customer_email() {
		if ( $this->email ) {
			return $this->email;
		}

		$data = $this->get_license_data();

		if ( isset( $data->customer_email ) ) {
			$this->email = $data->customer_email;
		}

		return $this->email;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_customer_name() {
		if ( $this->name ) {
			return $this->name;
		}

		$data = $this->get_license_data();

		if ( isset( $data->customer_name ) ) {
			$this->name = $data->customer_name;
		}

		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_item_id() {
		if ( $this->item_id ) {
			return $this->item_id;
		}

		$data = $this->get_license_data();

		if ( isset( $data->item_id ) ) {
			$this->item_id = (int) $data->item_id;
		}

		return $this->item_id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_price_id() {
		if ( $this->price_id ) {
			return $this->price_id;
		}

		$data = $this->get_license_data();

		if ( isset( $data->price_id ) ) {
			$this->price_id = (int) $data->price_id;
		}

		return $this->price_id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_expiration() {
		if ( $this->expiration ) {
			return $this->expiration;
		}

		$data = $this->get_license_data();

		if ( isset( $data->expires ) ) {
			$this->expiration = $data->expires;
		}

		return $this->expiration;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_date_created() {
		if ( $this->date_created ) {
			return $this->date_created;
		}

		$data = $this->get_license_data();

		if ( isset( $data->date_created ) ) {
			$this->date_created = $data->date_created;
		}

		return $this->date_created;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_status() {
		if ( $this->status ) {
			return $this->status;
		}

		$data = $this->get_license_data();

		if ( isset( $data->license ) ) {
			$this->status = $data->license;
		}

		return $this->status;
	}

}
