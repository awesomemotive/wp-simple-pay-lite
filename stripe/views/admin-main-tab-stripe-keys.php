<?php 

	/**
	 * Represents the view for the Stripe Keys admin tab.
	 */

	global $sc_options; 
?>

<!-- Stripe Keys tab HTML -->
<div class="sc-admin-hidden" id="stripe-keys-settings-tab">
	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'enable_live_key' ) ); ?>"><?php _e( 'Test or Live Mode', 'sc' ); ?></label>
		<?php $sc_options->toggle_control( 'enable_live_key', array( __( 'Test', 'sc' ), __( 'Live', 'sc' ) ) ); ?>
		<?php $sc_options->description( sprintf( '<a href="%1$s" target="_blank">%2$s</a>', 'https://dashboard.stripe.com/account/apikeys', 
													__( 'Find your Stripe API keys here', 'sc' ) ), '' ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'test_secret_key' ) ); ?>"><?php _e( 'Test Secret Key', 'sc' ); ?></label>
		<?php $sc_options->textbox( 'test_secret_key', 'regular-text' ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'test_publish_key' ) ); ?>"><?php _e( 'Test Publishable Key', 'sc' ); ?></label>
		<?php $sc_options->textbox( 'test_publish_key', 'regular-text' ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'live_secret_key' ) ); ?>"><?php _e( 'Live Secret Key', 'sc' ); ?></label>
		<?php $sc_options->textbox( 'live_secret_key', 'regular-text' ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'live_publish_key' ) ); ?>"><?php _e( 'Live Publishable Key', 'sc' ); ?></label>
		<?php $sc_options->textbox( 'live_publish_key', 'regular-text' ); ?>
	</div>
</div>