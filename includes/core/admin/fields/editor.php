<?php

namespace SimplePay\Core\Admin\Fields;

use SimplePay\Core\Abstracts\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checkbox input field.
 *
 * Outputs one single checkbox or a fieldset of checkboxes for multiple choices.
 *
 * @since 3.0.0
 */
class Editor extends Field {

	/**
	 * Construct.
	 *
	 * @since 3.0.0
	 *
	 * @param array $field
	 */
	public function __construct( $field ) {
		$this->type_class = 'simpay-field-editor';
		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		// https://codex.wordpress.org/Function_Reference/wp_editor
		wp_editor( $this->value, $this->id, array( 'textarea_name' => $this->name ) );

		echo $this->description;
	}

}
