<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<table>
		<thead>
		<tr>
			<th colspan="2"><?php esc_html_e( 'Stripe Checkout Overlay Display', 'stripe' ); ?></th>
		</tr>
		</thead>

		<tbody class="simpay-panel-section">

		<tr class="simpay-panel-field">
			<th>
				<label for="_company_name"><?php esc_html_e( 'Company Name', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				simpay_print_field( array(
					'type'    => 'standard',
					'subtype' => 'text',
					'name'    => '_company_name',
					'id'      => '_company_name',
					'value'   => simpay_get_saved_meta( $post->ID, '_company_name', get_bloginfo( 'name' ) ),
					'class'   => array(
						'simpay-field-text',
					),
				) );
				?>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<label for="_item_description"><?php esc_html_e( 'Item Description', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				simpay_print_field( array(
					'type'    => 'standard',
					'subtype' => 'text',
					'name'    => '_item_description',
					'id'      => '_item_description',
					'value'   => simpay_get_saved_meta( $post->ID, '_item_description' ),
					'class'   => array(
						'simpay-field-text',
					),
				) );
				?>
			</td>
		</tr>

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
					<?php esc_html_e( 'Upload or select a square image of your brand or product to show on the overlay. The recommended minimum size is 128x128px.', 'stripe' ); ?>
				</p>

				<!-- Image preview -->
				<div class="simpay-image-preview-wrap <?php echo( empty( $image_url ) ? 'simpay-panel-hidden' : '' ); ?>">
					<a href="#" class="simpay-remove-image-preview simpay-remove-icon" aria-label="<?php esc_attr_e( 'Remove image', 'stripe' ); ?>" title="<?php esc_attr_e( 'Remove image', 'stripe' ); ?>"></a>
					<img src="<?php echo esc_attr( $image_url ); ?>" class="simpay-image-preview" />
				</div>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<label for="_enable_remember_me"><?php esc_html_e( 'Enable Remember Me', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				simpay_print_field( array(
					'type'  => 'checkbox',
					'name'  => '_enable_remember_me',
					'id'    => '_enable_remember_me',
					'value' => simpay_get_saved_meta( $post->ID, '_enable_remember_me', 'no' ),
				) );
				?>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<label for="_checkout_button_text"><?php esc_html_e( 'Checkout Button Text', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				simpay_print_field( array(
					'type'        => 'standard',
					'subtype'     => 'text',
					'name'        => '_checkout_button_text',
					'id'          => '_checkout_button_text',
					'value'       => simpay_get_saved_meta( $post->ID, '_checkout_button_text' ),
					'class'       => array(
						'simpay-field-text',
						'simpay-label-input',
					),
					'placeholder' => sprintf( esc_attr__( 'Pay %s', 'stripe' ), '{{amount}}' ),
					'description' => sprintf( esc_html__( "Text used for the final checkout button on the overlay (not the on-page payment button). Add %s where you'd like to show the amount. If %s is omitted, it will be appended at the end of the button text unless it is a free trial.", 'stripe' ), '{{amount}}', '{{amount}}' ),
				) );
				?>
			</td>
		</tr>

		<?php do_action( 'simpay_after_checkout_button_text' ); ?>

		<tr class="simpay-panel-field">
			<th>
				<label for="_verify_zip"><?php esc_html_e( 'Verify Zip/Postal Code', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				$verify_zip = simpay_get_saved_meta( $post->ID, '_verify_zip', 'no' );

				simpay_print_field( array(
					'type'  => 'checkbox',
					'name'  => '_verify_zip',
					'id'    => '_verify_zip',
					'value' => $verify_zip,
				) );
				?>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<label for="_enable_billing_address"><?php esc_html_e( 'Enable Billing Address', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				$enable_billing_address = simpay_get_saved_meta( $post->ID, '_enable_billing_address', 'no' );

				simpay_print_field( array(
					'type'       => 'checkbox',
					'name'       => '_enable_billing_address',
					'id'         => '_enable_billing_address',
					'value'      => $enable_billing_address,
					'class'      => array(
						'simpay-section-toggle',
					),
					'attributes' => array(
						'data-show' => '#enable-shipping-address',
					),
				) );
				?>
			</td>
		</tr>

		<tr class="simpay-panel-field <?php echo ( 'yes' !== $enable_billing_address ) ? 'simpay-panel-hidden' : ''; ?>" id="enable-shipping-address">
			<th>
				<label for="_enable_shipping_address"><?php esc_html_e( 'Enable Shipping Address', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				simpay_print_field( array(
					'type'  => 'checkbox',
					'name'  => '_enable_shipping_address',
					'id'    => '_enable_shipping_address',
					'value' => simpay_get_saved_meta( $post->ID, '_enable_shipping_address' ),
				) );
				?>
			</td>
		</tr>

		</tbody>
	</table>

<?php do_action( 'simpay_admin_after_overlay_display' );
