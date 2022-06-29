<?php
/**
 * Admin: Preview Payment Form Output
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 *
 * @var int $id get post ID
 * @var string $edit_form_url URL of the edit form page
 */

?><html>
<head>
	<title>
		<?php
		echo esc_html(
			sprintf(
				/* translators: Payment form ID. */
				__( 'Previewing Payment Form #%d - WP Simple Pay', 'stripe' ),
				$id
			)
		);
		?>
	</title>
</head>
<body class="simpay-form-preview">

	<div class="simpay-form-preview-notice">
		<p class="simpay-form-preview-notice-section">
			<?php
			esc_html_e(
				'This is a preview of your payment form. This page is not publicly accessible. To add your payment form to a page, use the WP Simple Pay block or embed the shortcode.',
				'stripe'
			);
			?>
		</p>

		<?php if ( in_array( get_post_status( $id ), array( 'pending', 'draft' ), true ) ) : ?>
			<p class="simpay-form-preview-notice-section">
				<strong>
					<?php
					esc_html_e(
						'This payment form is currently unpublished and will not be able to accept payments until it is published.',
						'stripe'
					);
					?>
				</strong>
			</p>
		<?php endif; ?>

		<div class="simpay-form-preview-notice-actions">
			<button
				data-clipboard-text='[simpay id="<?php echo esc_attr( (string) $id ); ?>"]'
				data-copied="<?php echo esc_attr__( 'Copied!', 'stripe' ); ?>"
				class="simpay-form-preview-notice-button simpay-copy-button"
			>
				<?php esc_html_e( 'Copy Shortcode', 'stripe' ); ?>
			</button>

			<button
				data-clipboard-text='<!-- wp:simpay/payment-form {"formId":<?php echo esc_attr( (string) $id ); ?>} /-->'
				data-copied="<?php echo esc_attr__( 'Copied!', 'stripe' ); ?>"
				style="margin-left: 8px;"
				class="simpay-form-preview-notice-button simpay-copy-button"
			>
				<?php esc_html_e( 'Copy Block', 'stripe' ); ?>
			</button>

			<a href="<?php echo esc_url( $edit_form_url ); ?>" class="simpay-form-preview-notice-button-link">
				<span class="dashicons dashicons-edit"></span>
				<span>
					<?php esc_html_e( 'Continue Editing', 'stripe' ); ?>
				</span>
			</a>
		</div>
	</div>

	<div class="simpay-form-preview-wrap">
		<?php echo do_shortcode( sprintf( '[simpay id="%d"]', $id ) ); ?>
	</div>

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
