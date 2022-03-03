<?php
/**
 * License: Abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\License;

/**
 * AbstractLicense abstract.
 *
 * @since 4.4.0
 */
abstract class AbstractLicense implements LicenseInterface {

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_customer_email();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_customer_name();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_item_id();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_price_id();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_expiration();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_status();

	/**
	 * {@inheritdoc}
	 */
	public function get_key() {
		if ( true === $this->is_lite() ) {
			return null;
		}

		/** @var string */
		return get_option( 'simpay_license_key', '' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_valid() {
		return 'valid' === $this->get_status();
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function is_lite() {
		return ! class_exists( '\SimplePay\Pro\SimplePayPro', false );
	}

	/**
	 * {@inheritdoc}
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
}
