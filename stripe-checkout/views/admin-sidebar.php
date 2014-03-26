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
	<h3>To Do</h3>

	<div class="sidebar-content">
		To Do
	</div>
</div>

<div class="sidebar-container">
	<div class="sidebar-content">
		<p>
			<?php _e( 'Help us get noticed (and boost our egos) with a rating and short review.', 'sc' ); ?>
		</p>

		<a href="https://wordpress.org/support/view/plugin-reviews/stripe" class="btn btn-small btn-block btn-inverse" target="_blank">
			<?php _e( 'Rate this plugin on WordPress.org', 'sc' ); ?></a>
	</div>
</div>

<div class="sidebar-container">
	<div class="sidebar-content">
		<ul>
			<li>
				<i class="fui-arrow-right"></i>
				<a href="https://wordpress.org/support/plugin/stripe" target="_blank">
					<?php _e( 'Community Support Forums', 'sc' ); ?></a>
			</li>
		</ul>
	</div>
</div>
