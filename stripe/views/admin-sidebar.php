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

<div class="sidebar-container">
	<div class="sidebar-content">
		<p>
			<?php _e( 'Want to know when we add major features to Stripe Checkout?', 'sc' ); ?>
		</p>

		<form action="http://philderksen.us1.list-manage1.com/subscribe/post?u=7a94395392f63c258fe90f941&amp;id=e8bf1dff87" method="post" target="_blank" novalidate>
			<p>
				<input type="email" name="EMAIL" class="large-text" placeholder="Your email address">
			</p>
			<p>
				<input type="submit" value="<?php _e( 'Keep me in the Loop!', 'sc' ); ?>" name="subscribe" class="button-primary">
			</p>
		</form>
	</div>
</div>

<div class="sidebar-container">
	<div class="sidebar-content">
		<p>
			<?php _e( 'Now accepting 5-star reviews! It only takes seconds and means a lot.', 'sc' ); ?>
		</p>

		<a href="https://wordpress.org/support/view/plugin-reviews/stripe" class="button-primary" target="_blank">
			<?php _e( 'Rate this Plugin Now', 'sc' ); ?></a>
	</div>
</div>

<div class="sidebar-container">
	<div class="sidebar-content">
		<p>
			<?php _e( 'Need some help? Have a feature request?', 'sc' ); ?>
		</p>
		<p>
			<a href="https://wordpress.org/support/plugin/stripe" target="_blank">
				<?php _e( 'Visit our Community Support Forums', 'sc' ); ?></a>
		</p>
	</div>
</div>

<div class="sidebar-container-nobg">
	<div class="sidebar-content centered">
		<a href="https://stripe.com/" target="_blank">
			<img src="<?php echo SC_PLUGIN_URL; ?>assets/powered-by-stripe.png" />
		</a>
	</div>
</div>
