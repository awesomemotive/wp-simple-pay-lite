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
						'default',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					simpay_print_field(
						array(
							'type'    => 'radio',
							'name'    => '_success_redirect_type',
							'id'      => '_success_redirect_type',
							'class'   => array( 'simpay-multi-toggle' ),
							'options' => array(
								'default'  => __( 'Global Setting', 'stripe' ),
								'page'     => __( 'Custom Page', 'stripe' ),
								'redirect' => __( 'External URL', 'stripe' ),
							),
							'inline'  => 'inline',
							'default' => 'default',
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

	<table class="simpay-show-if" data-if="_success_redirect_type" data-is="default">
		<tbody class="simpay-panel-section">
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
