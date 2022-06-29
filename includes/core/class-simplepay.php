<?php
/**
 * SimplePay
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core;

use SimplePay\Core\Forms\Preview;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SimplePay class.
 *
 * @since 3.0.0
 */
final class SimplePay {

	/**
	 * Objects factory.
	 *
	 * @since 3.0.0
	 * @var SimplePay\Core\Objects
	 */
	public $objects = null;

	/**
	 * Sessions.
	 *
	 * @since 3.0.0
	 * @since 3.6.0 No longer used.
	 * @var null
	 */
	public $session = null;

	/**
	 * The single instance of this class.
	 *
	 * @since 3.0.0
	 * @var \SimplePay\Core\SimplePay
	 */
	protected static $_instance = null;

	/**
	 * Main Simple Pay instance
	 *
	 * Ensures only one instance of Simple Pay is loaded or can be loaded.
	 *
	 * @since 3.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 3.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'stripe' ), '3.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'stripe' ), '3.0' );
	}

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->load();

		register_activation_hook( SIMPLE_PAY_MAIN_FILE, array( 'SimplePay\Core\Installation', 'activate' ) );
		register_deactivation_hook( SIMPLE_PAY_MAIN_FILE, array( 'SimplePay\Core\Installation', 'deactivate' ) );

		do_action( 'simpay_loaded' );
	}

	/**
	 * Load the plugin.
	 *
	 * @since 3.0.0
	 */
	public function load() {
		// Vendors.
		require_once( SIMPLE_PAY_INC . 'core/utils/class-persistent-dismissible.php' );

		// i18n.
		require_once( SIMPLE_PAY_INC . 'core/i18n/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/i18n/countries.php' );
		require_once( SIMPLE_PAY_INC . 'core/i18n/stripe.php' );

		// Migrations.
		require_once( SIMPLE_PAY_INC . 'core/utils/migrations/admin.php' );
		require_once( SIMPLE_PAY_INC . 'core/utils/migrations/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/utils/migrations/register.php' );

		// Settings.
		require_once( SIMPLE_PAY_INC . 'core/settings/register.php' );
		require_once( SIMPLE_PAY_INC . 'core/settings/register-stripe.php' );
		require_once( SIMPLE_PAY_INC . 'core/settings/register-general.php' );
		require_once( SIMPLE_PAY_INC . 'core/settings/register-payment-confirmations.php' );
		require_once( SIMPLE_PAY_INC . 'core/settings/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/settings/display.php' );
		require_once( SIMPLE_PAY_INC . 'core/settings/compat.php' );

		// Post types.
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/register.php' );
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/meta.php' );

		// Load core shared back-end & front-end functions.
		require_once( SIMPLE_PAY_INC . 'core/utils/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/utils/exceptions.php' );
		require_once( SIMPLE_PAY_INC . 'core/utils/collections.php' );
		require_once( SIMPLE_PAY_INC . 'core/functions/template.php' );
		require_once( SIMPLE_PAY_INC . 'core/functions/shared.php' );
		require_once( SIMPLE_PAY_INC . 'core/functions/countries.php' );

		// Payment form.
		require_once( SIMPLE_PAY_INC . 'core/forms/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/forms/class-price-option.php' );
		require_once( SIMPLE_PAY_INC . 'core/forms/class-price-options.php' );

		// Payments/Purchase Flow.
		require_once( SIMPLE_PAY_INC . 'core/payments/customer.php' );
		require_once( SIMPLE_PAY_INC . 'core/payments/paymentintent.php' );
		require_once( SIMPLE_PAY_INC . 'core/payments/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'core/payments/payment-confirmation-template-tags.php' );

		// API.
		require_once( SIMPLE_PAY_INC . 'core/api/products.php' );
		require_once( SIMPLE_PAY_INC . 'core/api/plans.php' );
		require_once( SIMPLE_PAY_INC . 'core/api/prices.php' );
		require_once( SIMPLE_PAY_INC . 'core/api/tax-rates.php' );
		require_once( SIMPLE_PAY_INC . 'core/api/customers.php' );
		require_once( SIMPLE_PAY_INC . 'core/api/paymentmethods.php' );
		require_once( SIMPLE_PAY_INC . 'core/api/setupintents.php' );
		require_once( SIMPLE_PAY_INC . 'core/api/coupons.php' );

		// REST API.
		new REST_API();
		require_once( SIMPLE_PAY_INC . 'core/rest-api/functions.php' );

		// Stripe Checkout functionality.
		require_once( SIMPLE_PAY_INC . 'core/payments/stripe-checkout/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/payments/stripe-checkout/session.php' );

		// Stripe Connect functionality.
		require_once( SIMPLE_PAY_INC . 'core/stripe-connect/functions.php' );

		// reCAPTCHA.
		require_once( SIMPLE_PAY_INC . 'core/recaptcha/index.php' );
		require_once( SIMPLE_PAY_INC . 'core/recaptcha/settings.php' );

		// Legacy.
		require_once( SIMPLE_PAY_INC . 'core/legacy/functions.php' );
		require_once( SIMPLE_PAY_INC . 'core/legacy/hooks.php' );
		require_once( SIMPLE_PAY_INC . 'core/legacy/class-payment-form.php' );

		// Rate Limiting.
		$rate_limiting = new Utils\Rate_Limiting();
		$rate_limiting->init();

		$this->objects = new Objects();

		new Shortcodes();

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->load_admin();
		} else {
			Assets::get_instance();
			new Cache_Helper();
		}
	}

	/**
	 * Load the plugin admin.
	 *
	 * @since 3.0.0
	 */
	public function load_admin() {
		// Post types.
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/compat.php' );
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/menu.php' );
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/list-table.php' );
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/edit-form.php' );
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/edit-form-payment-options.php' );
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/edit-form-stripe-checkout.php' );
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/edit-form-custom-fields.php' );
		require_once( SIMPLE_PAY_INC . 'core/post-types/simple-pay/actions.php' );

		// Load core back-end only functions.
		require_once( SIMPLE_PAY_INC . 'core/functions/admin.php' );
		require_once( SIMPLE_PAY_INC . 'core/admin/functions/notices.php' );
		require_once( SIMPLE_PAY_INC . 'core/admin/functions/plugin-upgrade-notice.php' );

		new Admin\Assets();
		new Admin\Menus();
		new Admin\Notice_Manager();
	}
}

/**
 * Start WP Simple Pay.
 *
 * @since 3.0.0
 *
 * @return \SimplePay\Core\SimplePay
 */
function SimplePay() {
	return SimplePay::instance();
}

SimplePay();
