<?php use function SimplePay\Core\Admin\Notices\Promos\bfcm_is_promo_active; ?>
<h2><?php _e( 'Want to customize your payment forms even more?', 'stripe' ); ?></h2>
<p>
	<?php _e( 'By upgrading to WP Simple Pay Pro, you get access to powerful features such as:', 'stripe' ); ?>
</p>

<!-- Repeat this bulleted list in sidebar.php & generic-tab-promo.php -->
<ul>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Unlimited Custom Fields', 'stripe' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'User-Entered Amounts', 'stripe' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Coupon Codes', 'stripe' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Embedded & Overlay Custom Forms', 'stripe' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Apple Pay & Google Pay', 'stripe' ); ?></li>
	<li><div class="dashicons dashicons-yes"></div> <?php _e( 'Stripe Subscription Support (Plus license required)', 'stripe' ); ?></li>
</ul>

<?php if ( true === bfcm_is_promo_active() ) { ?>
	<h3>Black Friday & Cyber Monday sale!</h3>
	<p>
		<?php _e( '<strong>SAVE 25%</strong> on all WP Simple Pay Pro purchases this week, including renewals and upgrades! Sale ends <em>23:59 PM December 6th CST</em>. Use code <code>BFCM2019</code> at checkout.', 'stripe' ); ?>
	</p>
<?php } ?>
