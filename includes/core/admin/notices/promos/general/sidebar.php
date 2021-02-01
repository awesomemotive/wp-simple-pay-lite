<?php
/**
 * Promos: General
 *
 * @package SimplePay\Core\Admin\Notices\Promos
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.5
 */

?>

<div class="sidebar-container metabox-holder">
	<div class="postbox" style="margin-bottom: 0;">
		<h3 class="wp-ui-primary"><span><?php _e( 'Upgrade your Payment Forms', 'stripe' ); ?></span></h3>
		<div class="inside">
			<div class="main">
				<p class="sidebar-heading centered">
					<?php _e( 'Additional features included in<br />WP Simple Pay Pro', 'stripe' ); ?>
				</p>

				<!-- Repeat this bulleted list in sidebar.php & generic-tab-promo.php -->
				<ul>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Unlimited custom fields', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'User-entered amounts', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Coupon code support', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'On-site checkout (no redirect)', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Embedded & overlay forms', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'ACH and iDEAL payments', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Stripe Subscription support', 'stripe' ); ?>*</li>
				</ul>

				<div class="centered">
					<a
						href="<?php echo esc_url( simpay_pro_upgrade_url( 'sidebar-link' ) ); ?>"
						class="button simpay-upgrade-btn simpay-upgrade-btn-large"
						target="_blank"
					>
						<?php _e( 'Click here to Upgrade', 'stripe' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="sidebar-container metabox-holder">
	<div class="postbox-nobg" style="padding-bottom: 20px;">
		<div class="inside">
			<p>
				*<?php _e( 'Plus or higher license required', 'stripe' ); ?>
			</p>
			<p>
				<a href="<?php echo simpay_ga_url( simpay_get_url( 'docs' ), 'sidebar-link' ); ?>"
				   target="_blank"><?php echo SIMPLE_PAY_PLUGIN_NAME; ?> <?php _e( 'Docs', 'stripe' ); ?></a>
				<br />
				<a href="https://dashboard.stripe.com/" target="_blank">
					<?php _e( 'Your Stripe Dashboard', 'stripe' ); ?></a>
			</p>
			<p>&nbsp;</p>
			<p class="centered">
				<a href="https://stripe.com/" target="_blank">
					<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/powered_by_stripe.png' ); ?>" />
				</a>
			</p>
		</div>
	</div>
</div>
