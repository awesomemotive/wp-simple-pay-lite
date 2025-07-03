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
 * Price option "Label" field.
 *
 * @since 4.11.0
 */
trait Label {

	/**
	 * Outputs markup for the price option's "Label" field.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	public function unstable_price_option_label( $price, $instance_id ) {
		$id   = $this->unstable_get_input_id( 'label', $instance_id );
		$name = $this->unstable_get_input_name( 'label', $instance_id );

		$label = null !== $price->label
		? $price->label
		: '';
		?>

		<tr class="simpay-panel-field simpay-price-option-label">
			<th>
				<label for="<?php echo esc_attr( $id ); ?>">
					<?php esc_html_e( 'Label', 'stripe' ); ?>
				</label>
			</th>
			<td>
				<input
					type="text"
					name="<?php echo esc_attr( $name ); ?>"
					id="<?php echo esc_attr( $id ); ?>"
					class="simpay-field simpay-field-text simpay-price-label"
					value="<?php echo esc_attr( $label ); ?>"
				/>

				<p class="description">
					<?php
					esc_html_e(
						'Optional display label.',
						'stripe'
					);
					?>
				</p>
			</td>
		</tr>

		<?php
	}
}
