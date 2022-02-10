<?php
/**
 * Settings: Settings collection
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Settings;

use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setting_Collection class.
 *
 * @since 4.0.0
 */
class Setting_Collection extends Utils\Collection_Prioritized {

	/**
	 * Adds a Setting to the collection.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Setting $setting Settings arguments.
	 * @return \WP_Error|true True on successful addition, otherwise a \WP_Error object.
	 */
	public function add( $setting ) {
		// Ensure a valid section.
		if ( ! $setting instanceof \SimplePay\Core\Settings\Setting ) {
			return new \WP_Error(
				'invalid_setting',
				__( 'Invalid settings.', 'stripe' )
			);
		}

		$required_parameters = array(
			'id',
			'section',
			'subsection',
		);

		foreach ( $required_parameters as $parameter ) {
			if ( empty( $setting->$parameter ) ) {
				return new \WP_Error(
					'invalid_setting_' . $parameter,
					sprintf(
						/* translators: %s Required setting parameter. */
						__( 'Parameter <code>%s</code> is required when registering a setting.', 'stripe' ),
						$parameter
					)
				);
			}
		}

		return $this->add_item( $setting->id, $setting );
	}

	/**
	 * Filters registered settings given a criteria.
	 *
	 * @since 4.0.0
	 *
	 * @param string $by Attribute to filter by.
	 * @param string $value Attribute value to compare.
	 * @return array
	 */
	public function by( $by, $value ) {
		$settings = $this->get_items();

		return array_filter(
			$settings,
			function( $setting ) use ( $by, $value ) {
				return $value === $setting->$by;
			}
		);
	}

}
