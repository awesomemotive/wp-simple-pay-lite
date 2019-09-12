<?php

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	// Used to determine if we should load minified scripts or not
	public $min = '.min';

	private $scripts = array();

	public $styles = array();

	public static $instance;

	public static $script_variables = array();

	/**
	 * Assets constructor.
	 */
	public function __construct() {

		$this->set_minified();

		$this->setup();

		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		add_action( 'wp_footer', array( $this, 'localize_scripts' ) );
	}

	/**
	 * Get the singleton.
	 *
	 * @return Assets
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * See if we need to minify or not.
	 */
	public function set_minified() {

		if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) {
			$this->min = '';
		}
	}

	/**
	 * Setup arrays for both scripts & styles.
	 */
	public function setup() {

		$css_path = SIMPLE_PAY_ASSETS . 'css/';
		$js_path  = SIMPLE_PAY_ASSETS . 'js/';

		$this->scripts = array(
			'simpay-stripe-js-v3'    => array(
				'src'    => 'https://js.stripe.com/v3/',
				'deps'   => array(),
				'ver'    => null,
				'footer' => true,
			),
			'simpay-accounting'      => array(
				'src'    => $js_path . 'vendor/accounting' . $this->min . '.js',
				'deps'   => array(),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
			'simpay-shared'          => array(
				'src'    => $js_path . 'shared' . $this->min . '.js',
				'deps'   => array( 'jquery', 'simpay-accounting' ),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
			'simpay-public'    => array(
				'src'    => $js_path . 'public.min.js',
				'deps'   => array(
					'jquery',
					'wp-api',
					'simpay-accounting',
					'simpay-shared',
				),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
		);

		// Check if CSS is disabled and if not then load the array with our styles
		if ( 'disabled' !== simpay_get_global_setting( 'default_plugin_styles' ) ) {

			$this->styles = array(
				'google-font-roboto'     => array(
					'src'   => 'https://fonts.googleapis.com/css?family=Roboto',
					'deps'  => array(),
					'ver'   => null,
					'media' => 'all',
				),
				'stripe-checkout-button' => array(
					'src'   => 'https://checkout.stripe.com/v3/checkout/button.css',
					'deps'  => array(),
					'ver'   => null,
					'media' => 'all',
				),
				'simpay-public'          => array(
					'src'   => $css_path . 'public' . $this->min . '.css',
					'deps'  => array( 'google-font-roboto', 'stripe-checkout-button' ),
					'ver'   => SIMPLE_PAY_VERSION,
					'media' => 'all',
				),
			);
		}
	}

	/**
	 * Register scripts & styles
	 */
	public function register() {

		$this->styles  = apply_filters( 'simpay_before_register_public_styles', $this->styles, $this->min );
		$this->scripts = apply_filters( 'simpay_before_register_public_scripts', $this->scripts, $this->min );

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
	 * Enqueue registered scripts & styles
	 */
	public function enqueue() {

		if ( ! empty( $this->styles ) && is_array( $this->styles ) ) {
			foreach ( $this->styles as $style => $value ) {
				wp_enqueue_style( $style );
			}
		}

		if ( ! empty( $this->scripts ) && is_array( $this->scripts ) ) {
			foreach ( $this->scripts as $script => $value ) {
				wp_enqueue_script( $script );
			}
		}

		simpay_shared_script_variables();
	}

	/**
	 * Localize our public script
	 */
	public function localize_scripts() {
		wp_localize_script( 'simpay-public', 'simplePayForms', self::$script_variables );
	}

	/**
	 * Use this function to add to our script variables to localize later
	 *
	 * @param $vars
	 */
	public static function script_variables( $vars ) {

		// Do not array_merge here because it will just overwrite all the keys of previous forms. We need to keep them all.
		self::$script_variables = self::$script_variables + $vars;


	}
}
