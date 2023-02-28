<?php
/**
 * REST API
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST_API class.
 *
 * @since 3.5.0
 */
class REST_API {

	/**
	 * Hooks in to WordPress.
	 *
	 * @since 3.5.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ), 20 );
	}

	/**
	 * Register routes.
	 *
	 * @since 3.5.0
	 */
	public function register_routes() {
		$controllers = array();

		if ( ! simpay_is_upe() ) {
			$controllers[] = '\SimplePay\Core\REST_API\v2\Checkout_Session_Controller';
		}

		/**
		 * Filter the REST API controllers.
		 *
		 * @since 3.6.0
		 *
		 * @param array $controllers List of fully qualified REST API controller class names.
		 */
		$controllers = apply_filters( 'simpay_rest_api_controllers', $controllers );

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	}

}
