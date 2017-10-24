<div class="sidebar-container metabox-holder">
	<div class="postbox" style="margin-bottom: 0;">
		<h3 class="wp-ui-primary"><span><?php _e( 'Upgrade your Payment Forms', 'stripe' ); ?></span></h3>
		<div class="inside">
			<div class="main">
				<p class="sidebar-heading centered">
					<?php _e( "Additional features included in WP Simple Pay Pro", 'stripe' ); ?>
				</p>

				<ul>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Custom Fields', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'User-Entered Amounts', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Coupon Codes', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Tax Rate Support', 'stripe' ); ?></li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Stripe Subscriptions', 'stripe' ); ?>*</li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscription Installment Plans', 'stripe' ); ?>*</li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscription Setup Fees', 'stripe' ); ?>*</li>
					<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Subscription Trial Periods', 'stripe' ); ?>*</li>
				</ul>

				<div class="centered">
					<a href="<?php echo simpay_pro_upgrade_url( 'sidebar-link' ); ?>"
					   class="simpay-upgrade-btn simpay-upgrade-btn-large" target="_blank">
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
				*<?php _e( 'Business license required', 'stripe' ); ?>
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
					<img src="<?php echo SIMPLE_PAY_ASSETS; ?>images/powered_by_stripe.png" />
				</a>
			</p>
		</div>
	</div>
</div>
