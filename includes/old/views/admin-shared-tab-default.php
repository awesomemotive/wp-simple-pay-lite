<?php 

/**
 * Represents the view for the Default Settings tab - Shared between SP Lite & Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $sc_options;

?>

<!-- Default Settings tab HTML -->
<div class="tab-content sc-admin-hidden" id="default-settings-tab">
	<div>
		<strong><a href="<?php echo Stripe_Checkout_Admin::ga_campaign_url( SIMPAY_DOCS_BASE_URL . 'articles/basic-shortcodes-legacy/', 'help-link' ); ?>" target="_blank">
				<?php _e( 'See shortcode reference & examples.', 'stripe' ); ?>
			</a></strong>
		<br /><br />
		<?php $sc_options->description( __( 'Shortcode attributes take precedence and will always override site-wide default settings.', 'stripe' ) ); ?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'name' ) ); ?>"><?php _e( 'Site Name', 'stripe' ); ?></label>
		<?php 
			$sc_options->textbox( 'name', 'regular-text' ); 
			$sc_options->description( __( 'The name of your store or website. Defaults to Site Title if left blank.', 'stripe' ) );
		?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'currency' ) ); ?>"><?php _e( 'Currency', 'stripe' ); ?></label>
		<?php
			$sc_options->textbox( 'currency', 'regular-text' );
			$sc_options->description( sprintf( __( 'Specify a currency using it\'s <a href="%s" target="_blank">3-letter ISO Code</a>. Defaults to USD if left blank.', 'stripe' ), 'https://stripe.com/docs/currencies#charge-currencies' ) );
		?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'image_url' ) ); ?>"><?php _e( 'Image URL', 'stripe' ); ?></label>
		<?php 
			$sc_options->textbox( 'image_url', 'regular-text' );
			$sc_options->description( __( 'A URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px.', 'stripe' ) );
		?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'payment_button_label' ) ); ?>"><?php _e( 'Payment Button Label', 'stripe' ); ?></label>
		<?php
		$sc_options->textbox( 'payment_button_label', 'regular-text' );
		$sc_options->description( __( 'Text to display on the default blue button that users click to initiate a checkout process. Defaults to "Pay with Card" if left blank.', 'stripe' ) );
		?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'checkout_button_label' ) ); ?>"><?php _e( 'Checkout Button Label', 'stripe' ); ?></label>
		<?php 
			$sc_options->textbox( 'checkout_button_label', 'regular-text' );
			$sc_options->description( __( 'Text to display on the button within the checkout overlay. Insert {{amount}} where you\'d like to show the amount. If {{amount}} is omitted, it will be appended at the end of the button text unless it is a free trial.', 'stripe' ) );
		?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'success_redirect_url' ) ); ?>"><?php _e( 'Success Redirect URL', 'stripe' ); ?></label>
		<?php 
			$sc_options->textbox( 'success_redirect_url', 'regular-text' ); 
			$sc_options->description( __( 'The URL that the user should be redirected to after a successful payment.', 'stripe' ) );
		?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'disable_success_message' ) ); ?>"><?php _e( 'Disable Success Message', 'stripe' ); ?></label>
		<?php $sc_options->checkbox( 'disable_success_message' ); ?>
		<span><?php _e( 'Disable default success message. Useful if you are redirecting to your own success page.', 'stripe' ); ?></span>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'failure_redirect_url' ) ); ?>"><?php _e( 'Failure Redirect URL', 'stripe' ); ?></label>
		<?php 
			$sc_options->textbox( 'failure_redirect_url', 'regular-text' ); 
			$sc_options->description( __( 'The URL that the user should be redirected to after a failed payment.', 'stripe' ) );
		?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'billing' ) ); ?>"><?php _e( 'Enable Billing Address', 'stripe' ); ?></label>
		<?php $sc_options->checkbox( 'billing' ); ?>
		<span><?php _e( 'Require the user to enter their billing address during checkout.', 'stripe' ); ?></span>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'verify_zip' ) ); ?>"><?php _e( 'Verify Zip', 'stripe' ); ?></label>
		<?php $sc_options->checkbox( 'verify_zip' ); ?>
		<span><?php _e( 'Verifies the zip code of the card.', 'stripe' ); ?></span>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'enable_remember' ) ); ?>"><?php _e( 'Enable Remember', 'stripe' ); ?></label>
		<?php $sc_options->checkbox( 'enable_remember' ); ?>
		<span><?php _e( 'Adds a "Remember Me" option to the checkout form to allow the user to store their credit card for future use with other sites using Stripe.', 'stripe' ); ?></span>
	</div>
	
	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'locale' ) ); ?>"><?php _e( 'Set Locale', 'stripe' ); ?></label>
		<?php
			$sc_options->textbox( 'locale', 'small-text' );
			$sc_options->description( sprintf( __( '"auto" is used by default to select a language based on the user\'s browser configuration. '.
			                                       'To select a particular language, pass the two letter ISO 639-1 code such as "zh" for Chinese. <br/>' .
			                                       '<a href="%s" target="_blank">See languages supported by Stripe Checkout</a>', 'stripe' ),
				'https://stripe.com/docs/checkout#supported-languages' ) );
		?>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'disable_css' ) ); ?>"><?php _e( 'Disable Plugin CSS', 'stripe' ); ?></label>
		<?php $sc_options->checkbox( 'disable_css' ); ?>
		<span><?php _e( "If this option is checked, this plugin's CSS file will not be referenced.", 'stripe' ); ?></span>
	</div>

	<div>
		<label for="<?php echo esc_attr( $sc_options->get_setting_id( 'uninstall_save_settings' ) ); ?>"><?php _e( 'Save Settings', 'stripe' ); ?></label>
		<?php $sc_options->checkbox( 'uninstall_save_settings' ); ?>
		<span><?php _e( 'Save your settings when uninstalling this plugin. Useful when upgrading or re-installing.', 'stripe' ); ?></span>
	</div>
	
	
	<?php do_action( 'sc_settings_tab_default' ); ?>
</div>
