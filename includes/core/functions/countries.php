<?php
/**
 * Functions: Countries
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Country List
 *
 * Sources:
 * https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/master/includes/country-functions.php
 * https://github.com/woocommerce/woocommerce/blob/master/i18n/countries.php
 * https://github.com/umpirsky/country-list
 *
 * @return array $countries A list of the available countries
 */
function simpay_get_country_list() {
	return SimplePay\Core\i18n\get_countries();
}
