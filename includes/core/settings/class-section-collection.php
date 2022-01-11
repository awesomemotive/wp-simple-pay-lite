<?php
/**
 * Settings: Section collection
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
 * Section_Collection class.
 *
 * @since 4.0.0
 */
class Section_Collection extends Utils\Collection_Prioritized {

	/**
	 * Adds a Section to the collection.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Section $section Settings section arguments.
	 * @return \WP_Error|true True on successful addition, otherwise a \WP_Error object.
	 */
	public function add( $section ) {
		// Ensure a valid Section.
		if ( ! $section instanceof \SimplePay\Core\Settings\Section ) {
			return new \WP_Error(
				'invalid_settings_section',
				__( 'Invalid settings section.', 'stripe' )
			);
		}

		// Validate ID.
		if ( empty( $section->id ) ) {
			return new \WP_Error(
				'invalid_settings_section_id',
				__( 'Parameter <code>id</code> is required when registering a Section.', 'stripe' )
			);
		}

		return $this->add_item( $section->id, $section );
	}

}
