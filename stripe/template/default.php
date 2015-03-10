<div class="tab-content" id="default-settings-tab">
	<form method="post" action="#default-settings" id="default-settings">
		<?php
			//$settings = new MM_Settings( 'sc_settings' );
		
			global $settings;
		?>
		<div>
			<a href="<?php echo Stripe_Checkout_Misc::ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/shortcodes/stripe-checkout/', 'stripe_checkout', 'settings', 'docs' ); ?>" target="_blank">
				<?php _e( 'See shortcode options and examples for Stripe Checkout.', 'sc' ); ?>
			</a>
			<p class="description"> 
				<?php _e( 'Shortcode attributes take precedence and will always override site-wide default settings.', 'sc' ); ?>
			</p>
		</div>

		<div>
			<label for="sc_settings_name">Site Name</label>
			<p class="description">
				<?php _e( 'The name of your store or website. Defaults to Site Name.', 'sc' ); ?>
			</p>
			<input type="text" class="regular-text" name="sc_settings_name" id="sc_settings_name" value="<?php echo $settings->get_setting_value( 'sc_settings_name' ); ?>" />
		</div>

		<div>
			<label for="sc_settings_currency">Currency</label>
			<p class="description">
				<?php
					printf( __( 'Specify a currency using it\'s <a href="%s" target="_blank">3-letter ISO Code</a>. Defaults to USD.', 'sc' ), 'https://support.stripe.com/questions/which-currencies-does-stripe-support' );
				?>
			</p>
			<input type="text" class="regular-text" name="sc_settings_currency" id="sc_settings_currency" value="<?php echo $settings->get_setting_value( 'sc_settings_currency' ); ?>" />
		</div>

		<div>
			<label for="sc_settings_image_url">Image URL</label>
			<p class="description">
				<?php _e( 'A URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px.', 'sc' ); ?>
			</p>
			<input type="text" class="regular-text" name="sc_settings_image_url" id="sc_settings_image_url" value="<?php echo $settings->get_setting_value( 'sc_settings_image_url' ); ?>" />
		</div>

		<div>
			<label for="sc_settings_checkout_button_label">Checkout Button Label</label>
			<p class="description">
				<?php _e( 'The label of the payment button in the checkout form. You can use {{amount}} to display the amount.', 'sc' ); ?>
			</p>
			<input type="text" class="regular-text" name="sc_settings_checkout_button_label" id="sc_settings_checkout_button_label" value="<?php echo $settings->get_setting_value( 'sc_settings_checkout_button_label' ); ?>" />
		</div>

		<div>
			<label for="sc_settings_payment_button_label">Payment Button Label</label>
			<p class="description">
				<?php _e( 'Text to display on the default blue button that users click to initiate a checkout process.', 'sc' ); ?>
			</p>
			<input type="text" class="regular-text" name="sc_settings_payment_button_label" id="sc_settings_payment_button_label" value="<?php echo $settings->get_setting_value( 'sc_settings_payment_button_label' ); ?>" />
		</div>

		<div>
			<label for="sc_settings_success_redirect_url">Success Redirect URL</label>
			<p class="description">
				<?php _e( 'The URL that the user should be redirected to after a successful payment.', 'sc' ); ?>
			</p>
			<input type="text" class="regular-text" name="sc_settings_success_redirect_url" id="sc_settings_success_redirect_url" value="<?php echo $settings->get_setting_value( 'sc_settings_success_redirect_url' ); ?>" />
		</div>

		<div>
			<label for="sc_settings_disable_success_message">Disable Success Message</label>
			<input type="checkbox" class="" name="sc_settings_disable_success_message" id="sc_settings_disable_success_message" value="1" />
			<span><?php _e( 'Disable default success message. Useful if you are redirecting to your own success page.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="sc_settings_failure_redirect_url">Failure Redirect URL</label>
			<p class="description">
				<?php _e( 'The URL that the user should be redirected to after a failed payment.', 'sc' ); ?>
			</p>
			<input type="text" class="regular-text" name="sc_settings_failure_redirect_url" id="sc_settings_failure_redirect_url" value="<?php echo $settings->get_setting_value( 'sc_settings_failure_redirect_url' ); ?>" />
		</div>

		<div>
			<label for="sc_settings_billing">Billing</label>
			<input type="checkbox" class="" name="sc_settings_billing" id="sc_settings_billing" value="1" />
			<span><?php _e( 'Require the user to enter their billing address during checkout.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="sc_settings_verify_zip">Verify Zip</label>
			<input type="checkbox" class="" name="sc_settings_verify_zip" id="sc_settings_verify_zip" value="1" />
			<span><?php _e( 'Verifies the zip code of the card.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="sc_settings_enable_remember">Enable Remember</label>
			<input type="checkbox" class="" name="sc_settings_enable_remember" id="sc_settings_enable_remember" value="1" />
			<span><?php _e( 'Adds a "Remember Me" option to the checkout form to allow the user to store their credit card for future use with other sites using Stripe.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="sc_settings_disable_css">Disable CSS</label>
			<input type="checkbox" class="" name="sc_settings_disable_css" id="sc_settings_disable_css" value="1" />
			<span><?php _e( 'Disable the plugin from ouputting the default form CSS.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="sc_settings_always_enqueue">Always Enqueue</label>
			<input type="checkbox" class="" name="sc_settings_always_enqueue" id="sc_settings_always_enqueue" value="1" />
			<span><?php _e( 'Enqueue this plugin\'s scripts and styles on every post and page. Useful if using shortcodes in widgets or other non-standard locations.', 'sc' ); ?></span>
		</div>

		<div>
			<label for="sc_settings_uninstall_save_settings">Save Settings</label>
			<input type="checkbox" class="" name="sc_settings_uninstall_save_settings" id="sc_settings_uninstall_save_settings" value="1" />
			<span><?php _e( 'Save your settings when uninstalling this plugin. Useful when upgrading or re-installing.', 'sc' ); ?></span>
		</div>
		
		
		<?php $settings->ajax_save_button( 'test', 'Click to Save!' ); ?>
	</form>
</div>

