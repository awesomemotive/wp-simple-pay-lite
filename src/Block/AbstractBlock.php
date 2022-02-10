<?php
/**
 * Block: Abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Block;

/**
 * AbstractBlock abstract.
 *
 * @since 4.4.2
 */
abstract class AbstractBlock implements BlockInterface {

	/**
	 * {@inheritdoc}
	 */
	abstract public function register();

}
