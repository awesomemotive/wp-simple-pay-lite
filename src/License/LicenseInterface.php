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
	 * @return 'unlimited'|string
	 */
	public function get_expiration();

	/**
	 * Returns the license's status.
	 *
	 * @since 4.4.0
	 *
	 * @return 'empty'|'valid'|'valid-forever'|'expired'|'disabled'|'revoked'|'invalid'|'inactive'|'deactivated'|'failed'|'site_inactive'|'item_name_mismatch'|'invalid_item_id'|'no_activations_left'|null
	 */
	public function get_status();

	/**
	 * Determines if a license is valid.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function is_valid();

	/**
	 * Returns the license level name.
	 *
	 * @since 4.4.3
	 * @return string
	 */
	public function get_level();

	/**
	 * Returns the license key.
	 *
	 * @since 4.4.3
	 * @return null|string
	 */
	public function get_key();

	/**
	 * Determines if a license (install) is Lite.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function is_lite();

	/**
	 * Determines if a license is Pro.
	 *
	 * @since 4.4.0
	 *
	 * @param 'personal'|'plus'|'professional' $tier License tier. Default personal.
	 * @param '>'|'>='                         $comparison Tier comparision. Default greater or equal to.
	 * @return bool
	 */
	public function is_pro( $tier = 'personal', $comparison = '>=' );

}
