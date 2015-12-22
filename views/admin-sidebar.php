<?php

/**
 * Sidebar portion of the administration dashboard view - Shared between SP Lite & Pro
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<!-- Use some built-in WP admin theme styles. -->

<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<h3 class="wp-ui-primary"><span><?php _e( 'Need More Options?', 'stripe' ); ?></span></h3>
		<div class="inside">
			<div class="main">
				<p class="last-blurb centered">
					<?php printf( __( "Additional perks you'll get with %s", 'stripe' ), Stripe_Checkout::get_pro_plugin_title() ); ?>
				</p>

				<ul>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Let customers enter an amount', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Collect data with custom fields', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Offer discounts with coupon codes', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscriptions integration', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Stripe add-ons as they\'re released', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Automatic updates & email support', 'stripe' ); ?></li>
				</ul>

				<div class="centered">
					<a href="<?php echo Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL, 'sidebar-link' ); ?>"
					   class="button-primary button-large" target="_blank">
						<?php _e( 'Upgrade to Pro Now', 'stripe' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox-nobg">
		<div class="inside centered">
			<a href="https://stripe.com/" target="_blank">
				<img src="<?php echo SC_DIR_URL; ?>assets/images/powered-by-stripe.png" />
			</a>
		</div>
	</div>
</div>
