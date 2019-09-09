<?php
/**
 * REST API controller.
 *
 * @since 3.5.0
 */

namespace SimplePay\Core\REST_API;

use WP_REST_Controller;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wpsp/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '';

}
