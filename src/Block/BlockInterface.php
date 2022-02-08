<?php
/**
 * Block: Interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Block;

/**
 * BlockInterface interface.
 *
 * @since 4.4.2
 */
interface BlockInterface {

	/**
	 * Registers the block type.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function register();

}
