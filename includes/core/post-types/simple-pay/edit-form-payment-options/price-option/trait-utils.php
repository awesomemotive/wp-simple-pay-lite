<?php
/**
 * Simple Pay: Price fields options utils
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form_Payment_Options\Price_Option;

/**
 * Price option Utils
 *
 * @since 4.1.0
 */
trait Utils {
	/**
	 * Returns a price option's input `name` attribute.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param string|array $input Input name. List of items will be nested.
	 * @param string       $instance_id Unique instance ID.
	 */
	public function unstable_get_input_name( $input, $instance_id ) {
		$name = $input;

		if ( is_array( $input ) ) {
			$name = implode( '][', $input );
		}

		return sprintf(
			'_simpay_prices[%1$s][%2$s]',
			$instance_id,
			$name
		);
	}


	/**
	 * Returns a price option's input `id` attribute.
	 *
	 * @since 4.1.0
	 * @access public
	 *
	 * @param string|array $input Input name. List of items will be nested.
	 * @param string       $instance_id Unique instance ID.
	 */
	public function unstable_get_input_id( $input, $instance_id ) {
		$id = $input;

		if ( is_array( $input ) ) {
			$id = implode( '-', $input );
		}

		return sprintf(
			'simpay-price-%1$s-%2$s',
			$id,
			$instance_id
		);
	}
}
