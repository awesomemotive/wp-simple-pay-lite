<?php
/**
 * Custom Field: Text
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form\Custom_Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-payment-button-text-' . $counter ); ?>">
			<?php esc_html_e( 'Button Text', 'stripe' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
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
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-processing-button-text' . $counter ); ?>">
			<?php esc_html_e( 'Button Processing Text', 'stripe' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
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
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-payment-button-style-' . $counter ); ?>">
			<?php esc_html_e( 'Button Style', 'stripe' ); ?>
		</label>
	</th>
	<td style="border-bottom: 0;">
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[payment_button][' . $counter . '][style]',
				'id'      => esc_attr( 'simpay-payment-button-style-' . $counter ),
				'value'   => isset( $field['style'] )
					? $field['style']
					: 'stripe',
				'class'   => array( 'simpay-multi-toggle' ),
				'options' => array(
					'stripe' => esc_html__( 'Stripe blue', 'stripe' ),
					'none'   => esc_html__( 'Default', 'stripe' ),
				),
				'inline'  => 'inline',
			)
		);
		?>
	</td>
</tr>
