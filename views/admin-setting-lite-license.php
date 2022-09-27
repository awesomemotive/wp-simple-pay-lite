<?php
/**
 * Admin setting: Lite License
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.2
 *
 * @var string $nonce License management nonce.
 */

use SimplePay\Core\Utils;

?>

<p style="font-size: 110%;">
	<?php
	esc_html_e(
		'You\'re using WP Simple Pay Lite - no license needed. Enjoy! 😊',
		'stripe'
	);
	?>
</p>

<p class="description" style="margin-bottom: 8px;">
<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__(
				'Already purchased? Simply %1$sretrieve your license key%2$s and enter it below to connect with WP Simple Pay Pro.',
				'stripe'
			),
			sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
				simpay_ga_url(
					'https://wpsimplepay.com/my-account/licenses/',
					'license-settings',
					'retrieve your license key'
				)
			),
			Utils\get_external_link_markup() . '</a>'
		)
	);
	?>
</p>

<div class="simpay-license-field">
	<input
		type="text"
		id="simpay-connect-license-key"
		name="simpay-license-key"
		value=""
		class="regular-text"
		style="line-height: 1; font-size: 1.15rem; padding: 10px;"
	/>

	<button
		class="button button-secondary simpay-license-button"
		id="simpay-connect-license-submit"
		data-connecting="<?php esc_attr_e( 'Connecting...', 'stripe' ); ?>"
		data-connect="<?php esc_attr_e( 'Unlock Pro Features Now', 'stripe' ); ?>"
	>
		<?php esc_html_e( 'Unlock Pro Features Now', 'stripe' ); ?>
	</button>

	<input type="hidden" name="simpay-action" value="simpay-connect" />
	<input type="hidden" id="simpay-connect-license-nonce" name="simpay-connect-license-nonce" value="<?php echo esc_attr( $nonce ); ?>" />
</div>

<div id="simpay-connect-license-feedback" class="simpay-license-message"></div>

<div class="simpay-settings-upgrade">
	<div class="simpay-settings-upgrade__inner">
		<span class="dashicons dashicons-unlock" style="font-size: 40px; width: 40px; height: 50px;"></span>
		<h3>
			<?php
			esc_html_e(
				'Unlock Powerful Pro Features',
				'stripe'
			);
			?>
		</h3>

		<ul>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/wordpress-payments/', 'license-settings-upgrade', 'Accept Payments On-Site' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Accept Payments On-Site', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/subscriptions/', 'license-settings-upgrade', 'Create Recurring Payments' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Create Recurring Payments', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/custom-fields/', 'license-settings-upgrade', 'Collect Custom Data' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Collect Custom Data', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/payment-form-builder/', 'license-settings-upgrade', 'Advanced Form Builder' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Advanced Form Builder', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/payment-methods/', 'license-settings-upgrade', '10+ Payment Methods' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( '10+ Payment Methods', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/coupons/', 'license-settings-upgrade', 'Offer Discount Codes' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Offer Discount Codes', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/buy-now-pay-later/', 'license-settings-upgrade', 'Buy Now, Pay Later' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Buy Now, Pay Later', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/taxes/', 'license-settings-upgrade', 'Collect Taxes and Fees' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Collect Taxes and Fees', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/payment-form-templates/', 'license-settings-upgrade', 'Additional Form Templates' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Additional Form Templates', 'stripe' ); ?>
				</a>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/features/payment-pages/', 'license-settings-upgrade', 'Dedicated Payment Pages' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Dedicated Payment Pages', 'stripe' ); ?>
				</a>
			</li>
		</ul>

		<a href="<?php echo esc_url( simpay_ga_url( 'https://wpsimplepay.com/lite-vs-pro/', 'license-settings-upgrade', 'Upgrade to WP Simple Pay Pro' ) ); ?>" class="button button-primary button-large simpay-upgrade-btn simpay-upgrade-btn-large" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Upgrade to WP Simple Pay Pro', 'stripe' ); ?>
		</a>
	</div>

	<div class="simpay-upgrade-btn-subtext">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img" aria-hidden="true" focusable="false">
			<path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path>
		</svg>

		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'<strong>Bonus</strong>: Loyal WP Simple Pay Lite users get <u>50%% off</u> regular price, automatically applied at checkout. %1$sUpgrade to Pro →%2$s',
					'stripe'
				),
				sprintf(
					'<a href="%s" rel="noopener noreferrer" target="_blank">',
					esc_url(
						simpay_ga_url(
							'https://wpsimplepay.com/lite-vs-pro/',
							'license-settings-upgrade',
							'Upgrade to Pro →'
						)
					)
				),
				'</a>'
			),
			array(
				'a'      => array(
					'href'   => true,
					'rel'    => true,
					'target' => true,
				),
				'strong' => array(),
				'u'      => array(),
			)
		);
		?>
	</div>
</div>
