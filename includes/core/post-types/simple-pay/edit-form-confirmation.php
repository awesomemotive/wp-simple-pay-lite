<?php
/**
 * Form Builder: Confirmation
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.9
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

use Sandhills\Utils\Persistent_Dismissible;
use SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs markup for the "Payment Success Page" setting.
 *
 * @since 4.7.9
 * @access private
 *
 * @param int $post_id Current post ID (Payment Form ID).
 */
function _add_payment_success_page( $post_id ) {
	?>

	<table>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<label for="_success_redirect_type">
						<?php esc_html_e( 'Payment Success Page', 'stripe' ); ?>
					</label>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$success_redirect_type = simpay_get_payment_form_setting(
						$post_id,
						'_success_redirect_type',
						'dedicated',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					// Lagecy `payment_page_self_confirmation` setting.
					// If previously saved then set to "dedicated" to maintain previous behavior.
					$payment_page_self_confirmation = get_post_meta( $post_id, '_payment_page_self_confirmation', true );
					$enable_payment_page            = simpay_get_payment_form_setting(
						$post_id,
						'_enable_payment_page',
						'no',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					// Since self-confirmation is related to payment pages, we need to make sure it's enabled.
					if ( 'yes' === $payment_page_self_confirmation && 'yes' === $enable_payment_page ) {
						$success_redirect_type = 'dedicated'; // Set to dedicated to maintain previous behavior.
					}

					simpay_print_field(
						array(
							'type'    => 'radio',
							'name'    => '_success_redirect_type',
							'id'      => '_success_redirect_type',
							'class'   => array( 'simpay-multi-toggle' ),
							'options' => array(
								'dedicated' => __( 'Dedicated Confirmation Page', 'stripe' ),
								'default'   => __( 'Global Setting', 'stripe' ),
								'page'      => __( 'Custom Page', 'stripe' ),
								'redirect'  => __( 'External URL', 'stripe' ),
							),
							'inline'  => 'inline',
							'default' => 'dedicated',
							'value'   => $success_redirect_type,
						)
					);
					?>

					<div class="simpay-show-if" data-if="_success_redirect_type" data-is="default">
						<p class="description">
							<?php
							$settings_url = Settings\get_url(
								array(
									'section'    => 'payment-confirmations',
									'subsection' => 'pages',
									'setting'    => 'success_page',
								)
							);

							echo wp_kses(
								sprintf(
									/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
									__(
										'Redirect successful payments to the page specified in your %1$sglobal confirmation settings%2$s.',
										'stripe'
									),
									sprintf(
										'<a href="%s" target="_blank">',
										esc_url( $settings_url )
									),
									'</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
									),
								)
							);
							?>
						</p>
					</div>
					<div class="simpay-show-if" data-if="_success_redirect_type" data-is="dedicated">
						<p class="description">
							<?php
							$settings_url = Settings\get_url(
								array(
									'section'    => 'payment-confirmations',
									'subsection' => 'pages',
									'setting'    => 'success_page',
								)
							);

							echo __(
								'Use a distraction-free payment confirmation page without adding a new page in WordPress.',
								'stripe'
							);
							?>
						</p>
					</div>

					<div class="simpay-show-if" data-if="_success_redirect_type" data-is="page" style="margin-top: 12px;">
						<?php
						$success_redirect_page = simpay_get_payment_form_setting(
							$post_id,
							'_success_redirect_page',
							'',
							__unstable_simpay_get_payment_form_template_from_url()
						);

						simpay_print_field(
							array(
								'type'        => 'select',
								'page_select' => 'page_select',
								'name'        => '_success_redirect_page',
								'id'          => '_success_redirect_page',
								'value'       => $success_redirect_page,
								'description' => __(
									'Choose a page from your site to redirect to after a successful transaction.',
									'stripe'
								),
							)
						);
						?>
					</div>

					<div class="simpay-show-if" data-if="_success_redirect_type" data-is="redirect" style="margin-top: 12px;">
						<?php
						$success_redirect_url = simpay_get_payment_form_setting(
							$post_id,
							'_success_redirect_url',
							'',
							__unstable_simpay_get_payment_form_template_from_url()
						);

						simpay_print_field(
							array(
								'type'        => 'standard',
								'subtype'     => 'text',
								'name'        => '_success_redirect_url',
								'id'          => '_success_redirect_url',
								'class'       => array(
									'simpay-field-text',
								),
								'placeholder' => 'https://',
								'value'       => $success_redirect_url,
								'description' => __(
									'Enter a custom redirect URL for successful transactions.',
									'stripe'
								),
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
	'simpay_form_settings_confirmation_panel',
	__NAMESPACE__ . '\\_add_payment_success_page',
	10
);

/**
 * Outputs markup for the "Payment Confirmation Message" setting.
 *
 * @since 4.7.9
 * @access private
 *
 * @param int $post_id Current post ID (Payment Form ID).
 */
function _add_payment_success_message( $post_id ) {
	$settings_url = Settings\get_url(
		array(
			'section'    => 'payment-confirmations',
			'subsection' => 'pages',
		)
	);

	$payment_page_settings_url = '#payment-page-settings-panel';
	?>

	<div class="simpay-show-if" data-if="_success_redirect_type" data-is="default">
	<?php
	// Dismissed temporary notice.
	$dismissed_notice = (bool) Persistent_Dismissible::get(
		array(
			'id' => 'simpay-form-settings-confirmation-education',
		)
	);

	if ( true !== $dismissed_notice ) {
		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-payment-form-confirmation-settings.php'; // @phpstan-ignore-line
	}
	?>
	</div>

	<table class="simpay-show-if" data-if="_success_redirect_type" data-is="default dedicated">
		<tbody class="simpay-panel-section">
		<tr id="_confirmation_page_use_payment_page_config_wrapper" data-if="_success_redirect_type" data-is="dedicated" class="simpay-panel-field simpay-show-if">
				<th>
				<strong>
						<?php esc_html_e( 'Appearance', 'stripe' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$confirmation_page_use_payment_page_config = simpay_get_payment_form_setting(
						$post_id,
						'_confirmation_page_use_payment_page_config',
						'yes',
						__unstable_simpay_get_payment_form_template_from_url()
					);
					?>
					<label for="_confirmation_page_use_payment_page_config" class="simpay-field-bool">
						<input
							name="_confirmation_page_use_payment_page_config"
							type="checkbox"
							id="_confirmation_page_use_payment_page_config"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
							value="yes"
							<?php checked( true, 'yes' === $confirmation_page_use_payment_page_config ); ?>
						/>
						<?php
						echo wp_kses(
							sprintf(
								/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__(
									'Inherit the %1$sPayment Page\'s%2$s style.',
									'stripe'
								),
								sprintf(
									'<a href="%s" data-show-tab="simpay-payment_page" class="simpay-tab-link">',
									esc_url( $payment_page_settings_url )
								),
								'</a>'
							),
							array(
								'a' => array(
									'href'          => true,
									'class'         => true,
									'data-show-tab' => true,
								),
							)
						);
						?>
					</label>
					<p class="description"><?php esc_html_e( 'Use the same distraction-free styles configured on the Payment Page', 'stripe' ); ?></p>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<th>
					<label for="_success_redirect_type">
						<?php esc_html_e( 'Payment Success Message', 'stripe' ); ?>
					</label>
				</th>
				<td>
					<?php
					$message = get_post_meta( $post_id, '_success_message', true );

					wp_editor(
						$message,
						'_success_message',
						array(
							'textarea_name' => '_success_message',
							'textarea_rows' => 10,
						)
					);
					?>

					<p class="description">
						<?php
						echo wp_kses(
							sprintf(
								/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__(
									'Enter a custom message to display after a sucessful payment is made with this payment form. Leave blank to use your %1$sglobal confirmation settings%2$s.',
									'stripe'
								),
								sprintf(
									'<a href="%s" target="_blank">',
									esc_url( $settings_url )
								),
								'</a>'
							),
							array(
								'a' => array(
									'href'   => true,
									'target' => true,
								),
							)
						);
						?>
					</p>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_confirmation_panel',
	__NAMESPACE__ . '\\_add_payment_success_message',
	20
);

/**
 * Outputs markup for the "Dedicated Confirmation Page" setting.
 *
 * @since 4.12.0
 * @access private
 *
 * @param int $post_id Current post ID (Payment Form ID).
 */
function _add_dedicated_confirmation_page_customization_options( $post_id ) {
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-color-picker' );
	$confirmation_page_use_payment_page_config = simpay_get_payment_form_setting(
		$post_id,
		'_confirmation_page_use_payment_page_config',
		'yes',
		__unstable_simpay_get_payment_form_template_from_url()
	);
	$visiblity_class                           = 'yes' === $confirmation_page_use_payment_page_config ? 'simpay-panel-hidden' : '';
	?>

	<div id="simpay-dedicated-confimration-page-customization-options" class="<?php echo esc_attr( $visiblity_class ); ?>" data-if="_success_redirect_type" data-is="dedicated">
	<table >
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Color Scheme', 'stripe' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$background_color = simpay_get_payment_form_setting(
						$post_id,
						'_confirmation_page_background_color',
						'#428bca',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					$colors = array(
						'#428bca' => __( 'Blue', 'stripe' ),
						'#1aa59f' => __( 'Teal', 'stripe' ),
						'#5ab552' => __( 'Green', 'stripe' ),
						'#d34342' => __( 'Red', 'stripe' ),
						'#9376b5' => __( 'Purple', 'stripe' ),
						'#999999' => __( 'Gray', 'stripe' ),
					);
					?>

					<div class="payment-page-background-color-selector">
						<?php foreach ( $colors as $hex => $name ) : ?>
							<div class="simpay-payment-page-background-color <?php echo $background_color === $hex ? 'is-selected' : ''; ?>" >
								<input
									type="radio"
									name="_confirmation_page_background_color"
									id="confirmation-page-background-color-<?php echo esc_attr( $hex ); ?>"
									value="<?php echo esc_attr( $hex ); ?>"
									<?php checked( true, $background_color === $hex ); ?>
								/>
								<label for="confirmation-page-background-color-<?php echo esc_attr( $hex ); ?>" style="background-color: <?php echo $hex; ?>; border-color: <?php echo $hex; ?>">
									<span class="screen-reader-text">
										<?php echo esc_html( $name ); ?>
									</span>
								</label>
							</div>
						<?php endforeach; ?>

						<?php
						$custom_color = array_key_exists( $background_color, $colors )
							? '#cacaca'
							: $background_color;
						?>

						<div class="simpay-payment-page-background-color <?php echo ! array_key_exists( $background_color, $colors ) ? 'is-selected' : ''; ?>" >
							<input
								type="radio"
								name="_confirmation_page_background_color"
								id="confirmation-page-background-color-custom"
								<?php checked( false, array_key_exists( $background_color, $colors ) ); ?>
								value="<?php echo esc_attr( $custom_color ); ?>"
							/>
						</div>
					</div>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Form Title & Description', 'stripe' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$confirmation_page_title_description = simpay_get_payment_form_setting(
						$post_id,
						'_confirmation_page_title_description',
						'yes',
						__unstable_simpay_get_payment_form_template_from_url()
					);
					?>

					<label for="_confirmation_page_title_description" class="simpay-field-bool">
						<input
							name="_confirmation_page_title_description"
							type="checkbox"
							id="_confirmation_page_title_description"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
							value="yes"
							<?php checked( true, 'yes' === $confirmation_page_title_description ); ?>
						/><?php esc_html_e( 'Display the payment form\'s title and description', 'stripe' ); ?>
					</label>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Header Image / Logo', 'stripe' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$confirmation_page_image_url = simpay_get_payment_form_setting(
						$post_id,
						'_confirmation_page_image_url',
						'',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					simpay_print_field(
						array(
							'type'    => 'standard',
							'subtype' => 'hidden',
							'name'    => '_confirmation_page_image_url',
							'id'      => '_confirmation_page_image_url',
							'value'   => $confirmation_page_image_url,
							'class'   => array(
								'simpay-field-text',
								'simpay-field-image-url',
							),
						)
					);
					?>

					<div style="display: flex; align-items: center;">
						<button type="button" class="simpay-media-uploader button button-secondary" style="margin-top: 4px;"><?php esc_html_e( 'Choose Image', 'stripe' ); ?></button>

						<button class="simpay-remove-image-preview button button-secondary button-danger button-link" style="margin-left: 8px; display: <?php echo ! empty( $confirmation_page_image_url ) ? 'block' : 'none'; ?>">
							<?php esc_attr_e( 'Remove', 'stripe' ); ?>
						</button>
					</div>

					<div class="simpay-image-preview-wrap <?php echo( empty( $confirmation_page_image_url ) ? 'simpay-panel-hidden' : '' ); ?>">
						<img src="<?php echo esc_attr( $confirmation_page_image_url ); ?>" class="simpay-image-preview" />
					</div>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Footer Text', 'stripe' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0;">
					<?php
					$confirmation_page_footer_text = simpay_get_payment_form_setting(
						$post_id,
						'_confirmation_page_footer_text',
						'This content is neither created nor endorsed by WP Simple Pay',
						__unstable_simpay_get_payment_form_template_from_url()
					);
					?>

					<label for="_confirmation_page_footer_text" class="simpay-field-bool">
						<input
							name="_confirmation_page_footer_text"
							type="text"
							id="_confirmation_page_footer_text"
							class="simpay-field"
							value="<?php echo esc_attr( $confirmation_page_footer_text ); ?>"
							style="width: 80%;"
						/>
					</label>
					<div style="height: 8px;"></div>

					<?php

					$confirmation_page_powered_by = simpay_get_payment_form_setting(
						$post_id,
						'_confirmation_page_powered_by',
						'no',
						__unstable_simpay_get_payment_form_template_from_url()
					);
					?>

					<label for="_confirmation_page_powered_by" class="simpay-field-bool">
						<input
							name="_confirmation_page_powered_by"
							type="checkbox"
							id="_confirmation_page_powered_by"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
							value="yes"
							<?php checked( true, 'yes' === $confirmation_page_powered_by ); ?>
						/><?php esc_html_e( 'Hide WP Simple Pay branding', 'stripe' ); ?>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	</div>

	<?php
}

add_action(
	'simpay_form_settings_confirmation_panel',
	__NAMESPACE__ . '\\_add_dedicated_confirmation_page_customization_options',
	20
);
