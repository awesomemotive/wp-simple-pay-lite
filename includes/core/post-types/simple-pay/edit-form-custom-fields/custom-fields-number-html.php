<?php
/**
 * Custom Field: Number
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form\Custom_Fields
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
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
		<label for="<?php echo 'simpay-number-label-' . $counter; ?>">
			<?php esc_html_e( 'Label', 'stripe' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[number][' . $counter . '][label]',
				'id'          => 'simpay-number-label-' . $counter,
				'value'       => isset( $field['label'] ) ? $field['label'] : '',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => simpay_form_field_label_description(),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-number-required-' . $counter; ?>">
			<?php esc_html_e( 'Required', 'stripe' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'       => 'checkbox',
				'name'       => '_simpay_custom_field[number][' . $counter . '][required]',
				'id'         => 'simpay-number-required-' . $counter,
				'value'      => isset( $field['required'] ) ? $field['required'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);
		?>
	</td>
</tr>
