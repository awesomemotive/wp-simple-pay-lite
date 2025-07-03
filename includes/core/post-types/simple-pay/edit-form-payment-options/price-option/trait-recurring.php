<?php
/**
 * Simple Pay: Price fields options
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form_Payment_Options\Price_Option;

use SimplePay\Core\Utils;

/**
 * Recurring fields for price options.
 *
 * @since 4.11.0
 */
trait Recurring {

	/**
	 * Outputs markup for the "Allow conversion to subscription" setting.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	public function unstable_price_option_recurring_amount_toggle( $price, $instance_id ) {
		$name = $this->unstable_get_input_name( 'can_recur', $instance_id );
		$id   = $this->unstable_get_input_id( 'can_recur', $instance_id );

		$price_locked_class = $price->is_defined_amount()
				? 'simpay-price-locked'
				: '';

		$can_recur_name = $this->unstable_get_input_name(
			array(
				'recurring',
				'id',
			),
			$instance_id
		);

		$license = simpay_get_license();

		$upgrade_title = __(
			'Unlock Opt-in Subscription Functionality',
			'stripe'
		);

		$upgrade_description = __(
			'We\'re sorry, opt-in recurring payments through subscriptions are not available on your plan. Please upgrade to the <strong>Plus</strong> plan or higher to unlock this and other awesome features.',
			'stripe'
		);

		$upgrade_url = simpay_pro_upgrade_url(
			'form-price-option-settings',
			'Opt-in subscriptions'
		);

		$upgrade_purchased_url = simpay_docs_link(
			'Opt-in subscriptions (already purchased)',
			$license->is_lite()
			? 'upgrading-wp-simple-pay-lite-to-pro'
			: 'activate-wp-simple-pay-pro-license',
			'form-price-option-settings',
			true
		);

		// Lock the configuration button if the price is saved and one-time.
		$is_config_button_locked = (
			! $license->is_lite() && // Only lock the button if the license is not lite becase lite has only one price option.
			! $price->recurring &&
			! isset( $price->__unstable_unsaved )
		);

		// Add a lock class if price is saved and one-time.
		$price_locked_class_for_subscription = $is_config_button_locked
				? 'simpay-price-locked'
				: '';
		$license                             = simpay_get_license();
		$upgrade_can_recur_title             = esc_html__(
			'Unlock Optionally Purchasable Subscription Functionality',
			'stripe'
		);

		$upgrade_can_recur_description = esc_html__(
			'We\'re sorry, optionally purchasable subscriptions are not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
			'stripe'
		);

		$upgrade_can_recur_url = simpay_pro_upgrade_url(
			'form-price-option-settings',
			'Optionally Purchasable Subscriptions'
		);

		$upgrade_can_recur_purchased_url = simpay_docs_link(
			'Optionally Purchasable Subscriptions (already purchased)',
			'upgrading-wp-simple-pay-lite-to-pro',
			'form-price-option-settings',
			true
		);
		?>

	<tr class="simpay-panel-field">
		<td style="border-bottom: 0; padding-bottom: 10px; display: flex; align-items:center; justify-content:space-between">
			<div class="<?php echo esc_attr( $price_locked_class ); ?>">
				<label for="<?php echo esc_attr( $id ); ?>">
					<?php
					/**
					 * Do not allow `Allow price to optionally be purchased as a subscription`
					 * to be checked if the license is lite.
					 *
					 * @since 4.14.0
					 */

					?>
					<?php if ( $license->is_lite() ) : ?>
						<input
							type="checkbox"
							id="simpay-can-recur-lite"
							class="simpay-price-enable-optional-subscription"
							data-available="no"
							data-upgrade-title="<?php echo esc_attr( $upgrade_can_recur_title ); ?>"
							data-upgrade-description="<?php echo esc_attr( $upgrade_can_recur_description ); ?>"
							data-upgrade-url="<?php echo esc_url( $upgrade_can_recur_url ); ?>"
							data-upgrade-purchased-url="<?php echo esc_url( $upgrade_can_recur_purchased_url ); ?>"
						/>
					<?php else : ?>
					<input
						type="checkbox"
						name="<?php echo esc_attr( $name ); ?>"
						id="<?php echo esc_attr( $id ); ?>"
						class="simpay-price-enable-optional-subscription"
						<?php checked( true, $price->can_recur ); ?>
						data-available="<?php echo esc_attr( simpay_subscriptions_enabled() ? 'yes' : 'no' ); ?>"
					/>
					<?php endif; ?>
					<input class="simpay-false-recurring-checkbox" type="checkbox" checked="checked" disabled="disabled" style="display: none;" />
					<span class="simpay-price-can-recur-label">
					<?php
					esc_html_e(
						'Allow price to optionally be purchased as a subscription',
						'stripe'
					);
					?>
					</span>
				</label>
			</div>

			<?php if ( isset( $price->recurring['id'] ) ) : ?>
			<input
				type="hidden"
				name="<?php echo esc_attr( $can_recur_name ); ?>"
				value="<?php echo esc_attr( $price->recurring['id'] ); ?>"
			/>
			<?php endif; ?>

			<button
				data-target-id="recurring-amount-<?php echo esc_attr( $instance_id ); ?>"
				data-dialog-title="<?php esc_attr_e( 'Recurring Settings', 'stripe' ); ?>"
				class="simpay-panel-field-payment-method__configure button button-link button-small simpay-price-configure-btn <?php echo esc_attr( $price_locked_class_for_subscription ); ?>"
				style="text-decoration: none;"
			>
				<span style="margin-right: 4px;">
					<?php esc_html_e( 'Configure', 'stripe' ); ?>
				</span>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
					<path fill-rule="evenodd" d="M11.828 2.25c-.916 0-1.699.663-1.85 1.567l-.091.549a.798.798 0 0 1-.517.608 7.45 7.45 0 0 0-.478.198.798.798 0 0 1-.796-.064l-.453-.324a1.875 1.875 0 0 0-2.416.2l-.243.243a1.875 1.875 0 0 0-.2 2.416l.324.453a.798.798 0 0 1 .064.796 7.448 7.448 0 0 0-.198.478.798.798 0 0 1-.608.517l-.55.092a1.875 1.875 0 0 0-1.566 1.849v.344c0 .916.663 1.699 1.567 1.85l.549.091c.281.047.508.25.608.517.06.162.127.321.198.478a.798.798 0 0 1-.064.796l-.324.453a1.875 1.875 0 0 0 .2 2.416l.243.243c.648.648 1.67.733 2.416.2l.453-.324a.798.798 0 0 1 .796-.064c.157.071.316.137.478.198.267.1.47.327.517.608l.092.55c.15.903.932 1.566 1.849 1.566h.344c.916 0 1.699-.663 1.85-1.567l.091-.549a.798.798 0 0 1 .517-.608 7.52 7.52 0 0 0 .478-.198.798.798 0 0 1 .796.064l.453.324a1.875 1.875 0 0 0 2.416-.2l.243-.243c.648-.648.733-1.67.2-2.416l-.324-.453a.798.798 0 0 1-.064-.796c.071-.157.137-.316.198-.478.1-.267.327-.47.608-.517l.55-.091a1.875 1.875 0 0 0 1.566-1.85v-.344c0-.916-.663-1.699-1.567-1.85l-.549-.091a.798.798 0 0 1-.608-.517 7.507 7.507 0 0 0-.198-.478.798.798 0 0 1 .064-.796l.324-.453a1.875 1.875 0 0 0-.2-2.416l-.243-.243a1.875 1.875 0 0 0-2.416-.2l-.453.324a.798.798 0 0 1-.796.064 7.462 7.462 0 0 0-.478-.198.798.798 0 0 1-.517-.608l-.091-.55a1.875 1.875 0 0 0-1.85-1.566h-.344zM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5z" clip-rule="evenodd"/>
				</svg>
			</button>
		</td>
	</tr>

		<?php
	}

	/**
	 * Outputs markup for the "Recurring" settings.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 * @return void
	 */
	public function __unstable_price_option_recurring_options( $price, $instance_id ) {
		$this->recurring_amount_toggle_label( $price, $instance_id );
		$this->billing_period( $price, $instance_id );
		$this->invoice_limit( $price, $instance_id );
		$this->trial( $price, $instance_id );
		$this->setup_fee( $price, $instance_id );
		$this->plan_setup_fee( $price, $instance_id );

		$license = simpay_get_license();
		?>
		<div style="display: flex; justify-content: space-between; align-items: center;" class="simpay-dialog-actions">
				<button class="button button-secondary update">
					<?php
					esc_html_e( 'Cancel', 'stripe' );
					?>
				</button>
				<button class="button button-primary update">
					<?php
					esc_html_e( 'Update', 'stripe' );
					?>
				</button>
		</div>

		<?php
	}

	/**
	 * Outputs markup for the "Billing Period" (recurring) settings.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function billing_period( $price, $instance_id ) {
		$license = simpay_get_license();

		$upgrade_url = simpay_pro_upgrade_url(
			'form-price-option-settings',
			'Billing Period'
		);

		$is_locked = (
			$price->is_defined_amount() &&
			! isset( $price->__unstable_unsaved )
		);

		$price_locked_class = $is_locked
			? 'simpay-price-locked simpay-price-locked--dialog'
			: '';

		$interval_count_id = $this->unstable_get_input_id(
			array(
				'recurring',
				'interval_count',
			),
			$instance_id
		);

		$interval_count_name = $this->unstable_get_input_name(
			array(
				'recurring',
				'interval_count',
			),
			$instance_id
		);

		// Current value.
		$value = null !== $price->recurring
			? $price->recurring['interval']
			: 'month';

		// Recurring intervals.
		$intervals = simpay_get_recurring_intervals();

		// Use the current value to set initialize plurazation of options.
		$options = array_map(
			function ( $interval ) use ( $value ) {
				return 1 === intval( $value )
					? $interval[0]
					: $interval[1];
			},
			$intervals
		);
		?>

		<div class="simpay-dialog-section">
			<?php if ( false === $license->is_subscriptions_enabled() ) : ?>
				<div style="display: flex; justify-content: space-between; align-items: center;">
					<div style="margin-right: 15px;">
						<label class="simpay-dialog-label">
							<?php esc_html_e( 'Billing Interval', 'stripe' ); ?>
						</label>
						<?php
						esc_html_e(
							'Determine how often a subscription is automatically billed.',
							'stripe'
						);
						?>
					</div>

					<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noreferrer noopener" class="button button-primary button-small">
						<?php esc_html_e( 'Upgrade', 'stripe' ); ?>
					</a>
				</div>
			<?php else : ?>
				<strong class="simpay-dialog-label" style="display: flex; align-items: center;">
					<?php esc_html_e( 'Billing Period', 'stripe' ); ?>
				</strong>

				<div
					style="display: flex; align-items: center; margin-top: 5px;"
					class="<?php echo esc_attr( $price_locked_class ); ?>"
				>
					<span style="margin-right: 5px;">
						<?php esc_html_e( 'every', 'stripe' ); ?>
					</span>

					<label
						for="<?php echo esc_attr( $interval_count_id ); ?>"
						class="screen-reader-text"
					>
						<?php esc_html_e( 'Billing Interval Count', 'stripe' ); ?>
					</label>

					<?php
					$attributes = array(
						'min'  => 1,
						'max'  => 365,
						'step' => 1,
					);

					// If Lite, lock the interval count.
					$license = simpay_get_license();
					if ( $license->is_lite() ) {
						$is_locked = true;
					}

					if ( $is_locked ) {
						$attributes['readonly'] = $is_locked;
					}

					simpay_print_field(
						array(
							'type'       => 'standard',
							'subtype'    => 'number',
							'name'       => $interval_count_name,
							'id'         => $interval_count_id,
							'value'      => null !== $price->recurring
								? $price->recurring['interval_count']
								: 1,
							'class'      => array(
								'simpay-price-recurring-interval-count',
								'simpay-field',
								'small-text',
							),
							'attributes' => $attributes,
						)
					);
					?>

					<label
						for="<?php echo esc_attr( 'price-billing-custom-interval-count-' . $instance_id ); ?>"
						class="screen-reader-text"
					>
						<?php esc_html_e( 'Billing Interval', 'stripe' ); ?>
					</label>
					<?php
						$attributes = array(
							'data-intervals' => wp_json_encode( $intervals ),
						);

						if ( $is_locked ) {
							$attributes['readonly'] = $is_locked;
						}

						simpay_print_field(
							array(
								'type'       => 'select',
								'name'       => '_simpay_prices[' . $instance_id . '][recurring][interval]',
								'id'         => 'price-billing-custom-interval-count-' . $instance_id,
								'value'      => $value,
								'options'    => $options,
								'attributes' => $attributes,
								'class'      => array(
									'simpay-price-recurring-interval',
								),
							)
						);
					?>
					<input type="hidden" name="<?php echo esc_attr( '_simpay_prices[' . $instance_id . '][recurring_interval_current]' ); ?>" value="<?php echo esc_attr( $value ); ?>" />
				</div>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Outputs markup for the "Invoice Limit" (recurring) settings.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function invoice_limit( $price, $instance_id ) {
		$invoice_limit_id = $this->unstable_get_input_id(
			array(
				'recurring',
				'invoice_limit',
			),
			$instance_id
		);

		$invoice_limit_name = $this->unstable_get_input_name(
			array(
				'recurring',
				'invoice_limit',
			),
			$instance_id
		);

		$license = simpay_get_license();

		$upgrade_url = simpay_pro_upgrade_url(
			'form-price-option-settings',
			'Invoice limit'
		);
		?>

		<div class="simpay-panel-field--requires-upgrade simpay-dialog-section">
			<?php if ( false === $license->is_enhanced_subscriptions_enabled() ) : ?>
				<div style="display: flex; justify-content: space-between; align-items: center;">
					<div style="margin-right: 15px;">
						<label
							for="<?php echo esc_attr( $invoice_limit_id ); ?>"
							class="simpay-dialog-label"
						>
							<?php esc_html_e( 'Installment Plan', 'stripe' ); ?>
						</label>
						<?php
						esc_html_e(
							'Automatically cancel subscriptions after a specified number of invoice payments.',
							'stripe'
						);
						?>
					</div>

					<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noreferrer noopener" class="button button-primary button-small">
						<?php esc_html_e( 'Upgrade', 'stripe' ); ?>
					</a>
				</div>
			<?php else : ?>
				<label
					for="<?php echo esc_attr( $invoice_limit_id ); ?>"
					class="simpay-dialog-label"
				>
					<?php esc_html_e( 'Installment Plan', 'stripe' ); ?>
				</label>

				<?php
				simpay_print_field(
					array(
						'type'    => 'standard',
						'subtype' => 'number',
						'name'    => $invoice_limit_name,
						'id'      => $invoice_limit_id,
						'value'   => null !== $price->recurring
							&& isset( $price->recurring['invoice_limit'] )
							? $price->recurring['invoice_limit']
							: '',
						'class'   => array(
							'simpay-field',
							'small-text',
							'simpay-price-invoice-limit',
						),
					)
				);
				?>

				<p class="description" style="display: block;">
					<?php
					echo esc_html(
						__(
							'Automatically cancel subscriptions after a specified number of invoice payments. Leave blank for indefinite billing.',
							'stripe'
						) . ' '
					);
					?>
				</p>

				<p class="description" style="margin-top: 10px; display: block;">
					<span class="dashicons dashicons-editor-help"></span>

					<a
						href="<?php echo esc_url( simpay_docs_link( 'Changes do not affect existing subscriptions', 'installment-plans', 'form-price-option-settings', true ) ); ?>'#note-of-caution"
						class="simpay-external-link"
						target="_blank"
						rel="noopener noreferrer"
					>
						<?php
						esc_html_e(
							'Changes do not affect existing Subscriptions.',
							'stripe'
						);

						echo Utils\get_external_link_markup();
						?>
					</a>
				</p>

				<p class="description" style="margin-top: 10px; display: block;">
					<span class="dashicons dashicons-editor-help"></span>

					<?php
					esc_html_e(
						'Webooks are required.',
						'stripe'
					);
					?>

					<a href="#help/webhooks" class="simpay-external-link">
						<?php
						esc_html_e(
							'View the webhook documentation',
							'stripe'
						);

						echo Utils\get_external_link_markup();
						?>
					</a>
				</p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Outputs markup for the "Trial" (recurring) settings.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function trial( $price, $instance_id ) {
		$id = $this->unstable_get_input_id(
			array( 'recurring', 'trial_period_days' ),
			$instance_id
		);

		$name = $this->unstable_get_input_name(
			array( 'recurring', 'trial_period_days' ),
			$instance_id
		);

		$trial_period_days = null !== $price->recurring
			&& isset( $price->recurring['trial_period_days'] )
			? $price->recurring['trial_period_days']
			: '';

		$license = simpay_get_license();

		$upgrade_url = simpay_pro_upgrade_url(
			'form-price-option-settings',
			'Free trials'
		);
		?>

		<div class="simpay-panel-field--requires-upgrade simpay-dialog-section">
			<?php if ( false === $license->is_enhanced_subscriptions_enabled() ) : ?>
				<div style="display: flex; justify-content: space-between; align-items: center;">
					<div style="margin-right: 15px;">
						<label
							for="<?php echo esc_attr( $id ); ?>"
							class="simpay-dialog-label"
						>
							<?php esc_html_e( 'Free Trial', 'stripe' ); ?>
						</label>
						<?php
						esc_html_e(
							'Let customers trial a subscription plan for a specified period of time before being charged.',
							'stripe'
						);
						?>
					</div>

					<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noreferrer noopener" class="button button-primary button-small">
						<?php esc_html_e( 'Upgrade', 'stripe' ); ?>
					</a>
				</div>
			<?php else : ?>
				<div>
					<label
						for="<?php echo esc_attr( $id ); ?>"
						class="simpay-dialog-label"
					>
						<?php esc_html_e( 'Free Trial', 'stripe' ); ?>
					</label>

					<?php
					simpay_print_field(
						array(
							'type'    => 'standard',
							'subtype' => 'number',
							'name'    => $name,
							'id'      => $id,
							'value'   => $trial_period_days,
							'class'   => array(
								'simpay-field',
								'small-text',
								'simpay-price-free-trial',
							),
						)
					);
					?>

					<span>
						<?php echo esc_html( _x( 'days', 'trial period', 'stripe' ) ); ?>
					</span>
				</div>

				<p class="description">
					<?php esc_html_e( 'Leave empty for no trial.', 'stripe' ); ?>
				</p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Outputs markup for the "Setup Fee" (recurring) settings.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function setup_fee( $price, $instance_id ) {
		$setup_fee_id = $this->unstable_get_input_id(
			array(
				'line_items',
				'subscription-setup-fee',
				'unit_amount',
			),
			$instance_id
		);

		$setup_fee = null !== $price->line_items
			&& isset( $price->line_items[0] )
			? $price->line_items[0]['unit_amount']
			: 0;

		$license = simpay_get_license();

		$upgrade_url = simpay_pro_upgrade_url(
			'form-price-option-settings',
			'Setup fees'
		);
		?>

		<div class="simpay-panel-field--requires-upgrade simpay-dialog-section" style="border-bottom: 0;">
			<?php if ( false === $license->is_enhanced_subscriptions_enabled() ) : ?>
				<div style="display: flex; justify-content: space-between; align-items: center;">
					<div style="margin-right: 15px;">
						<label
							for="<?php echo esc_attr( $setup_fee_id ); ?>"
							class="simpay-dialog-label"
						>
							<?php esc_html_e( 'Setup Fee', 'stripe' ); ?>
						</label>
						<?php
						esc_html_e(
							'Charge an additional fee as part of the first subscription payment.',
							'stripe'
						);
						?>
					</div>

					<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noreferrer noopener" class="button button-primary button-small">
						<?php esc_html_e( 'Upgrade', 'stripe' ); ?>
					</a>
				</div>
			<?php else : ?>
				<label
					for="<?php echo esc_attr( $setup_fee_id ); ?>"
					class="simpay-dialog-label"
				>
					<?php esc_html_e( 'Setup Fee', 'stripe' ); ?>
				</label>

				<?php
				$this->unstable_price_option_amount_control_fixed_currency(
					$price,
					$instance_id,
					$setup_fee,
					array(
						'line_items',
						'subscription-setup-fee',
						'unit_amount',
					)
				);
				?>

				<p class="description">
					<?php
					esc_html_e(
						'Additional amount to add to the initial payment.',
						'stripe'
					);
					?>
				</p>
			<?php endif; ?>
		</div>

		<?php
	}

	/**
	 * Outputs markup for the "Plan Setup Fee" (recurring) settings.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function plan_setup_fee( $price, $instance_id ) {
		$setup_fee_id = $this->unstable_get_input_id(
			array(
				'line_items',
				'plan-setup-fee',
				'unit_amount',
			),
			$instance_id
		);

		$setup_fee = null !== $price->line_items
			&& isset( $price->line_items[1] )
			? $price->line_items[1]['unit_amount']
			: 0;

		if ( 0 === $setup_fee ) {
			return;
		}
		?>

		<div
			style="padding-top: 16px; margin-top: 16px; border-top: 1px solid #eee;"
		>
			<button class="button button-secondary button-small simpay-price-legacy-setting-toggle">
				Legacy settings
			</button>

			<div
				class="simpay-price-legacy-setting"
				style="display: none; margin-top: 16px;"
			>
				<label
					for="<?php echo esc_attr( $setup_fee_id ); ?>"
					class="simpay-dialog-label"
				>
					<?php esc_html_e( 'Additional Setup Fee', 'stripe' ); ?>
				</label>

				<?php
				$this->unstable_price_option_amount_control_fixed_currency(
					$price,
					$instance_id,
					$setup_fee,
					array(
						'line_items',
						'plan-setup-fee',
						'unit_amount',
					)
				);
				?>

				<p class="description">
					<?php
					esc_html_e(
						'An additional amount to add to the first payment.',
						'stripe'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}

	/**
	 * Outputs markup for the "Recurring Amount Toggle Label" (recurring) settings.
	 *
	 * @since 4.11.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string                                  $instance_id Unique instance ID.
	 */
	private function recurring_amount_toggle_label( $price, $instance_id ) {
		$license = simpay_get_license();

		if ( false === $license->is_enhanced_subscriptions_enabled() ) {
			return;
		}
		// Only show if the price can recur.
		if ( ! isset( $price->__unstable_unsaved ) && ! $price->can_recur ) {
			return;
		}

		$id = $this->unstable_get_input_id(
			'recurring_amount_toggle_label',
			$instance_id
		);

		$name = $this->unstable_get_input_name(
			'recurring_amount_toggle_label',
			$instance_id
		);

		$select_by_default_id = $this->unstable_get_input_id(
			'can_recur_selected_by_default',
			$instance_id
		);

		$select_by_default_name = $this->unstable_get_input_name(
			'can_recur_selected_by_default',
			$instance_id
		);

		$recurring_amount_toggle_label = null !== $price->recurring_amount_toggle_label
			&& isset( $price->recurring_amount_toggle_label )
			? $price->recurring_amount_toggle_label
			: esc_html__(
				'Make this a recurring amount',
				'stripe'
			);
		?>

		<div class="simpay-dialog-section simpay-price-recurring-toggle-label" style="display: none;">
			<label
				for="<?php echo esc_attr( $id ); ?>"
				class="simpay-dialog-label"
			>
				<?php esc_html_e( '"Optional Recurring Toggle" Label', 'stripe' ); ?>
			</label>

			<div>
				<?php
				simpay_print_field(
					array(
						'type'    => 'standard',
						'subtype' => 'text',
						'name'    => $name,
						'id'      => $id,
						'value'   => $recurring_amount_toggle_label,
						'class'   => array(
							'simpay-field',
							'large-text',
						),
					)
				);
				?>

			</div>

			<p class="description" style="margin-bottom: 10px;">
				<?php esc_html_e( 'Text to display when allowing users to opt-in to a recurring payment.', 'stripe' ); ?>
			</p>
			<label class="simpay-dialog-label"><?php esc_html_e( 'Recurring by Default', 'stripe' ); ?></label>
			<div>
				<?php
				simpay_print_field(
					array(
						'type'  => 'checkbox',
						'name'  => $select_by_default_name,
						'id'    => $select_by_default_id,
						'class' => array(
							'simpay-email-link-enabled',
						),
						'value' => $price->can_recur_selected_by_default ? 'yes' : 'no',
						'text'  => __(
							'Purchases are opted-in to a recurring subscription automatically.',
							'stripe'
						),
					)
				);

				?>
			</div>
			<p class="description">
				<?php esc_html_e( 'Generate recurring revenue by making this a recurring payment by default.', 'stripe' ); ?>
			</p>
		</div>

		<?php
	}
}
