<?php
/**
 * Admin setting fields: Standard
 *
 * @package SimplePay\Core\Admin\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

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
	 * Field multiline
	 *
	 * @var bool $multiline
	 */
	public $multiline = false;

	/**
	 * Construct.
	 *
	 * @since 3.0.0
	 *
	 * @param array $field Field data.
	 */
	public function __construct( $field ) {
		$this->subtype   = isset( $field['subtype'] ) ? esc_attr( $field['subtype'] ) : 'text';
		$this->multiline = isset( $field['multiline'] ) ? (bool) $field['multiline'] : false;

		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 3.0.0
	 */
	public function html() {
		if ( true === $this->multiline ) {
			echo '<textarea
				rows="8"
				name="' . esc_attr( $this->name ) . '"
				id="' . esc_attr( $this->id ) . '"
				class="' . esc_attr( $this->class ) . '"
				' . ( $this->style ? 'style="' . $this->style . '" ' : ' ' ) . '
				' . ( $this->placeholder ? 'placeholder="' . $this->placeholder . '"' : ' ' ) . '
				' . ( $this->attributes ) . '>' . esc_attr( $this->value ) . '</textarea>';
		} else {
			echo '<input
				type="' . esc_attr( $this->subtype ) . '"
				name="' . esc_attr( $this->name ) . '"
				id="' . esc_attr( $this->id ) . '"
				value="' . esc_attr( $this->value ) . '"
				class="' . esc_attr( $this->class ) . '"
				autocomplete="off"
				' . ( $this->style ? 'style="' . $this->style . '" ' : ' ' ) . '
				' . ( $this->placeholder ? 'placeholder="' . $this->placeholder . '"' : ' ' ) . '
				' . ( $this->attributes ) . '
			/>';
		}

		if ( ! empty( $this->description ) ) {
			echo '<p class="description">' . $this->description . '</p>';
		}

		if ( is_string( $this->validation ) && ! empty( $this->validation ) ) {
			echo $this->validation;
		}
	}

}
