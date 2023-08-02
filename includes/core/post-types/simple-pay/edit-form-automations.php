<?php
/**
 * Form Builder: Automations
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.8
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a list of integrations available for Uncanny Automator.
 *
 * @since 4.7.8
 *
 * @return array<array<string, string>>
 */
function get_automator_integrations() {
	$upload_dir = wp_upload_dir();
	$cache_dir  = trailingslashit( $upload_dir['basedir'] );
	$cache_file = $cache_dir . 'wpsp-uncanny-automator-integrations.json';

	if ( ! file_exists( $cache_file ) ) {
		return array();
	}

	$cache_file_contents = file_get_contents( $cache_file );

	if ( false === $cache_file_contents ) {
		return array();
	}

	$integrations = json_decode( $cache_file_contents, true );

	if ( ! is_array( $integrations ) ) {
		return array();
	}

	// Remove WP Simple Pay. We will create our own direct recipe link.
	unset( $integrations['WPSIMPLEPAY'] );

	shuffle( $integrations );

	return $integrations;
}

/**
 * Adds content for the "Automations" tab.
 *
 * @since 4.7.8
 *
 * @return void
 */
function add_automations() {
	$integrations = get_automator_integrations();
	$has_plugin   = defined( 'AUTOMATOR_BASE_FILE' );

	// If Uncanny Automator is active, link to the new automation screen.
	// Otherwise link to the plugin installation page to auto install.
	$url = $has_plugin
		? add_query_arg( 'post_type', 'uo-recipe', admin_url( 'post-new.php' ) )
		: wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'install-plugin',
					'plugin' => 'uncanny-automator',
				),
				admin_url( 'update.php' )
			),
			'install-plugin_uncanny-automator'
		);

	// If the free version is installed, link to the pro upgrade page.
	$cta_url = $url;

	$cta_text = $has_plugin
		? __( 'Automate this Payment Form', 'stripe' )
		: wp_kses(
			sprintf(
				/* translators: %1$s Opening HTML, do not translate. %2$s Closing HTML, do not translate. */
				__( 'Start Automating for %1$sFree!%2$s', 'stripe' ),
				'<strong><u>',
				'</u></strong>'
			),
			array(
				'strong' => array(),
				'u'      => array(),
			)
		);
	?>

	<table class="simpay-form-builder-automations">
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<td style="padding: 20px 4px; box-sizing: border-box;">
					<div style="display: flex; justify-content: space-between; align-items: center;">
						<div style="margin-right: 0px;">
							<strong style="font-size: 18px;">
								<?php esc_html_e( 'Super Charge WP Simple Pay with Uncanny Automator', 'stripe' ); ?>
							</strong>
							<p style="margin: 2px 0 0;">
								<?php
								esc_html_e(
									'Slash development time and costs with the most popular automation plugin for WordPress.',
									'stripe'
								);
								?>
							</p>
						</div>
						<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/simple-pay-uncanny.svg' ); ?>" alt="Uncanny Automator" style="width: 200px;"/>
					</div>
				</td>
				<td class="simpay-form-builder-automator">
					<?php if ( ! empty( $integrations ) ) : ?>
					<label for="automations-search" class="screen-reader-text">
						<?php esc_html_e( 'Search available integrations', 'stripe' ); ?>
					</label>
					<input
						type="search"
						placeholder="<?php esc_html_e( 'Mailchimp, Google Sheets, WordPress', 'stripe' ); ?>"
						style="width: 100%;"
						id="automations-search"
						class="regular-text simpay-form-builder-automator__search"
					/>

					<div id="automations-results" class="simpay-form-builder-automator__integrations">
						<?php foreach ( $integrations as $i => $integration ) : ?>
						<a
							href="<?php echo esc_url( $url ); ?>"
							target="_blank"
							class="simpay-form-builder-automator__integrations-integration"
							data-id="<?php echo esc_attr( $integration['integration_id'] ); ?>"
							data-name="<?php echo esc_attr( $integration['integration_name'] ); ?>"
							data-description="<?php echo esc_attr( $integration['short_description'] ); ?>"
						>
							<img src="<?php echo esc_url( $integration['integration_icon'] ); ?>" />
							<span>
								<?php echo esc_html( $integration['integration_name'] ); ?>
							</span>
						</a>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
					<div class="simpay-form-builder-automations__cta">
						<a href="<?php echo esc_url( $cta_url ); ?>" target="_blank" class="button button-primary button-parge" style="font-size: 16px; font-weight: 500; padding: 3px 20px !important; height: auto;">
							<?php echo $cta_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_automations_panel',
	__NAMESPACE__ . '\\add_automations'
);
