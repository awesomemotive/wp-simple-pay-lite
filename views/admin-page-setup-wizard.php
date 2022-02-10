<?php
/**
 * Admin: "Setup Wizard" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

?>

<style>
	html {
		padding-top: 0 !important;
	}

	.wrap,
	.simpay-branding-bar,
	.notice,
	.simpay-notice,
	#adminmenumain,
	#wpadminbar,
	#wpfooter {
		display: none !important;
	}

	#wpcontent {
		margin-left: 0 !important;
		padding-left: 0 !important;
	}

	#wpbody,
	#wpbody-content {
		height: 100% !important;
	}

	#wpbody {
		padding-top: 0 !important;
	}

	#wpbody-content {
		float: none !important;
		padding-bottom: 0 !important;
	}

	.simpay-setup-wizard {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 100vw;
	}
</style>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Setup Wizard', 'simple-pay' ); ?>
	</h1>
	<hr class="wp-header-end">
</div>

<div id="simpay-setup-wizard" class="simpay-setup-wizard"></div>
