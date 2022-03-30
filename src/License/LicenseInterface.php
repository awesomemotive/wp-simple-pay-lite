<?php
/**
 * License: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\License;

/**
 * LicenseInterface interface.
 *
 * @since 4.4.0
 */
interface LicenseInterface {

	/**
	 * Returns the license's associated customer email address.
	 *
	 * @since 4.4.0
	 *
	 * @return string|null
	 */
	public function get_customer_email();

	/**
	 * Returns the license's associated customer name.
	 *
	 * @since 4.4.0
	 *
	 * @return string|null
	 */
	public function get_customer_name();

	/**
	 * Returns the license's item ID.
	 *
	 * @since 4.4.0
	 *
	 * @return int|null
	 */
	public function get_item_id();

	/**
	 * Returns the license's price ID.
	 *
	 * @since 4.4.0
	 *
	 * @return int|null
	 */
	public function get_price_id();

	/**
	 * Returns the license's expiration date.
	 *
	 * @since 4.4.0
	 *
	 * @return 'unlimited'|string|null
	 */
	public function get_expiration();

	/**
	 * Returns the license's creation date.
	 *
	 * @since 4.4.4
	 *
	 * @return null|string
	 */
	public function get_date_created();

	/**
	 * Returns the license's status.
	 *
	 * @since 4.4.0
	 *
	 * @return 'empty'|'valid'|'expired'|'disabled'|'revoked'|'invalid'|'inactive'|'deactivated'|'failed'|'site_inactive'|'item_name_mismatch'|'invalid_item_id'|'no_activations_left'|null
	 */
	public function get_status();

	/**
	 * Returns the license key.
	 *
	 * @since 4.4.3
	 * @return null|string
	 */
	public function get_key();

	/**
	 * Determines if the current license is valid.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function is_valid();

	/**
	 * Determines if the current license is Lite.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function is_lite();

	/**
	 * Determines if the current license is a specific type of Pro license.
	 *
	 * @since 4.4.0
	 *
	 * @param string $tier License tier/level.
	 * @param string $comparison PHP comparison string.
	 * @return bool
	 */
	public function is_pro( $tier = 'personal', $comparison = '>=' );

	/**
	 * Returns the current license level name.
	 *
	 * @since 4.4.0
	 *
	 * @return string 'lite'|'personal'|'plus'|'professional'|'ultimate'|'elite'
	 */
	public function get_level();

}
