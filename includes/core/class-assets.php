<?php
/**
 * Assets
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	private $scripts = array();

	public $styles = array();

	public static $instance;

	public static $script_variables = array();

	/**
	 * Assets constructor.
	 */
	public function __construct() {
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
	 * Setup arrays for both scripts & styles.
	 */
	public function setup() {
		$this->scripts = array(
			'simpay-stripe-js-v3' => array(
				'src'    => 'https://js.stripe.com/v3/',
				'deps'   => array(),
				'ver'    => null,
				'footer' => true,
			),
			'simpay-polyfill'     => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-polyfill.min.js',
				'deps'   => array(),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
			'simpay-accounting'   => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/vendor/accounting.min.js',
				'deps'   => array(),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
			'simpay-shared'       => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-public-shared.min.js',
				'deps'   => array( 'jquery', 'simpay-accounting' ),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => true,
			),
			'simpay-public'       => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-public.min.js',
				'deps'   => array(
					'jquery',
					'underscore',
					'wp-api',
					'simpay-polyfill',
					'simpay-accounting',
					'simpay-shared',
				),
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
	 * Register scripts & styles
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
