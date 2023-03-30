<?php
/**
 * Form Builder: Purchase restrictions
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.4
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds "Purchase Restrictions" form builder tab content.
 *
 * @since 4.6.4
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_purchase_restrictions( $post_id ) {
	// License/upgrades messaging.
	$license = simpay_get_license();

	$upgrade_inventory_title = __(
		'Unlock Inventory Purchase Restrictions',
		'stripe'
	);

	$upgrade_inventory_description = __(
		'We\'re sorry, managing inventory and restricting purchases based on available stock is not available on your plan. Please upgrade to the <strong>Personal</strong> plan or higher to unlock this and other awesome features.',
		'stripe'
	);

	$upgrade_inventory_url = simpay_pro_upgrade_url(
		'form-purchase-resetrictions-settings',
		'Inventory'
	);

	$upgrade_inventory_purchased_url = simpay_docs_link(
		'Inventory (already purchased)',
		'upgrading-wp-simple-pay-lite-to-pro',
		$license->is_lite()
			? 'upgrading-wp-simple-pay-lite-to-pro'
			: 'activate-wp-simple-pay-pro-license',
		true
	);

	$upgrade_individual_inventory_title = __(
		'Unlock Individual Inventory Purchase Restrictions',
		'stripe'
	);

	$upgrade_individual_inventory_description = __(
		'We\'re sorry, managing individual inventory limits and restricting purchases based on available stock is not available on your plan. Please upgrade to the <strong>Professional</strong> plan or higher to unlock this and other awesome features.',
		'stripe'
	);

	$upgrade_individual_inventory_url = simpay_pro_upgrade_url(
		'form-purchase-resetrictions-settings',
		'Individually Inventory'
	);

	$upgrade_individual_inventory_purchased_url = simpay_docs_link(
		'Individual Inventory (already purchased)',
		'upgrading-wp-simple-pay-lite-to-pro',
		$license->is_lite()
			? 'upgrading-wp-simple-pay-lite-to-pro'
			: 'activate-wp-simple-pay-pro-license',
		true
	);

	$upgrade_schedule_title = __(
		'Unlock Scheduled Purchase Restrictions',
		'stripe'
	);

	$upgrade_schedule_description = __(
		'We\'re sorry, scheduling payment forms and restricting purchases based on a schedule is not available on your plan. Please upgrade to the <strong>Professional</strong> plan or higher to unlock this and other awesome features.',
		'stripe'
	);

	$upgrade_schedule_url = simpay_pro_upgrade_url(
		'form-purchase-resetrictions-settings',
		'Schedule'
	);

	$upgrade_schedule_purchased_url = simpay_docs_link(
		'Schedule (already purchased)',
		'upgrading-wp-simple-pay-lite-to-pro',
		$license->is_lite()
			? 'upgrading-wp-simple-pay-lite-to-pro'
			: 'activate-wp-simple-pay-pro-license',
		true
	);

	// Inventory.
	$inventory_enabled = simpay_get_payment_form_setting(
		$post_id,
		'_inventory',
		'no',
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$inventory_behavior = simpay_get_payment_form_setting(
		$post_id,
		'_inventory_behavior',
		'combined',
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$inventory_combined = simpay_get_payment_form_setting(
		$post_id,
		'_inventory_behavior_combined',
		'',
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$inventory_individual = simpay_get_payment_form_setting(
		$post_id,
		'_inventory_behavior_individual',
		array(),
		__unstable_simpay_get_payment_form_template_from_url()
	);

	// Schedule.
	$schedule_start = simpay_get_payment_form_setting(
		$post_id,
		'_schedule_start',
		'no',
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$schedule_start_gmt = simpay_get_payment_form_setting(
		$post_id,
		'_schedule_start_gmt',
		'',
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$schedule_start_date = '';
	$schedule_start_time = '';

	if ( 'yes' === $schedule_start && ! empty( $schedule_start_gmt ) ) {
		$schedule_start_date = get_date_from_gmt(
			gmdate( 'Y-m-d H:i:s', $schedule_start_gmt ),
			'Y-m-d'
		);

		$schedule_start_time = get_date_from_gmt(
			gmdate( 'Y-m-d H:i:s', $schedule_start_gmt ),
			'H:i'
		);
	}

	$schedule_end = simpay_get_payment_form_setting(
		$post_id,
		'_schedule_end',
		'no',
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$schedule_end_gmt = simpay_get_payment_form_setting(
		$post_id,
		'_schedule_end_gmt',
		'',
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$schedule_end_date = '';
	$schedule_end_time = '';

	if ( 'yes' === $schedule_end && ! empty( $schedule_end_gmt ) ) {
		$schedule_end_date = get_date_from_gmt(
			gmdate( 'Y-m-d H:i:s', $schedule_end_gmt ),
			'Y-m-d'
		);

		$schedule_end_time = get_date_from_gmt(
			gmdate( 'Y-m-d H:i:s', $schedule_end_gmt ),
			'H:i:s'
		);
	}
	?>

	<table
		<?php if ( true === $license->is_lite() ) : ?>
		class="simpay-form-builder-purchase-restrictions"
		<?php elseif ( false === $license->is_lite() ) : ?>
			class="simpay-form-builder-purchase-restrictions simpay-show-if"
			data-if="_form_type"
			data-is="on-site"
		<?php endif; ?>
	>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<?php esc_html_e( 'Inventory', 'stripe' ); ?>
				</th>
				<td>
					<div>
						<label for="_inventory">
							<input
								type="checkbox"
								id="_inventory"
								name="_inventory"
								value="yes"
								<?php checked( 'yes', $inventory_enabled ); ?>
								data-available="<?php echo $license->is_pro( 'personal', '>=' ) ? 'yes' : 'no'; ?>"
								data-upgrade-title="<?php echo esc_attr( $upgrade_inventory_title ); ?>"
								data-upgrade-description="<?php echo esc_attr( $upgrade_inventory_description ); ?>"
								data-upgrade-url="<?php echo esc_attr( $upgrade_inventory_url ); ?>"
								data-upgrade-purchased-url="<?php echo esc_attr( $upgrade_inventory_purchased_url ); ?>"
							/>

							<?php
							esc_html_e(
								'Hide the payment form after a set number of payments',
								'stripe'
							);
							?>
						</label>
					</div>

					<fieldset
						class="simpay-form-builder-inset-settings simpay-show-if"
						data-if="_inventory"
						data-is="yes"
					>
						<legend class="screen-reader-text">
							<?php
							esc_html_e(
								'How should payment be counted?',
								'stripe'
							);
							?>
						</legend>

						<div>
							<label for="_inventory_behavior_combined">
								<input
									type="radio"
									value="combined"
									name="_inventory_behavior"
									id="_inventory_behavior_combined"
									<?php if ( 'combined' === $inventory_behavior || 'no' === $inventory_enabled ) : ?>
										checked
									<?php endif; ?>
								/>

								<?php
								esc_html_e(
									'All payments count towards a single limit',
									'stripe'
								);
								?>
							</label>

							<div
								class="simpay-form-builder-inset-settings simpay-form-builder-purchase-restrictions__restriction-item simpay-show-if"
								data-if="_inventory_behavior"
								data-is="combined"
							>
								<div class="simpay-form-builder-inventory-control">
									<input
										type="number"
										value="<?php echo ! empty( $inventory_combined ) ? esc_attr( $inventory_combined['available'] ) : ''; ?>"
										placeholder="100"
										min="0"
										step="1"
										name="_inventory_behavior_combined"
										id="_inventory_behavior_combined"
									/>

									<?php if ( ! empty( $inventory_combined ) ) : ?>
									<span class="simpay-form-builder-inventory-control__initial">
										<?php echo esc_html( $inventory_combined['initial'] ); ?>
									</span>
									<?php endif; ?>
								</div>

								<label class="screen-reader-text" for="_inventory_behavior_combined">
									<?php
									esc_html_e(
										'Total number of payments',
										'stripe'
									);
									?>
								</label>
							</div>
						</div>

						<div>
							<label for="_inventory_behavior_individual">
								<input
									type="radio"
									value="individual"
									name="_inventory_behavior"
									id="_inventory_behavior_individual"
									<?php checked( 'individual', $inventory_behavior ); ?>
									data-available="<?php echo $license->is_pro( 'professional', '>=' ) ? 'yes' : 'no'; ?>"
									data-upgrade-title="<?php echo esc_attr( $upgrade_individual_inventory_title ); ?>"
									data-upgrade-description="<?php echo esc_attr( $upgrade_individual_inventory_description ); ?>"
									data-upgrade-url="<?php echo esc_attr( $upgrade_individual_inventory_url ); ?>"
									data-upgrade-purchased-url="<?php echo esc_attr( $upgrade_individual_inventory_purchased_url ); ?>"
									data-prev-value="combined"
								/>

								<?php
								esc_html_e(
									'Each price option has its own limit',
									'stripe'
								);
								?>
							</label>

							<div id="simpay-form-builder-inventory-individual">
								<?php
								if ( ! empty( $inventory_individual ) ) :
									$form   = simpay_get_form( $post_id );
									$prices = simpay_get_payment_form_prices( $form );

									foreach ( $inventory_individual as $instance_id => $inventory ) :
										?>

								<div
									id="inventory-price-<?php echo esc_attr( $instance_id ); ?>"
									class="simpay-form-builder-inset-settings simpay-form-builder-purchase-restrictions__restriction-item simpay-show-if"
									data-if="_inventory_behavior"
									data-is="individual">
										<div class="simpay-form-builder-inventory-control">
											<input
												type="number"
												min="0"
												step="1"
												placeholder="100"
												value="<?php echo esc_attr( $inventory['available'] ); ?>"
												name="_inventory_behavior_individual[<?php echo esc_attr( $instance_id ); ?>]"
												id="_inventory_behavior_individual-price-<?php echo esc_attr( $instance_id ); ?>"
											/>

											<span class="simpay-form-builder-inventory-control__initial">
												<?php echo esc_html( $inventory['initial'] ); ?>
											</span>
										</div>

										<label for="_inventory_behavior_individual-price-<?php echo esc_attr( $instance_id ); ?>">
											<?php
											echo esc_html(
												$prices[ $instance_id ]->get_display_label()
											);
											?>
										</label>
									</div>

										<?php
									endforeach;
								endif;
								?>
							</div>
						</div>
					</fieldset>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<th>
					<?php esc_html_e( 'Schedule', 'stripe' ); ?>
				</th>
				<td style="border-bottom: 0;">
					<div>
						<label for="_schedule_start">
							<input
								type="checkbox"
								id="_schedule_start"
								name="_schedule_start"
								<?php checked( 'yes' === $schedule_start ); ?>
								data-available="<?php echo $license->is_pro( 'professional', '>=' ) ? 'yes' : 'no'; ?>"
								data-upgrade-title="<?php echo esc_attr( $upgrade_schedule_title ); ?>"
								data-upgrade-description="<?php echo esc_attr( $upgrade_schedule_description ); ?>"
								data-upgrade-url="<?php echo esc_attr( $upgrade_schedule_url ); ?>"
								data-upgrade-purchased-url="<?php echo esc_attr( $upgrade_schedule_purchased_url ); ?>"
							/>

							<?php
							esc_html_e(
								'Show the payment form after a specific date and time',
								'stripe'
							);
							?>
						</label>
					</div>

					<div
						class="simpay-form-builder-inset-settings simpay-form-builder-purchase-restrictions__restriction-item simpay-show-if"
						data-if="_schedule_start"
						data-is="yes"
					>
						<div class="simpay-form-builder-purchase-restrictions__restriction-item-datetime">
							<label for="_schedule_start_date">
								<span class="screen-reader-text">
								<?php
								esc_html_e(
									'Start Date',
									'stripe'
								);
								?>
								</span>

								<input
									type="date"
									name="_schedule_start_date"
									value="<?php echo esc_attr( $schedule_start_date ); ?>"
								/>
							</label>

							<label for="_schedule_start_time">
								<span class="screen-reader-text">
								<?php
								esc_html_e(
									'Start Time',
									'stripe'
								);
								?>
								</span>

								<input
									type="time"
									name="_schedule_start_time"
									value="<?php echo esc_attr( $schedule_start_time ); ?>"
								/>
							</label>

							<span>
								<?php echo esc_html( simpay_wp_timezone_string() ); ?>
							</span>
						</div>
					</div>

					<div style="margin-top: 12px;">
						<label for="_schedule_end">
							<input
								type="checkbox"
								id="_schedule_end"
								name="_schedule_end"
								<?php checked( 'yes' === $schedule_end ); ?>
								data-available="<?php echo $license->is_pro( 'professional', '>=' ) ? 'yes' : 'no'; ?>"
								data-upgrade-title="<?php echo esc_attr( $upgrade_schedule_title ); ?>"
								data-upgrade-description="<?php echo esc_attr( $upgrade_schedule_description ); ?>"
								data-upgrade-url="<?php echo esc_attr( $upgrade_schedule_url ); ?>"
								data-upgrade-purchased-url="<?php echo esc_attr( $upgrade_schedule_purchased_url ); ?>"
							/>

							<?php
							esc_html_e(
								'Hide the payment form after a specific date and time',
								'stripe'
							);
							?>
						</label>
					</div>

					<div
						class="simpay-form-builder-inset-settings simpay-form-builder-purchase-restrictions__restriction-item simpay-show-if"
						data-if="_schedule_end"
						data-is="yes"
					>
						<div class="simpay-form-builder-purchase-restrictions__restriction-item-datetime">
							<label for="_schedule_end_date">
								<span class="screen-reader-text">
								<?php
								esc_html_e(
									'End Date',
									'stripe'
								);
								?>
								</span>

								<input
									type="date"
									name="_schedule_end_date"
									value="<?php echo esc_attr( $schedule_end_date ); ?>"
								/>
							</label>

							<label for="_schedule_end_time">
								<span class="screen-reader-text">
								<?php
								esc_html_e(
									'End Time',
									'stripe'
								);
								?>
								</span>

								<input
									type="time"
									name="_schedule_end_time"
									value="<?php echo esc_attr( $schedule_end_time ); ?>"
								/>
							</label>

							<span>
								<?php echo esc_html( simpay_wp_timezone_string() ); ?>
							</span>
						</div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<table
		<?php if ( true === $license->is_lite() ) : ?>
			style="display: none;"
		<?php elseif ( false === $license->is_lite() ) : ?>
			class="simpay-form-builder-purchase-restrictions simpay-show-if"
			data-if="_form_type"
			data-is="off-site"
		<?php endif; ?>
	>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<td>
					<div class="notice notice-warning inline" style="margin-top: 18px;"><p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
							__(
								'Sorry, purchase restrictions are only available for on-site payment forms. %1$sUpdate the form type%2$s to "On-site" to enable purchase restrictions.',
								'stripe'
							),
							'<a href="#form-display-options-settings-panel" data-show-tab="simpay-form_display_options" class="simpay-tab-link">',
							'</a>'
						)
					);
					?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_purchase_restrictions_panel',
	__NAMESPACE__ . '\\add_purchase_restrictions'
);
