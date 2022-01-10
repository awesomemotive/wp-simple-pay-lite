<?php
/**
 * Settings: Subsection collection
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
 * Subsection_Collection class.
 *
 * @since 4.0.0
 */
class Subsection_Collection extends Utils\Collection_Prioritized {

	/**
	 * Adds a Subsection to the collection.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Subsection $subsection Settings Subsection arguments.
	 * @return \WP_Error|true True on successful addition, otherwise a \WP_Error object.
	 */
	public function add( $subsection ) {
		// Ensure a valid subsection.
		if ( ! $subsection instanceof \SimplePay\Core\Settings\Subsection ) {
			return new \WP_Error(
				'invalid_settings_subsection',
				__( 'Invalid settings subsection.', 'stripe' )
			);
		}

		// Validate ID.
		if ( empty( $subsection->id ) ) {
			return new \WP_Error(
				'invalid_settings_subsection_id',
				__( 'Parameter <code>id</code> is required when registering a settings subsection.', 'stripe' )
			);
		}

		// Validate section.
		if ( empty( $subsection->section ) ) {
			return new \WP_Error(
				'invalid_settings_subsection_section',
				__( 'Parameter <code>section</code> is required when registering a settings subsection.', 'stripe' )
			);
		} else {
			static $sections = array();

			if ( empty( $sections ) ) {
				$sections = Utils\get_collection( 'settings-sections' );
			}

			if ( false === $sections->get_item( $subsection->section ) ) {
				return new \WP_Error(
					'invalid_settings_subsection_section',
					sprintf(
						/* translators: %s Settings subsection parent seection. */
						__( 'Settings subsection parent section %s does not exist.', 'stripe' ),
						$subsection->section
					)
				);
			}
		}

		return $this->add_item( $subsection->id, $subsection );
	}

	/**
	 * Filters registered subsections given a criteria.
	 *
	 * @since 4.0.0
	 *
	 * @param string $by Attribute to filter by.
	 * @param string $value Attribute value to compare.
	 * @return array
	 */
	public function by( $by, $value ) {
		$subsections = $this->get_items();

		return array_filter(
			$subsections,
			function( $subsection ) use ( $by, $value ) {
				return $value === $subsection->$by;
			}
		);
	}

}
