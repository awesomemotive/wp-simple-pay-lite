<div class="tab-content" id="default-settings-tab">
	<form method="post" action="#default-settings" id="default-settings">
		<?php
			global $settings;
		?>
		<div>
			<a href="<?php echo Stripe_Checkout_Misc::ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/shortcodes/stripe-checkout/', 'stripe_checkout', 'settings', 'docs' ); ?>" target="_blank">
				<?php _e( 'See shortcode options and examples for Stripe Checkout.', 'sc' ); ?>
			</a>
			<?php $settings->description( __( 'Shortcode attributes take precedence and will always override site-wide default settings.', 'sc' ) ); ?>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'name' ); ?>"><?php _e( 'Site Name', 'sc' ); ?></label>
			<?php 
				$settings->description( __( 'The name of your store or website. Defaults to Site Name.', 'sc' ) );
				$settings->textbox( 'name', 'regular-text' ); 
			?>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'currency' ); ?>"><?php _e( 'Currency', 'sc' ); ?></label>
			<?php
				$settings->description( sprintf( __( 'Specify a currency using it\'s <a href="%s" target="_blank">3-letter ISO Code</a>. Defaults to USD.', 'sc' ), 'https://support.stripe.com/questions/which-currencies-does-stripe-support' ) );
				$settings->textbox( 'currency', 'regular-text' ); 
			?>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'image_url' ); ?>"><?php _e( 'Image URL', 'sc' ); ?></label>
			<?php 
				$settings->description( __( 'A URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px.', 'sc' ) );
				$settings->textbox( 'image_url', 'regular-text' ); 
			?>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'checkout_button_label' ); ?>"><?php _e( 'Checkout Button Label', 'sc' ); ?></label>
			<?php 
				$settings->description( __( 'The label of the payment button in the checkout form. You can use {{amount}} to display the amount.', 'sc' ) );
				$settings->textbox( 'checkout_button_label', 'regular-text' ); 
			?>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'payment_button_label' ); ?>"><?php _e( 'Payment Button Label', 'sc' ); ?></label>
			<?php 
				$settings->description( __( 'Text to display on the default blue button that users click to initiate a checkout process.', 'sc' ) );
				$settings->textbox( 'payment_button_label', 'regular-text' ); 
			?>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'success_redirect_url' ); ?>"><?php _e( 'Success Redirect URL', 'sc' ); ?></label>
			<?php 
				$settings->description( __( 'The URL that the user should be redirected to after a successful payment.', 'sc' ) );
				$settings->textbox( 'success_redirect_url', 'regular-text' ); 
			?>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'disable_success_message' ); ?>"><?php _e( 'Disable Success Message', 'sc' ); ?></label>
			<?php $settings->checkbox( 'disable_success_message' ); ?>
			<span><?php _e( 'Disable default success message. Useful if you are redirecting to your own success page.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'failure_redirect_url' ); ?>"><?php _e( 'Failure Redirect URL', 'sc' ); ?></label>
			<p class="description">
				<?php _e( 'The URL that the user should be redirected to after a failed payment.', 'sc' ); ?>
			</p>
			<?php $settings->textbox( 'failure_redirect_url', 'regular-text' ); ?>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'billing' ); ?>"><?php _e( 'Billing', 'sc' ); ?></label>
			<?php $settings->checkbox( 'billing' ); ?>
			<span><?php _e( 'Require the user to enter their billing address during checkout.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'verify_zip' ); ?>"><?php _e( 'Verify Zip', 'sc' ); ?></label>
			<?php $settings->checkbox( 'verify_zip' ); ?>
			<span><?php _e( 'Verifies the zip code of the card.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'enable_remember' ); ?>"><?php _e( 'Enable Remember', 'sc' ); ?></label>
			<?php $settings->checkbox( 'enable_remember' ); ?>
			<span><?php _e( 'Adds a "Remember Me" option to the checkout form to allow the user to store their credit card for future use with other sites using Stripe.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'disable_css' ); ?>"><?php _e( 'Disable CSS', 'sc' ); ?></label>
			<?php $settings->checkbox( 'disable_css' ); ?>
			<span><?php _e( 'Disable the plugin from ouputting the default form CSS.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'always_enqueue' ); ?>"><?php _e( 'Always Enqueue', 'sc' ); ?></label>
			<?php $settings->checkbox( 'always_enqueue' ); ?>
			<span><?php _e( 'Enqueue this plugin\'s scripts and styles on every post and page. Useful if using shortcodes in widgets or other non-standard locations.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="<?php echo $settings->get_setting_id( 'uninstall_save_settings' ); ?>"><?php _e( 'Save Settings', 'sc' ); ?></label>
			<?php $settings->checkbox( 'uninstall_save_settings' ); ?>
			<span><?php _e( 'Save your settings when uninstalling this plugin. Useful when upgrading or re-installing.', 'sc' ); ?></span>
		</div>
		
		
		<?php $settings->ajax_save_button( 'test', 'Click to Save!' ); ?>
	</form>
</div>

