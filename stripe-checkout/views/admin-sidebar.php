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
			<?php _e( 'Be the first to know when major new features are released.', 'sc' ); ?>
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
			<?php _e( 'Help us get noticed (and boost our egos) with a rating and short review.', 'sc' ); ?>
		</p>

		<a href="https://wordpress.org/support/view/plugin-reviews/stripe" class="button-primary" target="_blank">
			<?php _e( 'Rate this plugin on WordPress.org', 'sc' ); ?></a>
	</div>
</div>

<div class="sidebar-container">
	<div class="sidebar-content">
		<p>
			<?php _e( 'Have a feature request or need help from others?', 'sc' ); ?>
		</p>
		<p>
			<a href="https://wordpress.org/support/plugin/stripe" target="_blank">
				<?php _e( 'Visit our Community Support Forums', 'sc' ); ?></a>
		</p>
	</div>
</div>
