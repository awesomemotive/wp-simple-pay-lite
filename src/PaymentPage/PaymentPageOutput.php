<?php
/**
 * Payment Page: Output
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.0
 */

namespace SimplePay\Core\PaymentPage;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Payments\Payment_Confirmation;

/**
 * PaymentPageOutput class.
 *
 * @since 4.5.0
 */
class PaymentPageOutput implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Payment form being shown.
	 *
	 * @since 4.5.0
	 * @var \SimplePay\Core\Abstracts\Form|false $form Payment form.
	 */
	private $form = false;

	/**
	 * Event manager.
	 *
	 * @since 4.5.0
	 * @var \SimplePay\Core\EventManagement\EventManager $events Event manager.
	 */
	private $events;

	/**
	 * PaymentPageOutput.
	 *
	 * @since 4.5.0
	 *
	 * @param \SimplePay\Core\EventManagement\EventManager $events Event manager.
	 */
	public function __construct( $events ) {
		$this->events = $events;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( true === $this->license->is_lite() ) {
			return array();
		}

		return array(
			'parse_request' => 'parse_pretty_request',
		);
	}

	/**
	 * Parses "pretty" permalink requests and shows a payment form if needed.
	 *
	 * @since 4.5.0
	 *
	 * @param \WP $wp WordPress request.
	 * @return void
	 */
	public function parse_pretty_request( $wp ) {
		// Do not take over the request if we are returning from a payment method redirect.
		if ( isset( $_GET['payment_intent'] ) ) {
			return;
		}

		if ( ! empty( $wp->query_vars['name'] ) ) {
			$request = $wp->query_vars['name'];
		}

		if ( empty( $request ) && ! empty( $wp->query_vars['pagename'] ) ) {
			$request = $wp->query_vars['pagename'];
		}

		if ( empty( $request ) ) {
			$request = ! empty( $_SERVER['REQUEST_URI'] )
				? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
				: '';

			$path    = (string) wp_parse_url( $request, PHP_URL_PATH );
			$request = ! empty( $request ) ? sanitize_key( $path ) : '';
		}

		$payment_form_obj = get_page_by_path( $request, OBJECT, 'simple-pay' );

		if ( null === $payment_form_obj ) {
			return;
		}

		// Set the "Success Page" redirect _before_ we call the form, but when
		// we are pretty sure we are on a Payment Page.
		$this->events->add_callback(
			'simpay_payment_success_page',
			array( $this, 'set_self_redirect' ),
			10,
			2
		);

		$this->form = simpay_get_form( $payment_form_obj->ID );

		if (
			false === $this->form ||
			false === $this->is_payment_page_enabled( $payment_form_obj->ID )
		) {
			return;
		}

		// Override page URLs with the same slug.
		if ( ! empty( $wp->query_vars['pagename'] ) ) {
			$wp->query_vars['name'] = $wp->query_vars['pagename'];
			unset( $wp->query_vars['pagename'] );
		}

		if ( empty( $wp->query_vars['name'] ) ) {
			$wp->query_vars['name'] = $request;
		}

		$wp->query_vars['post_type'] = 'simple-pay';

		// Unset 'error' query var that may appear if custom permalink structures used.
		unset( $wp->query_vars['error'] );

		$this->compat_hooks();
		$this->output( $payment_form_obj->ID );
	}

	/**
	 * Additional compatibility.
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	private function compat_hooks() {
		// Remove <head> data.
		$this->events->remove_callback(
			'wp_head',
			'adjacent_posts_rel_link_wp_head'
		);
		$this->events->remove_callback(
			'wp_head',
			'wp_oembed_add_discovery_links'
		);
		$this->events->remove_callback(
			'wp_head',
			'wp_oembed_add_host_js'
		);

		// Update <title> tag.
		// Remove conditional title tag rendering...
		$this->events->remove_callback(
			'wp_head',
			'_wp_render_title_tag',
			1
		);

		// ...and make it unconditional.
		$this->events->add_callback(
			'wp_head',
			'_block_template_render_title_tag',
			1
		);

		// Override parts.
		$this->events->add_callback(
			'document_title_parts',
			array( $this, 'change_payment_page_title' )
		);

		// Asset Compatibility.
		$this->events->add_callback(
			'wp_print_styles',
			array( $this, 'css_compatibility_mode' ),
			99
		);

		$this->events->add_callback(
			'wp_print_scripts',
			array( $this, 'js_compatibility_mode' ),
			99
		);

		/**
		 * Allows Payment Page compatibility to be added/modified.
		 *
		 * @since 4.5.0
		 *
		 * @param \SimplePay\Core\EventManagement\EventManager $events
		 */
		do_action( 'simpay_before_payment_page_output', $this->events );
	}

	/**
	 * Update the payment confirmation URL if receipt should show on the same page.
	 *
	 * @since 4.5.0
	 *
	 * @param string $url Success page URL.
	 * @param int    $form_id Payment form ID.
	 * @return string
	 */
	public function set_self_redirect( $url, $form_id ) {
		// Return standard success URL if Payment Page is not enabled.
		if ( false === $this->is_payment_page_enabled( $form_id ) ) {
			return $url;
		}

		$self_confirmation = get_post_meta(
			$form_id,
			'_payment_page_self_confirmation',
			true
		);

		// Return standard success URL if self confirmation is not enabled.
		if ( 'no' === $self_confirmation ) {
			return $url;
		}

		$redirect_url = get_permalink( $form_id );

		if ( ! is_string( $redirect_url ) ) {
			return $url;
		}

		return $redirect_url;
	}

	/**
	 * Use the payment form's title as the page title.
	 *
	 * @since 4.5.0
	 *
	 * @param array<string, string> $title Title parts.
	 * @return array<string, string>
	 */
	public function change_payment_page_title( $title ) {
		if ( false === $this->form ) {
			return $title;
		}

		/** @var string $form_title */
		$form_title = get_post_meta(
			$this->form->id,
			'_company_name',
			true
		);

		$title['title'] = $form_title;

		return $title;
	}

	/**
	 * Outputs the payment page.
	 *
	 * @since 4.5.0
	 *
	 * @param int $form_id Payment forM ID.
	 * @return void
	 */
	private function output( $form_id ) {
		/** @var string $background_color */
		$background_color = get_post_meta(
			$form_id,
			'_payment_page_background_color',
			true
		);

		$darker_background_color = $this->adjust_color_brightness(
			$background_color,
			-20
		);

		$title_desc = get_post_meta(
			$form_id,
			'_payment_page_title_description',
			true
		);

		$footer_text = get_post_meta(
			$form_id,
			'_payment_page_footer_text',
			true
		);

		$powered_by = get_post_meta(
			$form_id,
			'_payment_page_powered_by',
			true
		);

		$image = get_post_meta(
			$form_id,
			'_payment_page_image_url',
			true
		);

		$title = get_post_meta(
			$form_id,
			'_company_name',
			true
		);

		$desc = get_post_meta(
			$form_id,
			'_item_description',
			true
		);

		$is_confirmation = ! empty(
			Payment_Confirmation\get_confirmation_data()
		);

		include_once SIMPLE_PAY_DIR . '/views/payment-page-output.php'; // @phpstan-ignore-line
		exit;
	}

	/**
	 * Unload CSS potentially interfering with Payment Pages.
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function css_compatibility_mode() {
		$enable_css_compatibility_mode = true;

		/**
		 * Filters if "CSS Compatibility" mode should be active for Payment Pages.
		 *
		 * This removes all non-WP Simple Pay styles from the page.
		 *
		 * @since 4.5.0
		 *
		 * @param bool $enable_css_compatibility_mode Enable "CSS Compatibility" mode for payment pages.
		 */
		$enable_css_compatibility_mode = apply_filters(
			'simpay_payment_page_css_compatibility_mode',
			$enable_css_compatibility_mode
		);

		if ( false === $enable_css_compatibility_mode ) {
			return;
		}

		$this->asset_compatibility_mode( 'css' );
	}

	/**
	 * Unload JS potentially interfering with Payment Pages.
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function js_compatibility_mode() {
		$enable_js_compatibility_mode = true;

		/**
		 * Filters if "JS Compatibility" mode should be active for Payment Pages.
		 *
		 * This removes all non-WP Simple Pay styles from the page.
		 *
		 * @since 4.5.0
		 *
		 * @param bool $enable_js_compatibility_mode Enable "JS Compatibility" mode for payment pages.
		 */
		$enable_js_compatibility_mode = apply_filters(
			'simpay_payment_page_js_compatibility_mode',
			$enable_js_compatibility_mode
		);

		if ( false === $enable_js_compatibility_mode ) {
			return;
		}

		$this->asset_compatibility_mode( 'js' );
	}

	/**
	 * Dequeues assets (CSS or JS) that are not a part of the plugin.
	 *
	 * @since 4.5.0
	 *
	 * @param string $asset_type Asset type. `css` or `js`.
	 * @return void
	 */
	private function asset_compatibility_mode( $asset_type ) {
		$assets = 'css' === $asset_type ? wp_styles() : wp_scripts();

		if ( empty( $assets->queue ) ) {
			return;
		}

		$simpay_payment_page_asset_handle_allowlist = array(
			'wp-',
			'simpay-',
			'sandhills',
		);

		/**
		 * Filters the list of asset handles that should be allowed on Payment Pages.
		 *
		 * @since 4.5.0
		 *
		 * @param array<string> $simpay_payment_page_asset_handle_allowlist List of asset handles that should be allowed.
		 */
		$simpay_payment_page_asset_handle_allowlist = apply_filters(
			'simpay_payment_page_asset_handle_allowlist',
			$simpay_payment_page_asset_handle_allowlist
		);

		$non_simpay_assets = array_filter(
			$assets->queue,
			function( $handle ) use ( $simpay_payment_page_asset_handle_allowlist ) {
				$allowed = true;

				foreach ( $simpay_payment_page_asset_handle_allowlist as $allowed_handle ) {
					if ( false !== strpos( $handle, $allowed_handle ) ) {
						$allowed = false;
					}
				}

				return $allowed;
			}
		);

		foreach ( $non_simpay_assets as $handle ) {
			if ( 'css' === $asset_type ) {
				wp_dequeue_style( $handle );
			} else {
				wp_dequeue_script( $handle );
			}
		}
	}

	/**
	 * Determines if Payment Page mode is enabled for a payment form ID.
	 *
	 * @since 4.5.0
	 *
	 * @param int $form_id Payment form ID.
	 * @return bool
	 */
	private function is_payment_page_enabled( $form_id ) {
		$enabled = get_post_meta(
			$form_id,
			'_enable_payment_page',
			true
		);

		return 'yes' === $enabled;
	}

	/**
	 * Returns a darkened version of a hexidecimal code.
	 *
	 * @since 4.5.0
	 *
	 * @param string $color Hexidecimal code to darken.
	 * @param int    $steps Number of steps to adjust.
	 * @return string
	 */
	private function adjust_color_brightness( $color, $steps ) {
		$steps = max( -255, min( 255, $steps ) );
		$hex   = str_replace( '#', '', $color );

		if ( strlen( $hex ) === 3 ) {
			$hex =
				str_repeat( substr( $hex, 0, 1 ), 2 ) .
				str_repeat( substr( $hex, 1, 1 ), 2 ) .
				str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Split into three parts: R, G and B.
		$color_parts = str_split( $hex, 2 );
		$return      = '#';

		foreach ( $color_parts as $color ) {
			$color   = hexdec( $color );
			$color   = (int) max( 0, min( 255, $color + $steps ) );
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT );
		}

		return $return;
	}

}
