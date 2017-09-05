<?php

namespace SimplePay\Core\Admin\Fields;

use SimplePay\Core\Abstracts\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Standard input field.
 *
 * For standard text inputs and subtypes (e.g. number, password, email...).
 *
 * @since 3.0.0
 */
class Standard extends Field {

	/**
	 * Field subtype.
	 *
	 * @var string
	 */
	public $subtype = '';

	/**
	 * Construct.
	 *
	 * @since 3.0.0
	 *
	 * @param array $field
	 */
	public function __construct( $field ) {

		$this->subtype = isset( $field['subtype'] ) ? esc_attr( $field['subtype'] ) : 'text';

		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		?>
		<input type="<?php echo $this->subtype; ?>"
		       name="<?php echo $this->name; ?>"
		       id="<?php echo $this->id; ?>"
		       value="<?php echo $this->value; ?>"
		       class="<?php echo $this->class; ?>"<?php
		echo $this->style ? 'style="' . $this->style . '" ' : ' ';
		echo $this->placeholder ? 'placeholder="' . $this->placeholder . '"' : ' ';
		echo $this->attributes; ?>/>
		<?php


		if ( ! empty( $this->description ) ) {
			echo '<p class="description">' . $this->description . '</p>';
		}

		if ( is_string( $this->validation ) && ! empty ( $this->validation ) ) {
			echo $this->validation;
		}

	}

}
