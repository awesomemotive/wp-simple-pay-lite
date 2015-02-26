<?php

// TODO: Add direct file access check

$sc_settings = array(

			/* Default Settings */
			'default' => array(
				'section_name' => '',
				'note' => array(
					'id'   => 'settings_note',
					'name' => '',
					'desc' => '<a href="' . Stripe_Checkout_Misc::ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/shortcodes/stripe-checkout/', 'stripe_checkout', 'settings', 'docs' ) . '" target="_blank">' .
							  __( 'See shortcode options and examples', 'sc' ) . '</a> ' . __( 'for', 'sc' ) . ' ' . Stripe_Checkout::get_plugin_title() .
							  '<p class="description">' . __( 'Shortcode attributes take precedence and will always override site-wide default settings.', 'sc' ) . '</p>',
					'type' => 'section',
					'sort' => 0
				),
				'name' => array(
					'id'   => 'name',
					'name' => __( 'Site Name', 'sc' ),
					'desc' => __( 'The name of your store or website. Defaults to Site Name.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 1
				),
				'currency' => array(
					'id'   => 'currency',
					'name' => __( 'Currency Code', 'sc' ),
					'desc' => __( 'Specify a currency using it\'s ', 'sc' ) .
								sprintf( '<a href="%s" target="_blank">%s</a>', 'https://support.stripe.com/questions/which-currencies-does-stripe-support', __('3-letter ISO Code', 'sc' ) ) . '. ' .
								__( 'Defaults to USD.', 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 2
				),
				'image_url' => array(
					'id'   => 'image_url',
					'name' => __( 'Image URL', 'sc' ),
					'desc' => __( 'A URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 3
				),
				'checkout_button_label' => array(
					'id'   => 'checkout_button_label',
					'name' => __( 'Checkout Button Label', 'sc' ),
					'desc' => __( 'The label of the payment button in the checkout form. You can use {{amount}} to display the amount.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 4
				),
				'payment_button_label' => array(
					'id'   => 'payment_button_label',
					'name' => __( 'Payment Button Label', 'sc' ),
					'desc' => __( 'Text to display on the default blue button that users click to initiate a checkout process.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 5
				),
				'success_redirect_url' => array(
					'id'   => 'success_redirect_url',
					'name' => __( 'Success Redirect URL', 'sc' ),
					'desc' => __( 'The URL that the user should be redirected to after a successful payment.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 6
				),
				'disable_success_message' => array(
					'id'   => 'disable_success_message',
					'name' => __( 'Disable Success Message', 'sc' ),
					'desc' => __( 'Disable default success message. Useful if you are redirecting to your own success page.', 'sc' ),
					'type' => 'checkbox',
					'sort' => 7
				),
				'failure_redirect_url' => array(
					'id'   => 'failure_redirect_url',
					'name' => __( 'Failure Redirect URL', 'sc' ),
					'desc' => __( 'The URL that the user should be redirected to after a failed payment.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 8
				),
				'billing' => array(
					'id'   => 'billing',
					'name' => __( 'Enable Billing Address', 'sc' ),
					'desc' => __( 'Require the user to enter their billing address during checkout.', 'sc' ) . 
							( class_exists( 'Stripe_Checkout_Pro' ) ? '<br><em>' . __( 'See below if you also need to require a shipping address.', 'sc' ) . '</em>' : '' ),
					'type' => 'checkbox',
					'sort' => 9
				),
				'verify_zip' => array(
					'id'   => 'verify_zip',
					'name' => __( 'Verify Zip Code', 'sc' ),
					'desc' => __( 'Verifies the zip code of the card.', 'sc' ),
					'type' => 'checkbox',
					'sort' => 10
				),
				'enable_remember' => array(
					'id'   => 'enable_remember',
					'name' => __( 'Enable "Remember Me"', 'sc' ),
					'desc' => __( 'Adds a "Remember Me" option to the checkout form to allow the user to store their credit card for future use with other sites using Stripe. ', 'sc' ) .
						sprintf( '<a href="%s" target="_blank">%s</a>', 'https://stripe.com/checkout/info', __('See how it works', 'sc' ) ) . '.',
					'type' => 'checkbox',
					'sort' => 11
				),
				'disable_css' => array(
					'id'   => 'disable_css',
					'name' => __( 'Disable Form CSS', 'sc' ),
					'desc' => __( 'Disable the plugin from ouputting the default form CSS.', 'sc' ),
					'type' => 'checkbox',
					'sort' => 12
				),
				'always_enqueue' => array(
					'id'   => 'always_enqueue',
					'name' => __( 'Always Enqueue Scripts & Styles', 'sc' ),
					'desc' => __( sprintf( 'Enqueue this plugin\'s scripts and styles on every post and page. Useful if using shortcodes in widgets or other non-standard locations.' ), 'sc' ),
					'type' => 'checkbox',
					'sort' => 13
				),
				'uninstall_save_settings' => array(
					'id'   => 'uninstall_save_settings',
					'name' => __( 'Save Settings', 'sc' ),
					'desc' => __( 'Save your settings when uninstalling this plugin. Useful when upgrading or re-installing.', 'sc' ),
					'type' => 'checkbox',
					'sort' => 14
				)
			),

			/* Keys settings */
			'keys' => array(
				'section_name' => '',
				'enable_live_key' => array(
					'id'   => 'enable_live_key',
					'name' => __( 'Test or Live Mode', 'sc' ),
					'desc' => '<p class="description">' . __( 'Toggle between using your Test or Live API keys.', 'sc' ) . '</p>',
					'type' => 'toggle_control',
					'sort' => 0
				),
				'test_secret_key' => array(
					'id'   => 'test_secret_key',
					'name' => __( 'Test Secret Key', 'sc' ),
					'desc' => __( 'Enter your test secret key, found in your Stripe account settings.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 1
				),
				'test_publish_key' => array(
					'id'   => 'test_publish_key',
					'name' => __( 'Test Publishable Key', 'sc' ),
					'desc' => __( 'Enter your test publishable key, found in your Stripe account settings.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 2
				),
				'live_secret_key' => array(
					'id'   => 'live_secret_key',
					'name' => __( 'Live Secret Key', 'sc' ),
					'desc' => __( 'Enter your live secret key, found in your Stripe account settings.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 3
				),
				'live_publish_key' => array(
					'id'   => 'live_publish_key',
					'name' => __( 'Live Publishable Key', 'sc' ),
					'desc' => __( 'Enter your live publishable key, found in your Stripe account settings.' , 'sc' ),
					'type' => 'text',
					'size' => 'regular-text',
					'sort' => 4
				)
			)
		);
