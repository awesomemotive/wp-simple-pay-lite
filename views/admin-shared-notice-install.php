<?php

/**
 * Show notice after plugin install/activate in admin dashboard - Shared between SP Lite & Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $base_class;

?>

<style>
	#sc-install-notice .button-primary,
	#sc-install-notice .button-secondary {
		margin-left: 15px;
	}
</style>

<div id="sc-install-notice" class="updated">
	<p>
		<?php echo $base_class->get_plugin_title() . __( ' is now installed.', 'stripe' ); ?>
		<a href="<?php echo esc_url( add_query_arg( 'page', $base_class->plugin_slug, admin_url( 'admin.php' ) ) ); ?>" class="button-primary"><?php _e( 'Get started by entering your Stripe keys', 'stripe' ); ?></a>
		<a href="<?php echo Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/', 'activation-notice' ); ?>" class="button-secondary" target="_blank"><?php _e( 'Get help', 'stripe' ); ?></a>
		<a href="<?php echo esc_url( add_query_arg( 'sc-dismiss-install-nag', 1 ) ); ?>" class="button-secondary"><?php _e( 'Hide this', 'stripe' ); ?></a>
	</p>
</div>
