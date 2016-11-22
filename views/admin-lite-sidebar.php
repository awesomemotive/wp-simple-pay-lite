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
		<h3 class="wp-ui-primary"><span><?php _e( 'Looking for more?', 'stripe' ); ?></span></h3>
		<div class="inside">
			<div class="main">
				<p class="sidebar-heading centered">
					<?php printf( __( "Additional features included with %s", 'stripe' ), Stripe_Checkout::get_pro_plugin_title() ); ?>
				</p>

				<ul>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Custom Fields', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Coupon Codes', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'User-Entered Amounts', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Easy Pricing Tables Integration', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Email Support', 'stripe' ); ?></li>
				</ul>

				<p class="centered">
					<em><?php _e( 'Included in Business & Elite Licenses:', 'stripe' ); ?></em>
				</p>

				<ul>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Stripe Subscriptions', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscription Installment Plans', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscription Setup Fees', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscription Trial Periods', 'stripe' ); ?></li>
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
	<div class="postbox-nobg" style="padding-bottom: 20px;">
		<div class="inside centered">
			<p>
				<a href="<?php echo Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/', 'sidebar-link' ); ?>"
				   class="button-primary" target="_blank"><?php _e( 'Help & Documenation', 'stripe' ); ?></a>
			</p>
			<p>
				<a href="https://dashboard.stripe.com/" target="_blank">
					<?php _e( 'Your Stripe Dashboard', 'stripe' ); ?></a>
			</p>
			<p>&nbsp;</p>
			<p>
				<a href="https://stripe.com/" target="_blank">
					<img src="<?php echo SC_DIR_URL; ?>assets/images/powered_by_stripe.png" />
				</a>
			</p>
		</div>
	</div>
</div>
