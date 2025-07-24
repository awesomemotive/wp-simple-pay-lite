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

use SimplePay\Core\Settings;

/**
 * Price option "Custom Amount" field.
 *
 * @since 4.11.0
 */
trait Custom_Amount {

	/**
	 * Outputs markup for the "Custom Amount Toggle" setting.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	public function unstable_price_option_custom_amount_toggle( $price, $instance_id ) {
		$price_locked_class = $price->is_defined_amount()
		? 'simpay-price-locked'
		: '';

		$license = simpay_get_license();

		$upgrade_custom_title = esc_html__(
			'Unlock Custom Amounts',
			'stripe'
		);

		$upgrade_custom_description = esc_html__(
			'We\'re sorry, custom amounts are not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
			'stripe'
		);

		$upgrade_custom_url = simpay_pro_upgrade_url(
			'form-price-option-settings',
			'Custom amount'
		);

		$upgrade_custom_purchased_url = simpay_docs_link(
			'Custom amount (already purchased)',
			'upgrading-wp-simple-pay-lite-to-pro',
			'form-price-option-settings',
			true
		);
		?>

	<tr
		class="simpay-panel-field <?php echo esc_attr( $price_locked_class ); ?>"
	>
		<td  style="border-bottom: none;padding-bottom: 10px;display: flex; align-items:center; justify-content:space-between">
			<label for="<?php echo esc_attr( $this->unstable_get_input_id( 'custom', $instance_id ) ); ?>">
				<?php if ( $license->is_lite() ) : ?>
					<input
					type="checkbox"
					name="<?php echo esc_attr( $this->unstable_get_input_name( 'custom', $instance_id ) ); ?>"
					id="simpay-custom-lite"
					data-available="no"
					data-upgrade-title="<?php echo esc_attr( $upgrade_custom_title ); ?>"
					data-upgrade-description="<?php echo esc_attr( $upgrade_custom_description ); ?>"
					data-upgrade-url="<?php echo esc_url( $upgrade_custom_url ); ?>"
					data-upgrade-purchased-url="<?php echo esc_url( $upgrade_custom_purchased_url ); ?>"
					class="simpay-price-enable-custom-amount"/>
				<?php else : ?>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->unstable_get_input_name( 'custom', $instance_id ) ); ?>"
					id="<?php echo esc_attr( $this->unstable_get_input_id( 'custom', $instance_id ) ); ?>"
					class="simpay-price-enable-custom-amount"
					<?php checked( true, null !== $price->unit_amount_min ); ?>
				/>
				<?php endif; ?>
				<?php
				esc_html_e(
					'Allow amount to be determined by user',
					'stripe'
				);
				?>
			</label>
			<?php if ( ! $license->is_lite() ) : ?>
			<button
				data-target-id="custom-amount-<?php echo esc_attr( $instance_id ); ?>"
				data-dialog-title="<?php esc_attr_e( 'Custom Amount Settings', 'stripe' ); ?>"
				class="simpay-panel-field-payment-method__configure button button-link button-small simpay-price-configure-btn"
				style="text-decoration: none;">
					<span style="margin-right: 4px;">
						<?php esc_html_e( 'Configure', 'stripe' ); ?>
					</span>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M11.828 2.25c-.916 0-1.699.663-1.85 1.567l-.091.549a.798.798 0 0 1-.517.608 7.45 7.45 0 0 0-.478.198.798.798 0 0 1-.796-.064l-.453-.324a1.875 1.875 0 0 0-2.416.2l-.243.243a1.875 1.875 0 0 0-.2 2.416l.324.453a.798.798 0 0 1 .064.796 7.448 7.448 0 0 0-.198.478.798.798 0 0 1-.608.517l-.55.092a1.875 1.875 0 0 0-1.566 1.849v.344c0 .916.663 1.699 1.567 1.85l.549.091c.281.047.508.25.608.517.06.162.127.321.198.478a.798.798 0 0 1-.064.796l-.324.453a1.875 1.875 0 0 0 .2 2.416l.243.243c.648.648 1.67.733 2.416.2l.453-.324a.798.798 0 0 1 .796-.064c.157.071.316.137.478.198.267.1.47.327.517.608l.092.55c.15.903.932 1.566 1.849 1.566h.344c.916 0 1.699-.663 1.85-1.567l.091-.549a.798.798 0 0 1 .517-.608 7.52 7.52 0 0 0 .478-.198.798.798 0 0 1 .796.064l.453.324a1.875 1.875 0 0 0 2.416-.2l.243-.243c.648-.648.733-1.67.2-2.416l-.324-.453a.798.798 0 0 1-.064-.796c.071-.157.137-.316.198-.478.1-.267.327-.47.608-.517l.55-.091a1.875 1.875 0 0 0 1.566-1.85v-.344c0-.916-.663-1.699-1.567-1.85l-.549-.091a.798.798 0 0 1-.608-.517 7.507 7.507 0 0 0-.198-.478.798.798 0 0 1 .064-.796l.324-.453a1.875 1.875 0 0 0-.2-2.416l-.243-.243a1.875 1.875 0 0 0-2.416-.2l-.453.324a.798.798 0 0 1-.796.064 7.462 7.462 0 0 0-.478-.198.798.798 0 0 1-.517-.608l-.091-.55a1.875 1.875 0 0 0-1.85-1.566h-.344zM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5z" clip-rule="evenodd"/></svg>
			</button>
			<?php endif; ?>
		</td>
	</tr>

		<?php
	}

	/**
	 * Outputs markup for the "Custom Amount" toggle.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	public function unstable_price_option_custom_amount( $price, $instance_id ) {
		$price_locked_class = $price->is_defined_amount()
			? 'simpay-price-locked'
			: '';

		$custom_amount_min_id   = $this->unstable_get_input_id( 'unit_amount_min', $instance_id );
		$custom_amount_max_id   = $this->unstable_get_input_id( 'unit_amount_max', $instance_id );
		$custom_amount_min_name = 'unit_amount_min';
		$custom_amount_max_name = 'unit_amount_max';

		$default_min = simpay_is_zero_decimal( $price->currency )
			? 100
			: 1000;

		$unit_amount_min = null !== $price->unit_amount_min
			? $price->unit_amount_min
			: $default_min;

		$unit_amount_max = null !== $price->unit_amount_max
			? $price->unit_amount_max
			: '';

		$captcha_type = simpay_get_setting( 'captcha_type', '' );
		?>

		<div class="simpay-price-custom-amount">
			<label
				for="<?php echo esc_attr( $custom_amount_min_id ); ?>"
				class="simpay-dialog-label"
			>
				<?php esc_html_e( 'Minimum Amount', 'stripe' ); ?>
			</label>

			<?php
			$this->unstable_price_option_amount_control_fixed_currency(
				$price,
				$instance_id,
				$unit_amount_min,
				$custom_amount_min_name
			);
			?>

			<p class="description">
				<?php
				esc_html_e(
					'Set a minimum amount based on the expected payment amounts you will be receiving. Allowing too low of a custom amount can lead to abuse and fraud.',
					'stripe'
				);
				?>
			</p>

			<label
				for="<?php echo esc_attr( $custom_amount_max_id ); ?>"
				class="simpay-dialog-label"
				style="margin-top: 20px;"
			>
				<?php esc_html_e( 'Maximum Amount', 'stripe' ); ?>
			</label>

			<?php

			$unit_amount_max = empty( $unit_amount_max ) ? 0 : $unit_amount_max;

			$this->unstable_price_option_amount_control_fixed_currency(
				$price,
				$instance_id,
				$unit_amount_max,
				$custom_amount_max_name,
				''
			);
			?>

			<p class="description">
				<?php
				esc_html_e(
					'Set a maximum amount limit for custom payments. Leave empty for no upper limit.',
					'stripe'
				);
				?>
			</p>

			<div class="simpay-dialog-actions">
				<div>
				</div>
				<button class="button button-primary update">
					<?php esc_html_e( 'Update', 'stripe' ); ?>
				</button>
			</div>
		</div>

		<?php
	}
}
