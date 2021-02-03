<?php
/**
 * Simple Pay: Edit form custom fields
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds "Custom Fields" Payment Form settings tab content.
 *
 * Lite does not have true custom fields -- these are standard
 * form settings which are removed and replaced with true
 * custom fields in Pro.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_custom_fields( $post_id ) {
	$counter = 1;
	$fields  = get_post_meta( $post_id, '_custom_fields', true );
	$field   = isset( $fields['payment_button'] )
		? current( $fields['payment_button'] )
		: array();
	?>

<table>
	<tbody class="simpay-panel-section">
		<tr class="simpay-panel-field">
			<th>
				<label for="<?php echo esc_attr( 'simpay-payment-button-text-' . $counter ); ?>">
					<?php esc_html_e( 'Payment Button Text', 'stripe' ); ?>
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
					<?php esc_html_e( 'Payment Button Processing Text', 'stripe' ); ?>
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
					<?php esc_html_e( 'Payment Button Style', 'stripe' ); ?>
				</label>
			</th>
			<td>
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
	</tbody>
</table>

	<?php
	/**
	 * Allows further content after "Custom" Fields" Payment Form
	 * settings tab content.
	 *
	 * @since 3.0.0
	 */
	do_action( 'simpay_admin_after_custom_fields' );
}
add_action( 'simpay_form_settings_meta_form_display_panel', __NAMESPACE__ . '\\add_custom_fields' );
