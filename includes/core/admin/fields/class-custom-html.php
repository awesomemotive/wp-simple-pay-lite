<?php
/**
 * Admin setting fields: Custom HTML
 *
 * @package SimplePay\Core\Admin\Fields
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Admin\Fields;

use SimplePay\Core\Abstracts\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Custom_Html extends Field {

	/**
	 * Construct.
	 *
	 * @since 3.0.0
	 *
	 * @param array $field
	 */
	public function __construct( $field ) {
		$this->type_class = 'simpay-field-custom-html';
		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		echo $this->custom_html;
	}
}
