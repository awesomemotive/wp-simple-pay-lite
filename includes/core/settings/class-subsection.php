<?php
/**
 * Settings: Subsection
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subsection class
 *
 * @since 4.0.0
 */
class Subsection {

	/**
	 * Subsection ID.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $id;

	/**
	 * Subsection label.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $label;

	/**
	 * Subsection section ID.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $section;

	/**
	 * Subsection priority.
	 *
	 * @since 4.0.0
	 * @var float
	 */
	public $priority;

	/**
	 * Constructs the Subsection.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Subsection configuration.
	 *
	 *   @type string $id Subsection ID.
	 *   @type string $label Subsection label.
	 *   @type string $section Subsection section.
	 *   @type float  $priority Subsection priority.
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'id'       => '',
			'label'    => '',
			'section'  => '',
			'priority' => 10,
		);

		$args = wp_parse_args( $args, $defaults );

		// ID.
		$this->id = sanitize_text_field( $args['id'] );

		// Label.
		$this->label = wp_kses_post( $args['label'] );

		if ( empty( $this->label ) ) {
			$this->label = $this->id;
		}

		// Section.
		$this->section = sanitize_text_field( $args['section'] );

		// Priority.
		$this->priority = floatval( $args['priority'] );
	}

}
