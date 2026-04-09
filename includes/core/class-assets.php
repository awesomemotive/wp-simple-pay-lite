<?php
/**
 * Assets
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class.
 *
 * @since 3.0.0
 */
class Assets {

	/**
	 * Scripts.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public $scripts = array();

	/**
	 * Styles.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public $styles = array();

	/**
	 * Class instance.
	 *
	 * @since 3.0.0
	 * @var \SimplePay\Core\Assets
	 */
	public static $instance;

	/**
	 * Script variables.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	public static $script_variables = array();

	/**
	 * Whether frontend assets have been enqueued.
	 *
	 * @since 4.17.1
	 * @var bool
	 */
	private static $frontend_assets_enqueued = false;

	/**
	 * Hooks in to WordPress.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->setup();

		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_stripe_js' ) );

		add_action( 'wp_footer', array( $this, 'localize_scripts' ) );
	}

	/**
	 * Get the singleton.
	 *
	 * @since 3.0.0
	 *
	 * @return \SimplePay\Core\Assets
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup arrays for both scripts & styles.
	 *
	 * @since 3.0.0
	 */
	public function setup() {
		$public_js = SIMPLE_PAY_INC_URL . 'core/assets/js/dist/simpay-public-upe.js';

		$public_deps = array(
			'jquery',
			'wp-a11y',
			'wp-api-fetch',
			'simpay-shared',
		);

		$this->scripts = array(
			'sandhills-stripe-js-v3' => array(
				'src'    => 'https://js.stripe.com/v3/',
				'deps'   => array(),
				'ver'    => null,
				'footer' => true,
			),

			'simpay-accounting'      => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/vendor/accounting.min.js',
				'deps'   => array(),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
			'simpay-shared'          => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/dist/simpay-public-shared.js',
				'deps'   => array(),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
			'simpay-public'          => array(
				'src'    => $public_js,
				'deps'   => $public_deps,
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
		);

		$this->styles = array(
			'stripe-checkout-button' => array(
				'src'   => 'https://checkout.stripe.com/v3/checkout/button.css',
				'deps'  => array(),
				'ver'   => null,
				'media' => 'all',
			),
			'simpay-public'          => array(
				'src'   => SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-public.min.css',
				'deps'  => array( 'stripe-checkout-button' ),
				'ver'   => SIMPLE_PAY_VERSION,
				'media' => 'all',
			),
		);
	}

	/**
	 * Register scripts & styles.
	 *
	 * @since 3.0.0
	 */
	public function register() {
		/**
		 * Filters the styles before they are registered.
		 *
		 * @since 3.0.0
		 * @since 3.7.0 $min Always '.min'
		 *
		 * @param array  $styles List of styles to register.
		 * @param string $min Suffix for minification.
		 */
		$this->styles = apply_filters( 'simpay_before_register_public_styles', $this->styles, '.min' );

		/**
		 * Filters the scripts before they are registered.
		 *
		 * @since 3.0.0
		 * @since 3.7.0 $min Always '.min'
		 *
		 * @param array  $styles List of scripts to register.
		 * @param string $min Suffix for minification.
		 */
		$this->scripts = apply_filters( 'simpay_before_register_public_scripts', $this->scripts, '.min' );

		if ( ! empty( $this->styles ) && is_array( $this->styles ) ) {
			foreach ( $this->styles as $style => $values ) {

				if ( is_array( $values ) ) {
					wp_register_style( $style, $values['src'], $values['deps'], $values['ver'], $values['media'] );
				}
			}
		}

		if ( ! empty( $this->scripts ) && is_array( $this->scripts ) ) {
			foreach ( $this->scripts as $script => $values ) {

				if ( is_array( $values ) ) {
					wp_register_script( $script, $values['src'], $values['deps'], $values['ver'], $values['footer'] );
				}
			}
		}
	}

	/**
	 * Enqueue Stripe.js on every page for advanced fraud detection.
	 *
	 * Stripe recommends including Stripe.js on every page to leverage
	 * advanced fraud detection signals across the entire site.
	 *
	 * @link https://docs.stripe.com/disputes/prevention/advanced-fraud-detection
	 *
	 * @since 4.17.1
	 */
	public function enqueue_stripe_js() {
		wp_enqueue_script( 'sandhills-stripe-js-v3' );
	}

	/**
	 * Enqueue registered scripts & styles on demand when a form is rendered.
	 *
	 * @since 3.0.0
	 * @since 4.17.1 Changed to static, on-demand enqueuing only when a form is present.
	 */
	public static function enqueue_frontend_assets() {
		if ( self::$frontend_assets_enqueued ) {
			return;
		}

		self::$frontend_assets_enqueued = true;

		$instance = self::get_instance();

		if ( ! empty( $instance->styles ) && is_array( $instance->styles ) ) {
			foreach ( $instance->styles as $style => $value ) {
				wp_enqueue_style( $style );
			}
		}

		if ( ! empty( $instance->scripts ) && is_array( $instance->scripts ) ) {
			foreach ( $instance->scripts as $script => $value ) {
				wp_enqueue_script( $script );
			}
		}
	}

	/**
	 * Localize our public script
	 *
	 * @since 3.0.0
	 */
	public function localize_scripts() {
		$shared_vars = simpay_shared_script_variables();

		wp_localize_script( 'simpay-public', 'simplePayForms', self::$script_variables );
		wp_localize_script( 'simpay-shared', 'spGeneral', $shared_vars );
	}

	/**
	 * Use this function to add to our script variables to localize later
	 *
	 * @since 3.0.0
	 *
	 * @param array $vars Script variables.
	 */
	public static function script_variables( $vars ) {

		// Do not array_merge here because it will just overwrite all the keys of previous forms. We need to keep them all.
		self::$script_variables = self::$script_variables + $vars;
	}
}
