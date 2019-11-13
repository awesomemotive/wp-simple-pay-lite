<?php
/**
 * Promos: Black Friday/Cyber Monday Sidebar
 *
 * Additional promotional notices.
 *
 * @package SimplePay\Core\Admin\Notices\Promos
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

// Get the current global settings tab, or set a default
$current_tab = ! empty( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : 'keys';

// Make sure the current tab value is recognizable
switch ( $current_tab ) {
	case 'keys':
		$current_tab = 'stripe-setup';
		break;
	case 'display':
		$current_tab = 'payment-confirmation';
		break;
}

$coupon_code = 'BFCM2019';

$utm_args    = array(
	'utm_source'   => 'global-settings',
	'utm_medium'   => 'wp-admin',
	'utm_campaign' => 'bfcm2019',
	'utm_content'  => 'sidebar-promo-' . $current_tab,
);
$url         = add_query_arg( $utm_args, 'https://wpsimplepay.com/lite-vs-pro/' );
?>

<div class="simpay-settings-sidebar-content">

	<div class="simpay-sidebar-header-section">
		<img class="simpay-bcfm-header" src="<?php echo esc_url( SIMPLE_PAY_URL . 'includes/core/admin/notices/promos/bfcm/images/bfcm-header.svg' ); ?>">
	</div>

	<div class="simpay-sidebar-description-section">
		<p class="simpay-sidebar-description"><?php _e( 'Save 25% on all WP Simple Pay Pro purchases <strong>this week</strong>, including renewals and upgrades!', 'stripe' ); ?></p>
	</div>

	<div class="simpay-sidebar-coupon-section">
		<label for="simpay-coupon-code"><?php _e( 'Use code at checkout:', 'stripe' ); ?></label>
		<input id="simpay-coupon-code" type="text" value="<?php echo $coupon_code; ?>" readonly>
		<p class="simpay-coupon-note"><?php _e( 'Sale ends 23:59 PM December 6th CST. Save 25% on <a href="https://sandhillsdev.com/projects/" target="_blank">our other plugins</a>.', 'stripe' ); ?></p>
	</div>

	<div class="simpay-sidebar-footer-section">
		<a class="simpay-cta-button" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php _e( 'Upgrade Now!', 'stripe' ); ?></a>
	</div>

</div>
