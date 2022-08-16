<?php
/**
 * Edit Form: Payment Page
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

use Sandhills\Utils\Persistent_Dismissible;
use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds dismissible (1 year) education about payment pages.
 *
 * @since 4.5.0
 *
 * @return void
 */
function payment_page_education() {
	// Dismissed temporary notice.
	$dismissed_notice = (bool) Persistent_Dismissible::get(
		array(
			'id' => 'simpay-form-settings-payment-page-education',
		)
	);

	if ( true === $dismissed_notice ) {
		return;
	}

	$features_url = simpay_docs_link(
		'Payment Pages',
		'how-to-use-payment-pages',
		'form-payment-page-settings',
		true
	);

	include_once SIMPLE_PAY_DIR . '/views/admin-education-payment-form-payment-page-settings.php'; // @phpstan-ignore-line
}
add_action(
	'simpay_form_settings_payment_page_panel',
	__NAMESPACE__ . '\\payment_page_education'
);

/**
 * Adds the "Payment Page Mode" setting.
 *
 * @since 4.5.0
 *
 * @param int $post_id Payment Form ID.
 * @return void
 */
function payment_page_enable( $post_id ) {
	$structure = get_option( 'permalink_structure' );

	?>

	<table>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Payment Page Mode', 'stripe' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0;">
					<?php
					$license = simpay_get_license();

					$enable_payment_page = simpay_get_payment_form_setting(
						$post_id,
						'_enable_payment_page',
						'no',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					$enable_payment_page = (
						$license->is_pro( 'professional', '>=' ) &&
						'yes' === $enable_payment_page ? 'yes' : 'no'
					);

					$upgrade_title = __(
						'Unlock Payment Pages',
						'stripe'
					);

					$upgrade_description = __(
						'We\'re sorry, creating dedicated payment pages is not available on your plan. Please upgrade to the <strong>Professional</strong> plan or higher to unlock this and other awesome features.',
						'stripe'
					);

					$upgrade_url = simpay_pro_upgrade_url(
						'form-payment-page-settings',
						'Payment Page Mode'
					);

					$upgrade_purchased_url = simpay_docs_link(
						'Payment Page Mode (already purchased)',
						'upgrading-wp-simple-pay-lite-to-pro',
						$license->is_lite()
							? 'upgrading-wp-simple-pay-lite-to-pro'
							: 'activate-wp-simple-pay-pro-license',
						true
					);
					?>

					<label for="_enable_payment_page" class="simpay-field-bool">
						<input
							name="_enable_payment_page"
							type="checkbox"
							id="_enable_payment_page"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
							value="yes"
							data-available="<?php echo $license->is_pro( 'professional' ) ? 'yes' : 'no'; ?>"
							data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
							data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
							data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
							data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
							<?php if ( empty( $structure ) ) : ?>
								disabled
							<?php else : ?>
								<?php checked( true, 'yes' === $enable_payment_page ); ?>
							<?php endif; ?>
						/><?php esc_html_e( 'Enable a dedicated payment page', 'stripe' ); ?>
					</label>

					<?php if ( empty( $structure ) ) : ?>
						<div class="notice notice-warning inline" style="margin-top: 12px;"><p>
						<?php
						echo wp_kses(
							sprintf(
								/* translators: %1$s Opening <a> tag, do not translate. %2$s Closing </a> tag, do not translate. */
								__(
									'Heads up! To use Payment Pages, please configure your site\'s permalinks on the WordPress %1$sPermalink Settings%2$s page.',
									'stripe'
								),
								'<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
								Utils\get_external_link_markup() . '</a>'
							),
							array(
								'a'    => array(
									'href'   => true,
									'rel'    => true,
									'target' => true,
									'class'  => true,
								),
								'span' => array(
									'class' => true,
								),
							)
						);
						?>
						</p></div>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_payment_page_panel',
	__NAMESPACE__ . '\\payment_page_enable'
);
