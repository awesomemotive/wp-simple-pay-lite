<?php
/**
 * Settings: Setting
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
 * Setting class
 *
 * @since 4.0.0
 */
class Setting {

	/**
	 * Setting ID.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $id;

	/**
	 * Setting section.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $section;

	/**
	 * Setting subsection.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $subsection;

	/**
	 * Setting label.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $label;

	/**
	 * Setting description.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $description;

	/**
	 * Setting schema.
	 *
	 * @since 4.4.2
	 * @var array<mixed>
	 */
	public $schema;

	/**
	 * Setting value.
	 *
	 * @since 4.0.0
	 * @var mixed
	 */
	public $value = null;

	/**
	 * Settings section priority.
	 *
	 * @since 4.0.0
	 * @var float
	 */
	public $priority;

	/**
	 * Setting output.
	 *
	 * @since 4.0.0
	 * @var null|callable
	 */
	public $output = null;

	/**
	 * Setting toggles.
	 *
	 * @since 4.0.0
	 * @var array
	 */

	/**
	 * Constructs the settings section.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Setting section configuration.
	 *
	 *   @type string        $id Setting ID.
	 *   @type string        $section Setting section ID.
	 *   @type string        $subsection Setting subsection ID.
	 *   @type string        $label Setting label.
	 *   @type string        $description Setting description.
	 *   @type array<mixed>  $schema Setting schema.
	 *   @type int           $priority Setting priority.
	 *   @type callable|null $output Setting output.
	 *   @type array         $toggles {
	 *     Setting toggles.
	 *
	 *     @type string $value Value to match against.
	 *     @type string[] $settings Setting IDs to toggle.
	 *   }
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'id'         => '',
			'section'    => '',
			'subsection' => '',
			'label'      => '',
			'schema'	 => array(),
			'priority'   => 10,
			'output'     => null,
			'toggles'    => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		// ID.
		$this->id = sanitize_text_field( $args['id'] );

		// Section/Subsection.
		$this->section    = sanitize_text_field( $args['section'] );
		$this->subsection = sanitize_text_field( $args['subsection'] );

		// Label.
		$this->label = wp_kses(
			$args['label'],
			array(
				'span' => array(
					'class' => 'screen-reader-text',
				),
			)
		);

		if ( empty( $this->label ) ) {
			$this->label = $this->id;
		}

		// Schema.
		$this->schema = $args['schema'];

		// Priority.
		$this->priority = floatval( $args['priority'] );

		// Output.
		$this->output = $args['output'];

		// Toggles.
		$this->toggles = $args['toggles'];
	}

	/**
	 * Outputs the default markup for a setting (text field).
	 *
	 * @since 4.0.0
	 */
	public function output() {
		if ( ! is_callable( $this->output ) ) {
			return _doing_it_wrong(
				__METHOD__,
				__( 'Output must be callable or overriden in a subclass.', 'stripe' ),
				''
			);
		}

		echo call_user_func( $this->output, $this );
	}

}
