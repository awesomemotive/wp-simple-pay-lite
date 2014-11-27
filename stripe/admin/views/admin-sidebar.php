<?php

/**
 * Sidebar portion of the administration dashboard view.
 *
 * @package    SC
 * @subpackage views
 * @author     Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
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
					<?php _e( 'Additional perks you\'ll get with Stripe Checkout Pro', 'sc' ); ?>
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
					<a href="<?php echo sc_ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout', 'sidebar_link', 'pro_upgrade' ); ?>"
					   class="button-primary button-large" target="_blank">
						<?php _e( 'Upgrade to Pro Now', 'sc' ); ?></a>
				</div>

				<!-- Black Friday 2014 Promo -->
				<div class="centered">
					<h3>Use the Black Friday discount code <span style="color:red;">BF2014</span> to get 30% off any license. Expires Friday, Dec. 5.</h3>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<h3 class="wp-ui-primary"><span><?php _e( 'Resources', 'sc' ); ?></span></h3>
		<div class="inside">
			<ul>
				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="<?php echo sc_ga_campaign_url( SC_WEBSITE_BASE_URL . 'docs/', 'stripe_checkout', 'sidebar_link', 'docs' ); ?>" target="_blank">
						<?php _e( 'Documentation', 'sc' ); ?></a>
				</li>

				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="http://wordpress.org/support/plugin/stripe" target="_blank">
						<?php _e( 'Community support forums', 'sc' ); ?></a>
				</li>

				<li>
					<div class="dashicons dashicons-arrow-right-alt2"></div>
					<a href="<?php echo sc_ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout', 'sidebar_link', 'addons' ); ?>" target="_blank">
						<?php _e( 'Stripe Checkout Pro', 'sc' ); ?></a>
				</li>
			</ul>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox">
		<div class="inside">
			<p>
				<?php _e( 'Now accepting 5-star reviews! It only takes seconds and means a lot.', 'sc' ); ?>
			</p>
			<div class="centered">
				<a href="http://wordpress.org/support/view/plugin-reviews/stripe" class="button-primary" target="_blank">
					<?php _e( 'Rate this Plugin Now', 'sc' ); ?></a>
			</div>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox-nobg">
		<div class="inside centered">
			<a href="https://stripe.com/" target="_blank">
				<img src="<?php echo SC_PLUGIN_URL; ?>assets/powered-by-stripe.png" />
			</a>
		</div>
	</div>
</div>
