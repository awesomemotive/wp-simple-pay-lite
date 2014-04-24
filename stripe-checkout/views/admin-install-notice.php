<?php

/**
 * Show notice after plugin install/activate in admin dashboard.
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
		<?php echo $this->get_plugin_title() . __( ' is now installed.', 'sc' ); ?>
		<a href="<?php echo add_query_arg( 'page', $this->plugin_slug, admin_url( 'admin.php' ) ); ?>" class="button-primary"><?php _e( 'Get started by entering your Stripe keys', 'sc' ); ?></a>
		<a href="<?php echo add_query_arg( 'sc-dismiss-install-nag', 1 ); ?>" class="button-secondary"><?php _e( 'Hide this', 'sc' ); ?></a>
	</p>
</div>
