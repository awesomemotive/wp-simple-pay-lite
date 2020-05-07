<?php
/**
 * REST API: Controller
 *
 * @package SimplePay\Core\REST_API
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Core\REST_API;

use WP_REST_Controller;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller class.
 *
 * @since 3.5.0
 */
abstract class Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $namespace = 'wpsp/v1';

	/**
	 * Route base.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	protected $rest_base = '';

}
