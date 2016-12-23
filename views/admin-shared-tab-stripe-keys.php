<?php

/**
 * Represents the view for the Stripe Keys admin tab - Shared between SP Lite & Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $sc_options;

?>

<!-- Stripe Keys tab HTML -->
<div class="tab-content sc-admin-hidden" id="stripe-keys-settings-tab">
	<div class="sc-live-mode-toggle">
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'enable_live_key' ) ); ?>"><?php _e( 'Live Mode', 'stripe' ); ?></label>
		<?php $sc_options->sc_live_mode_toggle(); ?>
	</div>

	<div>
		<?php $sc_options->description( sprintf( __( 'Switch to OFF while testing. Make sure Test mode is enabled in your <a href="%s" target="_blank">Stripe dashboard</a> to view test transactions.', 'stripe' ), 'https://dashboard.stripe.com/' ) ); ?>
		<?php $sc_options->description( sprintf( __( '<a href="%s" target="_blank">Find your Stripe API keys here</a>', 'stripe' ), 'https://dashboard.stripe.com/account/apikeys' ) ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'test_secret_key' ) ); ?>"><?php _e( 'Test Secret Key', 'stripe' ); ?></label>
		<?php $sc_options->textbox( 'test_secret_key', 'regular-text' ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'test_publish_key' ) ); ?>"><?php _e( 'Test Publishable Key', 'stripe' ); ?></label>
		<?php $sc_options->textbox( 'test_publish_key', 'regular-text' ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'live_secret_key' ) ); ?>"><?php _e( 'Live Secret Key', 'stripe' ); ?></label>
		<?php $sc_options->textbox( 'live_secret_key', 'regular-text' ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'live_publish_key' ) ); ?>"><?php _e( 'Live Publishable Key', 'stripe' ); ?></label>
		<?php $sc_options->textbox( 'live_publish_key', 'regular-text' ); ?>
	</div>
</div>
