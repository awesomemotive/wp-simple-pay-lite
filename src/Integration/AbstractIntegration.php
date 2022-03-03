<?php
/**
 * Integration: Abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * AbstractIntegration class.
 *
 * @since 4.4.3
 */
abstract class AbstractIntegration extends AbstractPluginServiceProvider implements IntegrationInterface {

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_id();

	/**
	 * {@inheritdoc}
	 */
	abstract public function is_active();

}
