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
					'simple-pay'
				)
			);
		}

		$id = absint( $_GET['simpay-preview'] );

		$edit_form_url = add_query_arg(
			array(
				'post'   => $id,
				'action' => 'edit',
			),
			admin_url( 'post.php' )
		);

		wp_enqueue_script( 'clipboard' );
		wp_enqueue_script( 'wp-a11y' );
		wp_enqueue_style( 'dashicons' );

		wp_head();
		?>
<html>
	<head>
		<title>
			<?php
			echo esc_html(
				sprintf(
					__( 'Previewing Payment Form #%d - WP Simple Pay', 'simple-pay' ),
					$id
				)
			);
			?>
		</title>
	</head>
	<body class="simpay-form-preview">
		<div class="simpay-form-preview-notice">
			<p>
				<?php
				esc_html_e(
					'This is a preview of your payment form. This page is not publicly accessible.',
					'simple-pay'
				);
				?>
			</p>

			<?php if ( in_array( get_post_status( $id ), array( 'pending', 'draft' ), true ) ) : ?>
				<p>
					<strong>
						<?php
						esc_html_e(
							'This payment form is currently unpublished and will not be able to accept payments until it is published.',
							'simple-pay'
						);
						?>
					</strong>
				</p>
			<?php endif; ?>

			<p style="margin-top: 20px;">
				<?php
				esc_html_e(
					'To add your payment form to a page, use the "WP Simple Pay" block, or embed the shortcode.',
					'simple-pay'
				);
				?>
			</p>

			<p style="display: flex; align-items: center;">
				<button
					data-clipboard-text='[simpay id="<?php echo esc_attr( (string) $id ); ?>"]'
					data-copied="<?php echo esc_attr__( 'Copied!', 'simple-pay' ); ?>"
					class="simpay-copy-button button"
				>
					<?php esc_html_e( 'Copy Shortcode', 'simple-pay' ); ?>
				</button>

				<button
					data-clipboard-text='<!-- wp:simpay/payment-form {"formId":<?php echo esc_attr( (string) $id ); ?>} /-->'
					data-copied="<?php echo esc_attr__( 'Copied!', 'simple-pay' ); ?>"
					style="margin-left: 8px;"
					class="simpay-copy-button button"
				>
					<?php esc_html_e( 'Copy Block', 'simple-pay' ); ?>
				</button>
			</p>

			<p style="margin-top: 20px;">
				<a href="<?php echo esc_url( $edit_form_url ); ?>" style="text-decoration: none; display: flex; align-items: center;">
					<span class="dashicons dashicons-edit"></span>
					<span style="margin-left: 5px;">
						<?php esc_html_e( 'Continue Editing', 'simple-pay' ); ?>
					</span>
				</a>
			</p>
		</div>

		<?php echo do_shortcode( sprintf( '[simpay id="%d"]', $id ) ); ?>

		<?php wp_footer(); ?>

		<script>
			var clipboard = new ClipboardJS( '.simpay-copy-button' );

			clipboard.on( 'success', function ( e ) {
				var buttonEl = e.trigger;
				var copiedText = buttonEl.dataset.copied;
				var originalText = buttonEl.innerHTML;

				buttonEl.innerHTML = copiedText;

				// Hide success visual feedback after 3 seconds since last success.
				var successTimeout = setTimeout( function () {
					buttonEl.innerHTML = originalText;

					// Remove the visually hidden textarea so that it isn't perceived by assistive technologies.
					if (
						clipboard.clipboardAction.fakeElem &&
						clipboard.clipboardAction.removeFake
					) {
						clipboard.clipboardAction.removeFake();
					}
				}, 3000 );

				e.clearSelection();

				// Handle success audible feedback.
				wp.a11y.speak( copiedText );
			} );
		</script>
	</body>
</html>
		<?php
		exit;
	}

}
