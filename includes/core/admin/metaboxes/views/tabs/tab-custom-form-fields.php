<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$counter = 1;

global $post;

$fields = get_post_meta( $post->ID, '_custom_fields', true );

$field = isset( $fields['payment_button'][0] ) ? $fields['payment_button'][0] : array();

?>

	<table>
		<thead>
		<tr>
			<th colspan="2"><?php esc_html_e( 'On-Page Form Display', 'simple-pay' ); ?></th>
		</tr>
		</thead>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<label for="<?php echo 'simpay-payment-button-text-' . $counter; ?>"><?php esc_html_e( 'Payment Button Text', 'simple-pay' ); ?></label>
				</th>
				<td>
					<?php

					simpay_print_field( array(
						'type'        => 'standard',
						'subtype'     => 'text',
						'name'        => '_simpay_custom_field[payment_button][' . $counter . '][text]',
						'id'          => 'simpay-payment-button-text-' . $counter,
						'value'       => isset( $field['text'] ) ? $field['text'] : '',
						'class'       => array(
							'simpay-field-text',
							'simpay-label-input',
						),
						'attributes'  => array(
							'data-field-key' => $counter,
						),
						'placeholder' => esc_attr__( 'Pay with Card', 'simple-pay' ),
					) );

					?>
				</td>
			</tr>

			<tr class="simpay-panel-field">
				<th>
					<label for="<?php echo 'simpay-processing-button-text' . $counter; ?>"><?php esc_html_e( 'Payment Button Processing Text', 'simple-pay' ); ?></label>
				</th>
				<td>
					<?php

					simpay_print_field( array(
						'type'        => 'standard',
						'subtype'     => 'text',
						'name'        => '_simpay_custom_field[payment_button][' . $counter . '][processing_text]',
						'id'          => 'simpay-processing-button-text-' . $counter,
						'value'       => isset( $field['processing_text'] ) ? $field['processing_text'] : '',
						'class'       => array(
							'simpay-field-text',
							'simpay-label-input',
						),
						'attributes'  => array(
							'data-field-key' => $counter,
						),
						'placeholder' => esc_attr__( 'Please Wait...', 'simple-pay' ),
					) );

					?>
				</td>
			</tr>

			<tr class="simpay-panel-field">
				<th>
					<label for="<?php echo esc_attr( 'simpay-payment-button-style-' . $counter ); ?>"><?php esc_html_e( 'Payment Button Style', 'simple-pay' ); ?></label>
				</th>
				<td>
					<?php
					simpay_print_field( array(
						'type'    => 'radio',
						'name'    => '_simpay_custom_field[payment_button][' . $counter . '][style]',
						'id'      => esc_attr( 'simpay-payment-button-style-' . $counter ),
						'value'   => isset( $field['style'] ) ? $field['style' ] : ( simpay_get_global_setting( 'payment_button_style' ) ? simpay_get_global_setting( 'payment_button_style' ) : 'stripe' ),
						'class'   => array( 'simpay-multi-toggle' ),
						'options' => array(
							'stripe' => esc_html__( 'Stripe blue', 'simple-pay' ),
							'none'   => esc_html__( 'Default', 'simple-pay' ),
						),
						'inline' => 'inline',
					) );
					?>
				</td>
			</tr>
		</tbody>
	</table>

<?php do_action( 'simpay_admin_after_custom_fields' );
