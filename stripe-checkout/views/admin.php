<?php

/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package    SC
 * @subpackage Views
 * @author     Phil Derksen <pderksen@gmail.com>, Nick Young <mycorsceb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $sc_options;
$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'keys';


?>

<div class="wrap">
	<div id="sc-settings">
		<div id="sc-settings-content">

			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo add_query_arg( 'tab', 'keys', remove_query_arg( 'settings-updated' )); ?>" class="nav-tab
					<?php echo $active_tab == 'keys' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Stripe Keys', 'sc' ); ?></a>
				<a href="<?php echo add_query_arg( 'tab', 'general', remove_query_arg( 'settings-updated' )); ?>" class="nav-tab
					<?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Default Settings', 'sc' ); ?></a>
				<a href="<?php echo add_query_arg( 'tab', 'help', remove_query_arg( 'settings-updated' )); ?>" class="nav-tab
					<?php echo $active_tab == 'help' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Shortcode Help', 'sc' ); ?></a>
			</h2>

			<div id="tab_container">

				<form method="post" action="options.php">
					<?php
					if ( $active_tab == 'keys' ) {
						settings_fields( 'sc_settings_keys' );
						do_settings_sections( 'sc_settings_keys' );
					} elseif ( $active_tab == 'general' ) {
						settings_fields( 'sc_settings_general' );
						do_settings_sections( 'sc_settings_general' );
					} elseif ( $active_tab == 'help' ) {
						include_once( 'admin-help.php' );
					} else {
						// Do nothing
					}

					submit_button();
					?>
				</form>
			</div><!-- #tab_container-->
		</div><!-- #sc-settings-content -->

		<div id="sc-settings-sidebar">
			<?php include( 'admin-sidebar.php' ); ?>
		</div>

	</div>
</div><!-- .wrap -->
