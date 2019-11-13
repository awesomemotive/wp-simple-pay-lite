<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<table>
		<thead>
		<tr>
			<th colspan="2"><?php esc_html_e( 'Stripe Checkout Display', 'stripe' ); ?></th>
		</tr>
		</thead>

		<tbody class="simpay-panel-section">

		<?php
		/**
		 * Allow extra setting rows to be added at the top of the table.
		 *
		 * @since 3.4.0
		 */
		do_action( 'simpay_admin_before_stripe_checkout_rows' );
		?>

		<tr class="simpay-panel-field">
			<th>
				<label for="_image_url"><?php esc_html_e( 'Logo/Image URL', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				$image_url = simpay_get_saved_meta( $post->ID, '_image_url' );

				simpay_print_field( array(
					'type'    => 'standard',
					'subtype' => 'text',
					'name'    => '_image_url',
					'id'      => '_image_url',
					'value'   => $image_url,
					'class'   => array(
						'simpay-field-text',
					),
					// Description set below so the add image button doesn't break to below the description
				) );
				?>
				<a class="simpay-media-uploader button"><?php esc_html_e( 'Add or Upload Image', 'stripe' ); ?></a>

				<p class="description">
					<?php esc_html_e( 'Upload or select a square image of your brand or product to show on on the Checkout page.', 'stripe' ); ?>
				</p>

				<!-- Image preview -->
				<div class="simpay-image-preview-wrap <?php echo( empty( $image_url ) ? 'simpay-panel-hidden' : '' ); ?>">
					<a href="#" class="simpay-remove-image-preview simpay-remove-icon" aria-label="<?php esc_attr_e( 'Remove image', 'stripe' ); ?>" title="<?php esc_attr_e( 'Remove image', 'stripe' ); ?>"></a>
					<img src="<?php echo esc_attr( $image_url ); ?>" class="simpay-image-preview" />
				</div>
			</td>
		</tr>

		<?php do_action( 'simpay_after_checkout_button_text' ); ?>

		<tr class="simpay-panel-field">
			<th>
				<label for="_enable_billing_address"><?php esc_html_e( 'Require Billing Address', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				$enable_billing_address = simpay_get_saved_meta( $post->ID, '_enable_billing_address', 'no' );

				simpay_print_field( array(
					'type'        => 'checkbox',
					'name'        => '_enable_billing_address',
					'id'          => '_enable_billing_address',
					'value'       => $enable_billing_address,
					'class'       => array(
						'simpay-section-toggle',
					),
					'description' => esc_html__( 'If enabled, Checkout will always collect the customer’s billing address. If not, Checkout will only collect the billing address when necessary.', 'stripe' ),
				) );
				?>
			</td>
		</tr>

		<?php
		/**
		 * Allow extra setting rows to be added at the bottom of the table.
		 *
		 * @since 3.4.0
		 */
		do_action( 'simpay_admin_after_stripe_checkout_rows' );
		?>

		</tbody>
	</table>

<?php do_action( 'simpay_admin_after_stripe_checkout' );
