<?php
/**
 * Model: Abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\Model;

/**
 * AbstractModel class.
 *
 * @since 4.4.5
 */
abstract class AbstractModel {

	/**
	 * Notification.
	 *
	 * @since 4.4.5
	 *
	 * @param array<mixed> $data Data to create an model from.
	 */
	public function __construct( $data ) {
		foreach ( (array) $data as $key => $value ) {
			$this->{$key} = $value;
		}
	}

}
