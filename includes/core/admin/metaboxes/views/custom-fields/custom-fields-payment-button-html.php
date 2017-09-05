<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do intval on counter here so we don't have to run it each time we use it below. Saves some function calls.
$counter = absint( $counter );

?>

<?php do_action( 'simpay_admin_before_custom_field_payment_button' ); ?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-payment-button-text-' . $counter; ?>"><?php esc_html_e( 'Payment Button Text', 'stripe' ); ?></label>
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
			'placeholder' => esc_attr__( 'Pay with Card', 'stripe' ),
		) );

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-processing-button-text-' . $counter; ?>"><?php esc_html_e( 'Payment Button Processing Text', 'stripe' ); ?></label>
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
			'placeholder' => esc_attr__( 'Please Wait...', 'stripe' ),
		) );

		?>
	</td>
</tr>
