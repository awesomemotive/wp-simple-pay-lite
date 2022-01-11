<?php
/**
 * Admin setting fields: TinyMCE Editor
 *
 * @package SimplePay\Core\Admin\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

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
	 * @param array $field Field data.
	 */
	public function __construct( $field ) {
		$this->type_class = 'simpay-field-editor';
		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/wp_editor
	 *
	 * @since 3.0.0
	 */
	public function html() {
		wp_editor( $this->value, $this->id, array( 'textarea_name' => $this->name ) );

		echo $this->description;
	}

}
