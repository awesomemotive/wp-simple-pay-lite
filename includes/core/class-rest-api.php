<?php
/**
 * REST API.
 *
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
		$controllers = array(
			'\SimplePay\Core\REST_API\v2\Customer_Controller',
			'\SimplePay\Core\REST_API\v2\PaymentIntent_Controller',
			'\SimplePay\Core\REST_API\v2\Checkout_Session_Controller',
		);

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
