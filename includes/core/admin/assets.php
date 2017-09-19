<?php

namespace SimplePay\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	// Used to determine if we should load minified scripts or not
	public $min = '.min';

	private $scripts = array();

	public $styles = array();

	/**
	 * Assets constructor.
	 */
	public function __construct() {

		$this->set_minified();

		// Load admin scripts only on our own admin pages
		if ( false !== simpay_is_admin_screen() ) {

			$this->setup();

			add_action( 'admin_enqueue_scripts', array( $this, 'register' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		}

		// Enqueue our admin-bar css which needs to be on all admin pages
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_bar_css' ) );

		// Enqueue the upgrade-link on all pages
		add_action( 'admin_enqueue_scripts', array( $this, 'upgrade_link' ) );
	}

	/**
	 * Add upgrade link css and js to all pages
	 */
	public function upgrade_link() {

		// Load the CSS
		$src = SIMPLE_PAY_ASSETS . 'css/upgrade-link' . $this->min . '.css';
		wp_enqueue_style( 'simpay-upgrade-link', $src, array(), SIMPLE_PAY_VERSION, 'all' );

		// Load the JS
		$src = SIMPLE_PAY_ASSETS . 'js/upgrade-link' . $this->min . '.js';
		wp_enqueue_script( 'simpay-upgrade-link', $src, array( 'jquery' ), SIMPLE_PAY_VERSION, true );
	}

	/**
	 * Add admin bar to all admin pages so
	 */
	public function admin_bar_css() {
		$src = SIMPLE_PAY_ASSETS . 'css/admin-bar' . $this->min . '.css';
		wp_enqueue_style( 'simpay-admin-bar', $src, array(), SIMPLE_PAY_VERSION, 'all' );
	}

	public function set_minified() {

		if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) {
			$this->min = '';
		}
	}

	/**
	 * Setup arrays for both styles and scripts
	 */
	public function setup() {

		$css_path = SIMPLE_PAY_ASSETS . 'css/';
		$js_path  = SIMPLE_PAY_ASSETS . 'js/';

		$this->scripts = array(
			'simpay-chosen'          => array(
				'src'    => $js_path . 'vendor/chosen.jquery.js',
				'deps'   => array( 'jquery', 'jquery-ui-sortable' ),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
			'simpay-jquery-validate' => array(
				'src'    => $js_path . 'vendor/jquery.validate' . $this->min . '.js',
				'deps'   => array( 'jquery' ),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
			'simpay-accounting'      => array(
				'src'    => $js_path . 'vendor/accounting' . $this->min . '.js',
				'deps'   => array(),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
			'simpay-shared'          => array(
				'src'    => $js_path . 'shared' . $this->min . '.js',
				'deps'   => array( 'jquery', 'simpay-accounting' ),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
			'simpay-admin'           => array(
				'src'    => $js_path . 'admin' . $this->min . '.js',
				'deps'   => array(
					'jquery',
					'simpay-chosen',
					'simpay-jquery-validate',
					'simpay-accounting',
					'simpay-shared',
				),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
		);

		$this->styles = array(
			'simpay-chosen'              => array(
				'src'   => $css_path . 'chosen' . $this->min . '.css',
				'deps'  => array(),
				'ver'   => SIMPLE_PAY_VERSION,
				'media' => 'all',
			),
			'simpay-admin'               => array(
				'src'   => $css_path . 'admin' . $this->min . '.css',
				'deps'  => array( 'simpay-chosen' ),
				'ver'   => SIMPLE_PAY_VERSION,
				'media' => 'all',
			),
		);
	}

	/**
	 * Register scripte ad stlyes
	 */
	public function register() {

		$this->styles  = apply_filters( 'simpay_before_register_admin_styles', $this->styles, $this->min );
		$this->scripts = apply_filters( 'simpay_before_register_admin_scripts', $this->scripts, $this->min );

		if ( ! empty( $this->styles ) && is_array( $this->styles ) ) {
			foreach ( $this->styles as $style => $values ) {
				wp_register_style( $style, $values['src'], $values['deps'], $values['ver'], $values['media'] );
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
	 * Enqueue registered styles and scripts
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

		wp_enqueue_media();
	}
}
