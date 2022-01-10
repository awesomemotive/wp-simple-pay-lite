<?php
/**
 * Settings: Section
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
 * Section class
 *
 * @since 4.0.0
 */
class Section {

	/**
	 * Section ID.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $id;

	/**
	 * Section label.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $label;

	/**
	 * Section priority.
	 *
	 * @since 4.0.0
	 * @var float
	 */
	public $priority;

	/**
	 * Constructs the Section.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Section configuration.
	 *
	 *   @type string $id Section ID.
	 *   @type string $label Section label.
	 *   @type float  $priority Section priority.
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'id'       => '',
			'label'    => '',
			'priority' => 10,
		);

		$args = wp_parse_args( $args, $defaults );

		// ID.
		$this->id = sanitize_text_field( $args['id'] );

		// Label.
		$this->label = wp_kses_post( $args['label'] );

		// ... fall back to ID if empty.
		// A visual label is needed to construct the UI.
		if ( empty( $this->label ) ) {
			$this->label = $this->id;
		}

		// Priority.
		$this->priority = $args['priority'];
	}

	/**
	 * Returns the section's subsections.
	 *
	 * @since 4.0.0
	 *
	 * @return \SimplePay\Core\Admin\Settings\Subsection[]
	 */
	public function get_subsections() {
		static $subsections = array();

		if ( empty( $subsections ) ) {
			$collection = Utils\get_collection( 'settings-subsections' );

			if ( false !== $collection ) {
				$subsections = $collection->by( 'section', $this->id );
			}
		}

		return $subsections;
	}

}
