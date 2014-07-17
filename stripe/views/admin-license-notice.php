<?php

/**
 * Show notice for license keys
 *
 * @package    SC
 * @subpackage Views
 * @author     Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<style>
	#sc-install-notice .button-primary,
	#sc-install-notice .button-secondary {
		margin-left: 15px;
	}
</style>

<div id="sc-install-notice" class="updated">
	<p>
		<?php _e( 'You have one or more Stripe Checkout add-ons that require a valid license key.', 'sc' ); ?>
		<a href="<?php echo add_query_arg( array( 'page' => $this->plugin_slug, 'tab' => 'licenses' ), admin_url( 'admin.php' ) ); ?>" class="button-primary">
			<?php _e( 'Enter license keys now', 'sc' ); ?>
		</a>
	</p>
</div>
