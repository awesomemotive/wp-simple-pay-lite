<?php

/**
 * Sidebar portion of the administration dashboard view.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Use some built-in WP admin theme styles. -->

<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<h3 class="wp-ui-primary"><span><?php _e( 'Need More Options?', 'sc' ); ?></span></h3>
		<div class="inside">
			<div class="main">
				<p class="last-blurb centered">
					<?php printf( __( "Additional perks you'll get with %s", 'sc' ), Stripe_Checkout::get_pro_plugin_title() ); ?>
				</p>

				<ul>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Let customers enter an amount', 'sc' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Collect data with custom fields', 'sc' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Offer discounts with coupon codes', 'sc' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscriptions integration', 'sc' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Stripe add-ons as they\'re released', 'sc' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Automatic updates & email support', 'sc' ); ?></li>
				</ul>

				<div class="centered">
					<a href="<?php echo Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe-checkout', 'sidebar-link', 'pro-upgrade' ); ?>"
					   class="button-primary button-large" target="_blank">
						<?php _e( 'Upgrade to Pro Now', 'sc' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<div class="inside">
			<p>
				<?php _e( 'Your review helps more folks find our plugin. Thanks so much!', 'sc' ); ?>
			</p>
			<div class="centered">
				<a href="https://wordpress.org/support/view/plugin-reviews/stripe#postform" class="button-primary" target="_blank">
					<?php _e( 'Review this Plugin Now', 'sc' ); ?></a>
			</div>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<div class="inside">
			<ul>
				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="<?php echo Stripe_Checkout_Admin::ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/', 'stripe-checkout', 'sidebar-link', 'docs' ); ?>" target="_blank">
						<?php _e( 'Documentation', 'sc' ); ?></a>
				</li>

				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="https://wordpress.org/support/plugin/stripe" target="_blank">
						<?php _e( 'Community support forums', 'sc' ); ?></a>
				</li>

				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="https://dashboard.stripe.com/" target="_blank">
						<?php _e( 'Stripe Dashboard', 'sc' ); ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox-nobg">
		<div class="inside centered">
			<a href="https://stripe.com/" target="_blank">
				<img src="<?php echo SC_DIR_URL; ?>assets/img/powered-by-stripe.png" />
			</a>
		</div>
	</div>
</div>
