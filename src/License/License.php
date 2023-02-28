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

use stdClass;

/**
 * License class.
 *
 * @since 4.4.0
 */
class License {

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
	 * Determines if the license is expiring at the end of the billing cycle.
	 *
	 * @since 4.4.6
	 * @var bool|null
	 */
	private $is_expiring;

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
	 * Returns the license's associated customer email address.
	 *
	 * @since 4.4.0
	 *
	 * @return string|null
	 */
	public function get_customer_email() {
		$data = $this->get_license_data();

		if ( isset( $data->customer_email ) ) {
			$this->email = $data->customer_email;
		}

		return $this->email;
	}

	/**
	 * Returns the license's associated customer name.
	 *
	 * @since 4.4.0
	 *
	 * @return string|null
	 */
	public function get_customer_name() {
		$data = $this->get_license_data();

		if ( isset( $data->customer_name ) ) {
			$this->name = $data->customer_name;
		}

		return $this->name;
	}

	/**
	 * Returns the license's item ID.
	 *
	 * @since 4.4.0
	 *
	 * @return int|null
	 */
	public function get_item_id() {
		$data = $this->get_license_data();

		if ( isset( $data->item_id ) ) {
			$this->item_id = (int) $data->item_id;
		}

		return $this->item_id;
	}

	/**
	 * Returns the license's price ID.
	 *
	 * @since 4.4.0
	 *
	 * @return int|null
	 */
	public function get_price_id() {
		$data = $this->get_license_data();

		if ( isset( $data->price_id ) ) {
			$this->price_id = (int) $data->price_id;
		}

		return $this->price_id;
	}

	/**
	 * Returns the license's expiration date.
	 *
	 * @since 4.4.0
	 *
	 * @return string|null
	 */
	public function get_expiration() {
		$data = $this->get_license_data();

		if ( isset( $data->expires ) ) {
			$this->expiration = $data->expires;
		}

		return $this->expiration;
	}

	/**
	 * Returns the license's creation date.
	 *
	 * @since 4.4.4
	 *
	 * @return null|string
	 */
	public function get_date_created() {
		$data = $this->get_license_data();

		if ( isset( $data->date_created ) ) {
			$this->date_created = $data->date_created;
		}

		return $this->date_created;
	}

	/**
	 * Returns the license's status.
	 *
	 * @since 4.4.0
	 *
	 * @return 'empty'|'valid'|'expired'|'disabled'|'revoked'|'invalid'|'inactive'|'deactivated'|'failed'|'site_inactive'|'item_name_mismatch'|'invalid_item_id'|'no_activations_left'|null
	 */
	public function get_status() {
		$data = $this->get_license_data();

		if ( isset( $data->license ) ) {
			$this->status = $data->license;
		}

		return $this->status;
	}

	/**
	 * Returns the license key.
	 *
	 * @since 4.4.3
	 * @since 4.4.6 Only returns a string.
	 * @return string
	 */
	public function get_key() {
		if ( true === $this->is_lite() ) {
			return '';
		}

		if ( defined( 'SIMPLE_PAY_LICENSE_KEY' ) ) {
			return SIMPLE_PAY_LICENSE_KEY;
		}

		/** @var string */
		return get_option( 'simpay_license_key', '' );
	}

	/**
	 * Returns the current license level name.
	 *
	 * @since 4.4.0
	 *
	 * @return string 'lite'|'personal'|'plus'|'professional'|'ultimate'|'elite'
	 */
	public function get_level() {
		// Lite.
		if ( $this->is_lite() ) {
			return 'lite';
		}

		$price_id = $this->get_price_id();

		// No price ID is found, assume Personal.
		if ( null === $price_id ) {
			return 'personal';
		}

		switch ( $price_id ) {
			case '1':
				return 'personal';
			case '2':
				return 'plus';
			case '3':
				return 'professional';
			case '4':
				return 'ultimate';
			case '5':
				return 'elite';
			default:
				return 'personal';
		}
	}

	/**
	 * Determines if the current license is valid.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function is_valid() {
		return 'valid' === $this->get_status();
	}

	/**
	 * Determines if the current license is expiring at the "expiring" (billing cycle) date.
	 *
	 * @since 4.4.6
	 *
	 * @return bool|null
	 */
	public function is_expiring() {
		$data = $this->get_license_data();

		if ( isset( $data->is_expiring ) ) {
			$this->is_expiring = (bool) $data->is_expiring;
		}

		return $this->is_expiring;
	}

	/**
	 * Determines if the current license is in a grace period after expiration.
	 * If the license is valid or lifetime it is considered in the grace period.
	 *
	 * @since 4.4.6
	 *
	 * @return bool
	 */
	public function is_in_grace_period() {
		// License is valid, so technically we are in a grace period.
		if ( $this->is_valid() ) {
			return true;
		}

		$expired = 'expired' === $this->get_status();

		// License is not expired, so we are technically in a grace period.
		if ( ! $expired ) {
			return true;
		}

		/** @var string $expiration */
		$expiration = $this->get_expiration();

		// License is lifetime, so we are technically in a grace period.
		if ( 'lifetime' === $expiration ) {
			return true;
		}

		$time_since_expiration = time() - strtotime( $expiration );

		// Grace period is 14 days.
		return $time_since_expiration < ( DAY_IN_SECONDS * 14 );
	}

	/**
	 * Determines if the current license has access to subscription functionality.
	 *
	 * @since 4.4.4
	 *
	 * @return bool
	 */
	public function is_subscriptions_enabled() {
		// Invalid, so no subscriptions.
		if ( false === $this->is_valid() ) {
			return false;
		}

		// Lite, so no subscriptions.
		if ( true === $this->is_lite() ) {
			return false;
		}

		return $this->is_pro( 'plus', '>=' );
	}

	/**
	 * Determines if the current license has access to enhanced subscription functionality.
	 *
	 * @since 4.4.4
	 *
	 * @return bool
	 */
	public function is_enhanced_subscriptions_enabled() {
		// Not valid, so no subscriptions.
		if ( false === $this->is_subscriptions_enabled() ) {
			return false;
		}

		// Grandfather Plus or higher to all subscription features when purchased
		// before March 30, 2022.
		/** @var string $created */
		$created = $this->get_date_created();

		if (
			$this->is_pro( 'plus', '=' ) &&
			strtotime( $created ) < strtotime( '2022-03-30 23:23:59' )
		) {
			return true;
		}

		// Available to Professional or higher.
		return $this->is_pro( 'professional', '>=' );
	}

	/**
	 * Determines if the current license is Lite.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function is_lite() {
		$is_lite = ! file_exists(
			trailingslashit( SIMPLE_PAY_INC ) . 'pro/class-simplepaypro.php' // @phpstan-ignore-line
		);

		/**
		 * Filters whether the current environment is Lite or not.
		 *
		 * @since 4.6.0
		 *
		 * @param bool $is_lite Whether the current environment is Lite or not.
		 */
		$is_lite = apply_filters( 'simpay_is_lite', $is_lite );

		return $is_lite;
	}

	/**
	 * Determines if the current license is a specific type of Pro license.
	 *
	 * @since 4.4.0
	 *
	 * @param string $tier License tier/level.
	 * @param string $comparison PHP comparison string.
	 * @return bool
	 */
	public function is_pro( $tier = 'personal', $comparison = '>=' ) {
		// Lite.
		if ( $this->is_lite() ) {
			return false;
		}

		$price_id = $this->get_price_id();

		// No price ID is found, assume Personal.
		if ( null === $price_id ) {
			return false;
		}

		$price_id = (string) $price_id;

		switch ( $tier ) {
			case 'personal':
				return version_compare( $price_id, '1', $comparison );
			case 'plus':
				return version_compare( $price_id, '2', $comparison );
			case 'professional':
				return version_compare( $price_id, '3', $comparison );
			case 'ultimate':
				return version_compare( $price_id, '4', $comparison );
			case 'elite':
				return version_compare( $price_id, '5', $comparison );
			default:
				return false;
		}
	}

	/**
	 * Returns public license data.
	 *
	 * @since 4.4.6
	 *
	 * @return array<string, array<string, bool|string|null>|bool|string|null>
	 */
	public function to_array() {
		return array(
			'key'         => $this->get_key(),
			'is_lite'     => $this->is_lite(),
			'level'       => $this->get_level(),
			'valid'       => $this->is_valid(),
			'status'      => $this->get_status(),
			'expires'     => $this->get_expiration(),
			'is_expiring' => $this->is_expiring(),
			'created'     => $this->get_date_created(),
			'customer'    => array(
				'email' => $this->get_customer_email(),
				'name'  => $this->get_customer_name(),
			),
			'features'    => array(
				'subscriptions'          => $this->is_subscriptions_enabled(),
				'enhanced_subscriptions' => $this->is_enhanced_subscriptions_enabled(),
			),
		);
	}

	/**
	 * Returns the license data from the cache or remote response.
	 *
	 * @since 4.4.0
	 *
	 * @return object
	 */
	private function get_license_data() {
		$license_data = get_option( 'simpay_license_data', '' );

		if ( empty( $license_data ) ) {
			return new stdClass;
		}

		/** @var object $license_data */
		return $license_data;
	}

}
