<?php
/**
 * Admin assets
 *
 * @package SimplePay\Core\Admin
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class
 *
 * @since 3.0.0
 */
class Assets {

	/**
	 * All scripts.
	 *
	 * @var array
	 */
	private $scripts = array();

	/**
	 * All styles.
	 *
	 * @var array
	 */
	public $styles = array();

	/**
	 * Assets constructor.
	 */
	public function __construct() {
		$this->setup();

		add_action( 'admin_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		// Load admin scripts & styles on all admin pages.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_on_all_admin_pages' ) );
	}

	/**
	 * Enqueue JS & CSS file to run on all admin pages (in our own settings or not).
	 * Admin bar, inline plugin update message, etc.
	 */
	public function enqueue_on_all_admin_pages() {

		// CSS.
		$src = SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin-all-pages.min.css';
		wp_enqueue_style( 'simpay-admin-all-pages', $src, array(), SIMPLE_PAY_VERSION, 'all' );

		// Notices.
		wp_enqueue_script(
			'simpay-notices',
			SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-admin-notices.min.js',
			array( 'wp-util', 'jquery' ),
			SIMPLE_PAY_VERSION,
			true
		);
	}

	/**
	 * Setup arrays for both styles and scripts
	 */
	public function setup() {
		$this->scripts = array(
			'simpay-chosen'     => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/vendor/chosen.jquery.min.js',
				'deps'   => array( 'jquery', 'jquery-ui-sortable' ),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
			'simpay-accounting' => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/vendor/accounting.min.js',
				'deps'   => array(),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
			'simpay-shared'     => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-public-shared.min.js',
				'deps'   => array( 'jquery', 'simpay-accounting' ),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
			'simpay-admin'      => array(
				'src'    => SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-admin.min.js',
				'deps'   => array(
					'jquery',
					'simpay-chosen',
					'simpay-accounting',
					'simpay-shared',
					'wp-util',
				),
				'ver'    => SIMPLE_PAY_VERSION,
				'footer' => false,
			),
		);

		$this->styles = array(
			'simpay-chosen' => array(
				'src'   => SIMPLE_PAY_INC_URL . 'core/assets/css/vendor/chosen/chosen.min.css',
				'deps'  => array(),
				'ver'   => SIMPLE_PAY_VERSION,
				'media' => 'all',
			),
			'simpay-admin'  => array(
				'src'   => SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin.min.css',
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
		/**
		 * Filters the styles before they are registered in the admin.
		 *
		 * @since 3.0.0
		 * @since 3.7.0 $min Always '.min'
		 *
		 * @param array  $styles List of styles to register.
		 * @param string $min Suffix for minification.
		 */
		$this->styles = apply_filters( 'simpay_before_register_admin_styles', $this->styles, '.min' );

		/**
		 * Filters the scripts before they are registered in the admin.
		 *
		 * @since 3.0.0
		 * @since 3.7.0 $min Always '.min'
		 *
		 * @param array  $styles List of scripts to register.
		 * @param string $min Suffix for minification.
		 */
		$this->scripts = apply_filters( 'simpay_before_register_admin_scripts', $this->scripts, '.min' );

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
		if ( false === simpay_is_admin_screen() ) {
			return;
		}

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
