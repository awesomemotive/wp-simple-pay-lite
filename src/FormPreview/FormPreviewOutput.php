<?php
/**
 * Form preview: Output
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\FormPreview;

use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * FormPreviewOutput class.
 *
 * @since 4.4.2
 */
class FormPreviewOutput implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		// Render on `template_redirect`, after the main query has run, so
		// third-party `wp_head`/`wp_enqueue_scripts` callbacks (e.g. Popup
		// Maker) see a normal front-end environment. Rendering on `init`
		// fired those callbacks before WordPress conditionals were available
		// and caused fatal errors. Priority 1 runs before
		// `redirect_canonical` so the preview URL cannot be redirected away.
		//
		// `template_redirect` only fires when the site uses themes, so sites
		// that turn them off (`WP_USE_THEMES` false) render on `wp` instead.
		return array(
			'template_redirect' => array( 'maybe_output_preview', 1 ),
			'wp'                => array( 'maybe_output_preview_without_themes', 99 ),
		);
	}

	/**
	 * Outputs a payment form preview on sites that do not use themes.
	 *
	 * WordPress only fires `template_redirect` when themes are in use, so
	 * without this the preview would not render on a site that defines
	 * `WP_USE_THEMES` as false (e.g. some headless setups).
	 *
	 * @since 4.17.7
	 *
	 * @return void
	 */
	public function maybe_output_preview_without_themes() {
		if ( wp_using_themes() ) {
			return;
		}

		$this->maybe_output_preview();
	}

	/**
	 * Outputs a payment form preview if the requested URL is correct.
	 *
	 * @since 4.4.2
	 * @since 4.17.7 Responds with 403 Forbidden to visitors without permission.
	 *
	 * @return void
	 */
	public function maybe_output_preview() {
		if ( ! isset( $_GET['simpay-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			// 403 Forbidden, not wp_die()'s default 500.
			wp_die(
				esc_html__(
					'You do not have permission to preview this payment form.',
					'stripe'
				),
				'',
				array(
					'response' => 403,
				)
			);
		}

		$id   = absint( $_GET['simpay-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form = simpay_get_form( $id );

		if ( false === $form ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type' => 'simple-pay',
					),
					admin_url( 'edit.php' )
				)
			);
			exit;
		}

		$edit_form_url = add_query_arg(
			array(
				'post'   => $id,
				'action' => 'edit',
			),
			admin_url( 'post.php' )
		);

		$payment_page_enabled = get_post_meta( $id, '_enable_payment_page', true );
		$payment_page_url     = 'yes' === $payment_page_enabled
			? get_permalink( $id )
			: '';

		wp_enqueue_script( 'clipboard' );
		wp_enqueue_script( 'wp-a11y' );
		wp_enqueue_style( 'dashicons' );

		include_once SIMPLE_PAY_DIR . '/views/admin-form-preview-output.php';
		exit;
	}
}
