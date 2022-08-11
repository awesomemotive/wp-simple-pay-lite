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
		return array(
			'init' => 'maybe_output_preview',
		);
	}

	/**
	 * Outputs a payment form preview if the requested URL is correct.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function maybe_output_preview() {
		if ( ! isset( $_GET['simpay-preview'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__(
					'You do not have permission to preview this payment form.',
					'stripe'
				)
			);
		}

		$id   = absint( $_GET['simpay-preview'] );
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

		wp_head();
		?>

		<?php
        include_once SIMPLE_PAY_DIR . '/views/admin-form-preview-output.php'; // @phpstan-ignore-line
		exit;
	}

}
