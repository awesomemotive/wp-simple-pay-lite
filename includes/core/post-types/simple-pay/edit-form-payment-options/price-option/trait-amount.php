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
 * Price option "Amount" field.
 *
 * @since 4.11.0
 */
trait Amount {
	/**
	 * Outputs markup for the "Amount" settings.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	public function unstable_price_option_amount( $price, $instance_id ) {
		$id   = $this->unstable_get_input_id( 'unit_amount', $instance_id );
		$name = $this->unstable_get_input_name( 'unit_amount', $instance_id );

		$license   = simpay_get_license();
		$is_locked = $price->is_defined_amount() && ! isset( $price->__unstable_unsaved );
		?>

		<tr class="simpay-panel-field">
			<th>
				<label for="<?php echo esc_attr( $id ); ?>">
					<?php esc_html_e( 'Amount', 'stripe' ); ?>
				</label>

				<?php if ( $is_locked && ! $license->is_lite() ) : ?>
					<span class="dashicons dashicons-lock"></span>

					<p style="font-weight: normal; margin-top: 3px;">
						<?php
						esc_html_e(
							'Defined prices cannot be modified after creation. Remove this price and create a new price to make changes.',
							'stripe'
						)
						?>
					</p>
				<?php endif; ?>
			</th>
			<td
				style="border-bottom: 0;"
				class="<?php echo esc_attr( $is_locked ? 'simpay-price-locked' : '' ); ?>"
			>
				<div style="display: flex; align-items: center;">
					<div>
						<?php
							$this->unstable_price_option_amount_control(
								$price,
								$instance_id,
								'unit_amount',
								'currency'
							);
						?>
					</div>

					<div style="margin-left: 15px;">
							<?php
							$this->unstable_price_option_amount_type_control(
								$price,
								$instance_id
							);
							?>
					</div>
				</div>
			</td>
		</tr>

		<?php
	}

		/**
		 * Outputs markup for an "Amount" control.
		 *
		 * @since 4.11.0
		 * @access private
		 *
		 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
		 * @param string                                  $instance_id Unique instance ID.
		 * @param string                                  $amount_input_name Amount input name.
		 * @param string                                  $currency_input_name Currency input name.
		 */
	private function unstable_price_option_amount_control(
		$price,
		$instance_id,
		$amount_input_name,
		$currency_input_name
	) {
		$currency_position = simpay_get_currency_position();
		$is_zero_decimal   = simpay_is_zero_decimal( $price->currency );

		$currency_position_left = in_array(
			$currency_position,
			array( 'left', 'left_space' ),
			true
		);

		$currency_position_right = ! $currency_position_left;

		$amount_placeholder = simpay_format_currency(
			simpay_get_currency_minimum( $price->currency ),
			$price->currency,
			false
		);

		$currency_id   = $this->unstable_get_input_id( $currency_input_name, $instance_id );
		$currency_name = $this->unstable_get_input_name( $currency_input_name, $instance_id );

		$amount_id   = $this->unstable_get_input_id( $amount_input_name, $instance_id );
		$amount_name = $this->unstable_get_input_name( $amount_input_name, $instance_id );

		$amount                        = simpay_format_currency( $price->unit_amount, $price->currency, false );
		$license                       = simpay_get_license();
		$is_lite                       = $license->is_lite();
		$is_currency_selection_enabled = $is_lite ? false : true;
		$currency_selection_style      = $is_currency_selection_enabled ? '' : 'display: none;';
		$currency_symbol_style         = $is_currency_selection_enabled ? 'display: none;' : '';
		?>

	<div class="simpay-currency-field">
		<?php if ( $currency_position_left ) : ?>
			<label
				for="<?php echo esc_attr( $currency_id ); ?>"
				class="screen-reader-text"
			>
				<?php esc_html_e( 'Currency', 'stripe' ); ?>
			</label>
			<div
				class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-left"
				style="<?php echo esc_attr( $currency_symbol_style ); ?> border-top-right-radius: 0; border-bottom-right-radius: 0;"
			>
				<?php echo simpay_get_saved_currency_symbol(); ?>
			</div>
			<select
				name="<?php echo esc_attr( $currency_name ); ?>"
				id="<?php echo esc_attr( $currency_id ); ?>"
				class="simpay-price-currency simpay-currency-symbol simpay-currency-symbol-left"
				style="<?php echo esc_attr( $currency_selection_style ); ?> border-top-right-radius: 0; border-bottom-right-radius: 0;"
			>
				<?php $this->unstable_currency_select_options( $price->currency ); ?>
			</select>
		<?php endif; ?>

		<input
			type="text"
			name="<?php echo esc_attr( $amount_name ); ?>"
			id="<?php echo esc_attr( $amount_id ); ?>"
			class="simpay-price-amount simpay-field simpay-field-tiny simpay-field-amount"
			value="<?php echo esc_attr( $amount ); ?>"
			placeholder="<?php echo esc_attr( $amount_placeholder ); ?>"
		/>

		<?php if ( $currency_position_right ) : ?>
			<label
				for="<?php echo esc_attr( $currency_id ); ?>"
				class="screen-reader-text"
			>
				<?php esc_html_e( 'Currency', 'stripe' ); ?>
			</label>
			<div
				class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-left"
				style="<?php echo esc_attr( $currency_symbol_style ); ?> border-top-right-radius: 0; border-bottom-right-radius: 0;"
			>
				<?php echo simpay_get_saved_currency_symbol(); ?>
			</div>
			<select
				name="<?php echo esc_attr( $currency_name ); ?>"
				id="<?php echo esc_attr( $currency_id ); ?>"
				class="simpay-price-currency simpay-currency-symbol simpay-currency-symbol-right"
				style="<?php echo esc_attr( $currency_selection_style ); ?> border-top-left-radius: 0; border-bottom-left-radius: 0;"
			>
				<?php $this->unstable_currency_select_options( $price->currency ); ?>
			</select>
		<?php endif ?>
	</div>

		<?php
	}
}
