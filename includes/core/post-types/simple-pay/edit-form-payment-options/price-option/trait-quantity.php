<?php
/**
 * Simple Pay: Price fields options
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.11.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form_Payment_Options\Price_Option;

/**
 * Price option "Quantity" field.
 *
 * @since 4.11.0
 */
trait Quantity {

	/**
	 * Outputs markup for the "Allow quantity to be determined by user" setting.
	 *
	 * @since 4.11.0
	 * @access private
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function unstable_quantity_toggle( $price, $instance_id ) {
		$name = $this->unstable_get_input_name( 'quantity_toggle', $instance_id );
		$id   = $this->unstable_get_input_id( 'quantity_toggle', $instance_id );
		?>

	<tr
		class="simpay-panel-field simpay-show-if"
		data-if="_form_type"
		data-is="on-site"
	>
		<td style="display: flex; align-items:center; justify-content:space-between">
			<label for="<?php echo esc_attr( $id ); ?>">
				<input
					type="checkbox"
					name="<?php echo esc_attr( $name ); ?>"
					id="<?php echo esc_attr( $id ); ?>"
					value="yes"
					<?php checked( 'yes', $price->quantity_toggle ); ?>
					class="simpay-price-quantity"
				/>
				<?php
				esc_html_e(
					'Allow quantity to be determined by user',
					'stripe'
				);
				?>
			</label>

			<button
				data-target-id="item-quantity-<?php echo esc_attr( $instance_id ); ?>"
				data-dialog-title="<?php esc_attr_e( 'Quantity Settings', 'stripe' ); ?>"
				class="simpay-panel-field-payment-method__configure button button-link button-small simpay-price-configure-btn"
				style="text-decoration: none;">
					<span style="margin-right: 4px;">
						<?php esc_html_e( 'Configure', 'stripe' ); ?>
					</span>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M11.828 2.25c-.916 0-1.699.663-1.85 1.567l-.091.549a.798.798 0 0 1-.517.608 7.45 7.45 0 0 0-.478.198.798.798 0 0 1-.796-.064l-.453-.324a1.875 1.875 0 0 0-2.416.2l-.243.243a1.875 1.875 0 0 0-.2 2.416l.324.453a.798.798 0 0 1 .064.796 7.448 7.448 0 0 0-.198.478.798.798 0 0 1-.608.517l-.55.092a1.875 1.875 0 0 0-1.566 1.849v.344c0 .916.663 1.699 1.567 1.85l.549.091c.281.047.508.25.608.517.06.162.127.321.198.478a.798.798 0 0 1-.064.796l-.324.453a1.875 1.875 0 0 0 .2 2.416l.243.243c.648.648 1.67.733 2.416.2l.453-.324a.798.798 0 0 1 .796-.064c.157.071.316.137.478.198.267.1.47.327.517.608l.092.55c.15.903.932 1.566 1.849 1.566h.344c.916 0 1.699-.663 1.85-1.567l.091-.549a.798.798 0 0 1 .517-.608 7.52 7.52 0 0 0 .478-.198.798.798 0 0 1 .796.064l.453.324a1.875 1.875 0 0 0 2.416-.2l.243-.243c.648-.648.733-1.67.2-2.416l-.324-.453a.798.798 0 0 1-.064-.796c.071-.157.137-.316.198-.478.1-.267.327-.47.608-.517l.55-.091a1.875 1.875 0 0 0 1.566-1.85v-.344c0-.916-.663-1.699-1.567-1.85l-.549-.091a.798.798 0 0 1-.608-.517 7.507 7.507 0 0 0-.198-.478.798.798 0 0 1 .064-.796l.324-.453a1.875 1.875 0 0 0-.2-2.416l-.243-.243a1.875 1.875 0 0 0-2.416-.2l-.453.324a.798.798 0 0 1-.796.064 7.462 7.462 0 0 0-.478-.198.798.798 0 0 1-.517-.608l-.091-.55a1.875 1.875 0 0 0-1.85-1.566h-.344zM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5z" clip-rule="evenodd"/></svg>
			</button>
		</td>
	</tr>

		<?php
	}

	/**
	 * Outputs markup for the "Quantity fields options" setting.
	 *
	 * @since 4.11.0
	 * @access private
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function unstable_quantity_options( $price, $instance_id ) {
		$quantity_label_name = $this->unstable_get_input_name( 'quantity_label', $instance_id );
		$quantity_label_id   = $this->unstable_get_input_id( 'quantity_label', $instance_id );
		$quantity_max_name   = $this->unstable_get_input_name( 'quantity_maximum', $instance_id );
		$quantity_max_id     = $this->unstable_get_input_id( 'quantity_maximum', $instance_id );
		$quantity_min_name   = $this->unstable_get_input_name( 'quantity_minimum', $instance_id );
		$quantity_min_id     = $this->unstable_get_input_id( 'quantity_minimum', $instance_id );

		?>

		<div class="simpay-dialog-section">
			<label
				for="<?php echo 'simpay-number-label-' . $instance_id; ?>"
				class="simpay-dialog-label"
			>
				<?php esc_html_e( 'Label', 'stripe' ); ?>
			</label>

			<?php
			simpay_print_field(
				array(
					'type'        => 'standard',
					'subtype'     => 'text',
					'name'        => $quantity_label_name,
					'id'          => $quantity_label_id,
					'value'       => isset( $price->quantity_label ) ? $price->quantity_label : '',
					'class'       => array(
						'simpay-field-text',
						'simpay-label-input',
					),
					'attributes'  => array(
						'data-field-key' => $instance_id,
					),
					'description' => simpay_form_field_label_description(),
				)
			);
			?>
		</div>

		<div class="simpay-dialog-section">
			<label
				for="<?php echo 'simpay-number-minimum-' . $instance_id; ?>"
				class="simpay-dialog-label"
			>
				<?php esc_html_e( 'Minimum', 'stripe' ); ?>
			</label>

			<?php
			simpay_print_field(
				array(
					'type'       => 'standard',
					'subtype'    => 'number',
					'name'       => $quantity_min_name,
					'id'         => $quantity_min_id,
					'value'      => isset( $price->quantity_minimum ) ? $price->quantity_minimum : '',
					'class'      => array(
						'small-text',
					),
					'attributes' => array(
						'data-field-key' => $instance_id,
					),
				)
			);
			?>
		</div>

		<div>
			<label
				for="<?php echo 'simpay-number-maximum-' . $instance_id; ?>"
				class="simpay-dialog-label"
			>
				<?php esc_html_e( 'Maximum', 'stripe' ); ?>
			</label>

			<?php
			simpay_print_field(
				array(
					'type'       => 'standard',
					'subtype'    => 'number',
					'name'       => $quantity_max_name,
					'id'         => $quantity_max_id,
					'value'      => isset( $price->quantity_maximum ) ? $price->quantity_maximum : '',
					'class'      => array(
						'small-text',
					),
					'attributes' => array(
						'data-field-key' => $instance_id,
					),
				)
			);
			?>
		</div>

		<div class="simpay-dialog-actions">
			<div>
			</div>
			<button class="button button-primary update">
				<?php esc_html_e( 'Update', 'stripe' ); ?>
			</button>
		</div>

		<?php
	}
}
