<?php
/**
 * Simple Pay: Price fields options
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.11.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form_Payment_Options;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimplePay\Core\PaymentForm\PriceOption;

/**
 * Price Fields class.
 *
 * @since 4.11.0
 */
class Price_Fields {
	use Price_Option\Utils;
	use Price_Option\Label;
	use Price_Option\Amount;
	use Price_Option\Recurring;
	use Price_Option\Custom_Amount;
	use Price_Option\Quantity;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$priority = 5;
		$license  = simpay_get_license();
		if ( $license->is_lite() ) {
			$priority = 10;
		}
		add_action(
			'simpay_form_settings_meta_payment_options_panel',
			array( $this, 'unstable_price_options' ),
			$priority
		);
	}

	/**
	 * Outputs markup for the price option list.
	 *
	 * @since 4.11.0
	 *
	 * @param int $post_id Current Post ID (Payment Form ID).
	 */
	public function unstable_price_options( $post_id ) {
		$form = simpay_get_form( $post_id );

		if ( false === $form ) {
			return;
		}

		$add_price_nonce = wp_create_nonce( 'simpay_add_price_nonce' );
		$add_plan_nonce  = wp_create_nonce( 'simpay_add_plan_nonce' );

		$prices = simpay_get_payment_form_prices( $form );

		// Prefill the price options from a template or default fallback.
		//
		// Special handling vs using simpay_get_payment_form_setting() because we need
		// full access to the form to create a PriceOption instance.
		if ( empty( $prices ) ) {
			$template = __unstable_simpay_get_payment_form_template_from_url();

			// Generate from a template.
			if ( ! empty( $template ) ) {
				foreach ( $template['data']['prices'] as $price ) {
					$price = new PriceOption(
						$price,
						$form,
						wp_generate_uuid4()
					);

					$price->__unstable_unsaved = true;

					$prices[ wp_generate_uuid4() ] = $price;
				}

				// Single price option fallback.
			} else {
				$currency = strtolower(
					simpay_get_setting( 'currency', 'USD' )
				);

				$default_amount = simpay_is_zero_decimal( $currency )
				? 100
				: 1000;

				$prices = array(
					wp_generate_uuid4() => new PriceOption(
						array(
							'unit_amount' => $default_amount,
							'currency'    => $currency,
						),
						$form,
						wp_generate_uuid4()
					),
				);
			}
		}
		$license = simpay_get_license();

		$upgrade_add_price_title = esc_html__(
			'Unlock Multiple Price Options',
			'stripe'
		);

		$upgrade_add_price_description = esc_html__(
			'We\'re sorry, adding more than one price option is not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> unlock this and other awesome features.',
			'stripe'
		);

		$upgrade_add_price_url = simpay_pro_upgrade_url(
			'form-price-option-settings',
			'Add Price'
		);

		$upgrade_add_price_purchased_url = simpay_docs_link(
			'Add Price (already purchased)',
			'upgrading-wp-simple-pay-lite-to-pro',
			'form-price-option-settings',
			true
		);
		?>

	<table>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<?php esc_html_e( 'Price Options', 'stripe' ); ?>
				</th>
				<td style="border-bottom: 0;">
					<div
						style="
							display: flex;
							align-items: center;
							justify-content: space-between;
							margin: 0 0 12px;
						"
					>
					<?php if ( $license->is_lite() ) : ?>
						<button
						id="simpay-add-price-lite"
						class="button button-secondary"
						data-available="no"
						data-upgrade-title="<?php echo esc_attr( $upgrade_add_price_title ); ?>"
						data-upgrade-description="<?php echo esc_attr( $upgrade_add_price_description ); ?>"
						data-upgrade-url="<?php echo esc_url( $upgrade_add_price_url ); ?>"
						data-upgrade-purchased-url="<?php echo esc_url( $upgrade_add_price_purchased_url ); ?>"
					>
						<?php esc_html_e( 'Add Price', 'stripe' ); ?>
					</button>
					<?php else : ?>

						<button
							id="simpay-add-price"
							class="button button-secondary"
							data-nonce="<?php echo esc_attr( $add_price_nonce ); ?>"
							data-form-id="<?php echo esc_attr( $form->id ); ?>"
						>
							<?php esc_html_e( 'Add Price', 'stripe' ); ?>
						</button>

						<button
							id="simpay-prices-advanced-toggle"
							class="button button-link"
							style="
								display: flex;
								text-decoration: none;
								align-items: center;
								color: #666;
							"
						>
							<?php esc_html_e( 'Advanced', 'stripe' ); ?>
							<span
								class="dashicons dashicons-arrow-down-alt2"
								style="
									width: 14px;
									height: 14px;
									font-size: 14px;
									margin-left: 4px;
								"
							></span>
						</button>
						<?php endif; ?>
					</div>

					<div
						id="simpay-prices-advanced"
						style="display: none; margin-bottom: 12px;"
					>
						<input
							type="text"
							value=""
							style="margin-right: 5px; width: 150px;"
							placeholder="plan_123"
							id="simpay-prices-advanced-plan-id"
						/>
						<button
							id="simpay-prices-advanced-add"
							class="button button-secondary"
							data-nonce="<?php echo esc_attr( $add_plan_nonce ); ?>"
							data-form-id="<?php echo esc_attr( $post_id ); ?>"
						>
							<?php esc_html_e( 'Add existing Plan', 'stripe' ); ?>
						</button>
					</div>

					<div
						id="simpay-prices-wrap"
						class="panel simpay-metaboxes-wrapper"
					>
						<div
							id="simpay-prices"
							class="simpay-prices simpay-metaboxes ui-sortable"
						>
							<?php
							/** @var \SimplePay\Core\PaymentForm\PriceOption[] $price Price option.  */
							foreach ( $prices as $instance_id => $price ) :
								$this->unstable_price_option( $price, $instance_id, $prices );
								endforeach;
							?>
						</div>
					</div>

						<?php
						/**
						 * Allows extra output after the price option list.
						 *
						 * @since 4.4.0
						 */
						do_action( 'unstable_simpay_form_settings_pro_after_price_options' )
						?>
				</td>
			</tr>
		</tbody>
	</table>

		<?php
	}



	/**
	 * Displays a single price option.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption        $price Price option.
	 * @param string                                         $instance_id Price option instance ID.
	 *                                                                    Shared by both payment
	 *                                                                    modes.
	 * @param array<\SimplePay\Core\PaymentForm\PriceOption> $prices All price options.
	 */
	public function unstable_price_option( $price, $instance_id, $prices ) {
		$amount_type = null !== $price->recurring && false === $price->can_recur
		? 'recurring'
		: 'one-time';
		$label       = $price->get_display_label();

		$recurring_settings_display = 'recurring' === $amount_type ? 'table' : 'none';

		$one_time_settings_display = (
		'recurring' === $amount_type &&
		! isset( $price->recurring['id'] ) &&
		false === $price->can_recur
		)
			? 'none'
			: 'table';

		$has_one_price = 1 === count( $prices );

		$license = simpay_get_license();

		$unit_amount_current = simpay_format_currency(
			$price->unit_amount,
			$price->currency,
			false
		);
		?>

	<div
		id="price-<?php echo esc_attr( $instance_id ); ?>"
		class="postbox <?php echo esc_attr( $has_one_price ? '' : 'closed' ); ?> simpay-field-metabox simpay-metabox simpay-price"
		<?php if ( false === $has_one_price ) : ?>
		aria-expanded="false"
		<?php endif; ?>
	>
		<input
			type="hidden"
			name="<?php echo esc_attr( $this->unstable_get_input_name( 'id', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $price->id ); ?>"
		/>

		<input
			type="hidden"
			name="<?php echo esc_attr( $this->unstable_get_input_name( 'amount_type', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $amount_type ); ?>"
			class="simpay-price-amount-type"
		/>

		<?php if ( $license->is_lite() ) : ?>
		<input
			type="hidden"
			name="<?php echo esc_attr( $this->unstable_get_input_name( 'unsaved', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $price->__unstable_unsaved ? 'yes' : 'no' ); ?>"
		/>

		<input
			type="hidden"
			name="<?php echo esc_attr( $this->unstable_get_input_name( 'id_current', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $price->id ); ?>"
		/>

		<input
			type="hidden"
			name="<?php echo esc_attr( $this->unstable_get_input_name( 'unit_amount_current', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $unit_amount_current ); ?>"
		/>

		<input
			type="hidden"
			name="<?php echo esc_attr( $this->unstable_get_input_name( 'currency_current', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $price->currency ); ?>"
		/>

		<input
			type="hidden"
			name="<?php echo esc_attr( $this->unstable_get_input_name( 'amount_type_current', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $amount_type ); ?>"
			class="simpay-price-amount-type"
		/>
		<?php endif; ?>

		<button type="button" class="simpay-handlediv simpay-price-label-expand">
			<span class="screen-reader-text">
				<?php
				echo esc_html(
					sprintf(
						/* translators: Price option label. */
						__( 'Toggle price option: %s', 'stripe' ),
						$label
					)
				);
				?>
			</span>
			<span class="toggle-indicator" aria-hidden="true"></span>
		</button>

		<h2 class="simpay-hndle ui-sortable-handle">
			<span class="custom-field-dashicon dashicons dashicons-menu-alt2" style="cursor: move;"></span>

			<strong class="simpay-price-label-display">
					<?php echo esc_html( $label ); ?>
			</strong>

			<strong class="simpay-price-label-default" style="display: none;">
				<?php esc_html_e( 'Default Price', 'stripe' ); ?>
			</strong>
		</h2>

		<div class="simpay-field-data simpay-metabox-content inside">
			<table>
				<?php
				$this->unstable_price_option_label( $price, $instance_id );
				$this->unstable_price_option_amount( $price, $instance_id );
				?>
			</table>

			<table
				class="simpay-price-recurring-amount-toggle">
				<?php
				$this->unstable_price_option_recurring_amount_toggle( $price, $instance_id );
				?>
			</table>
			<div
				id="recurring-amount-<?php echo esc_attr( $instance_id ); ?>"
				style="display: none;"
			>
				<?php
				$this->__unstable_price_option_recurring_options( $price, $instance_id );
				?>
			</div>

			<table>
				<?php
				$this->unstable_price_option_custom_amount_toggle( $price, $instance_id );
				?>
			</table>
			<div
				id="custom-amount-<?php echo esc_attr( $instance_id ); ?>"
				style="display: none;"
			>
				<?php
				$this->unstable_price_option_custom_amount( $price, $instance_id );
				?>
			</div>

			<?php if ( ! $license->is_lite() ) : ?>
			<table class="simpay-quantity-toggle">
				<?php
				$this->unstable_quantity_toggle( $price, $instance_id );
				?>
			</table>
			<div
				id="item-quantity-<?php echo esc_attr( $instance_id ); ?>"
				style="display: none;"
			>
				<?php $this->unstable_quantity_options( $price, $instance_id ); ?>
			</div>
			<?php endif; ?>

			<div
				class="simpay-metabox-content-actions"
				style="display: flex; align-items: center;"
			>
				<?php if ( ! $license->is_lite() ) : ?>
				<button class="button-link button-link-delete simpay-price-remove" style="padding: 8px 0;">
					<?php esc_html_e( 'Remove Price', 'stripe' ); ?>
				</button>
				<div style="display: flex; align-items: center; gap: 10px;">
					<!-- Required Option -->
					<label
						class="simpay-price-required-check"
						for="<?php echo esc_attr( $this->unstable_get_input_id( 'required', $instance_id ) ); ?>"
						style="display: flex; align-items: center; padding: 8px 0; margin-left: auto"
					>
						<input
							type="checkbox"
							name="<?php echo esc_attr( $this->unstable_get_input_name( 'required', $instance_id ) ); ?>"
							id="<?php echo esc_attr( $this->unstable_get_input_id( 'required', $instance_id ) ); ?>"
							class="simpay-price-required"
							style="margin: -2px 4px 0 0;"
							<?php if ( true === $price->required ) : ?>
							checked
							<?php endif; ?>
						/>
						<span>
							<?php esc_html_e( 'Required', 'stripe' ); ?>
						</span>
					</label>

					<!-- Default Price -->
					<label
						class="simpay-price-default-check"
						for="<?php echo esc_attr( $this->unstable_get_input_id( 'default', $instance_id ) ); ?>"
						style="display: flex; align-items: center; padding: 8px 0; margin-left: auto"
					>
						<input
							type="checkbox"
							name="<?php echo esc_attr( $this->unstable_get_input_name( 'default', $instance_id ) ); ?>"
							id="<?php echo esc_attr( $this->unstable_get_input_id( 'default', $instance_id ) ); ?>"
							class="simpay-price-default"
							style="margin: -2px 4px 0 0;"
							value=""
							<?php if ( true === $price->default ) : ?>
							checked
							<?php endif; ?>
						/>
						<span>
							<?php esc_html_e( 'Default Price', 'stripe' ); ?>
						</span>
					</label>
				</div>
				<?php endif; ?>
			</div>

				<?php
				if (
				$price->is_defined_amount() &&
				$price->__unstable_stripe_object->livemode !==
					$price->form->is_livemode()
				) :
					?>
			<p style="margin: 0; padding: 9px 18px; font-size: 12px; color: #d63638;">
					<?php
					esc_html_e(
						'Price not available in the current payment mode. Please remove and add again.',
						'stripe'
					);
					?>
			</p>
				<?php endif; ?>

		</div>
	</div>

		<?php
	}



	/**
	 * Outputs markup for an "Amount" control with a fixed currency symbol.
	 *
	 * @since 4.11.0
	 * @access private
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 * @param int                                     $unit_amount Unit amount to display.
	 * @param string                                  $amount_input_name Amount input name.
	 */
	private function unstable_price_option_amount_control_fixed_currency(
		$price,
		$instance_id,
		$unit_amount,
		$amount_input_name,
		$amount_custom_placeholder = null
	) {
		$currency_position = simpay_get_currency_position();
		$is_zero_decimal   = simpay_is_zero_decimal( $price->currency );

		$currency_position_left = in_array(
			$currency_position,
			array( 'left', 'left_space' ),
			true
		);

		$currency_position_right = ! $currency_position_left;

		$amount_placeholder = null !== $amount_custom_placeholder ? $amount_custom_placeholder : simpay_format_currency(
			simpay_get_currency_minimum( $price->currency ),
			$price->currency,
			false
		);

		$amount_id   = $this->unstable_get_input_id( $amount_input_name, $instance_id );
		$amount_name = $this->unstable_get_input_name( $amount_input_name, $instance_id );

		if ( 0 === $unit_amount ) {
			$amount = '';
		} else {
			$amount = simpay_format_currency( $unit_amount, $price->currency, false );
		}

		?>

	<div class="simpay-currency-field">
		<?php if ( $currency_position_left ) : ?>
			<div
				class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-left"
				style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
			>
				<?php
				echo esc_html(
					simpay_get_currency_symbol( $price->currency )
				);
				?>
			</div>
		<?php endif; ?>

		<input
			type="text"
			name="<?php echo esc_attr( $amount_name ); ?>"
			id="<?php echo esc_attr( $amount_id ); ?>"
			class="simpay-field simpay-field-tiny simpay-field-amount simpay-price-amount"
			value="<?php echo esc_attr( $amount ); ?>"
			placeholder="<?php echo esc_attr( $amount_placeholder ); ?>"
		/>

			<?php if ( $currency_position_right ) : ?>
			<div
				class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-right"
				style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
			>
				<?php
				echo esc_html(
					simpay_get_currency_symbol( $price->currency )
				);
				?>
			</div>
		<?php endif ?>
	</div>

		<?php
	}

	/**
	 * Outputs markup for an "Amount Type" control.
	 *
	 * @since 4.11.0
	 * @access private
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function unstable_price_option_amount_type_control( $price, $instance_id ) {
		$one_time_active_class = (
			null === $price->recurring ||
			isset( $price->recurring['id'] ) ||
			true === $price->can_recur
		)
			? 'button-primary'
			: '';

		$recurring_active_class = (
			null !== $price->recurring &&
			! isset( $price->recurring['id'] ) &&
			false === $price->can_recur
		)
			? 'button-primary'
			: '';
		?>

	<fieldset>
		<legend class="screen-reader-text">
			<?php esc_html( 'Price Type', 'simple-pay' ); ?>
		</legend>

		<div class="button-group simpay-price-amount-type">
			<button
				class="button <?php echo esc_attr( $one_time_active_class ); ?>"
				aria-title="<?php esc_attr_e( 'One time', 'stripe' ); ?>"
				data-amount-type="one-time"
			>
				<?php esc_html_e( 'One time', 'stripe' ); ?>
			</button>
			<button
				class="button <?php echo esc_attr( $recurring_active_class ); ?>"
				aria-title="<?php esc_attr_e( 'Subscription', 'stripe' ); ?>"
				data-amount-type="recurring"
			>
				<?php esc_html_e( 'Subscription', 'stripe' ); ?>
			</button>
		</div>
	</fieldset>

		<?php
	}

	/**
	 * Outputs <options> markup for a list of available Stripe currencies.
	 *
	 * @since 4.11.0
	 * @access private
	 *
	 * @param false|string $selected Currently selected option.
	 */
	private function unstable_currency_select_options( $selected = false ) {
		$currencies = simpay_get_currencies();
		$options    = array();

		foreach ( $currencies as $code => $symbol ) {
			$options[] = sprintf(
				'<option value="%1$s" %4$s data-symbol="%3$s">%2$s (%3$s)</option>',
				esc_attr( strtolower( $code ) ),
				esc_html( $code ),
				esc_html( $symbol ),
				selected( $selected, strtolower( $code ), false )
			);
		}

		$options = implode( '', $options );

		echo $options;
	}
}
