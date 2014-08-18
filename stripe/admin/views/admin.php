<?php

/**
 * Represents the view for the administration dashboard.
 *
 * @package    SC
 * @subpackage Views
 * @author     Phil Derksen <pderksen@gmail.com>, Nick Young <mycorsceb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once( 'admin-helper-functions.php' );

global $sc_options;
$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'keys';

?>

<div class="wrap">
	<?php settings_errors(); ?>
	<div id="sc-settings">
		<div id="sc-settings-content">

			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			
			<h2 class="nav-tab-wrapper">
				<?php
					
					$sc_tabs = sc_get_admin_tabs();
					
					foreach( $sc_tabs as $key => $value ) {
				?>
						<a href="<?php echo add_query_arg( 'tab', $key, remove_query_arg( 'settings-updated' )); ?>" class="nav-tab
							<?php echo $active_tab == $key ? 'nav-tab-active' : ''; ?>"><?php echo $value ?></a>
				<?php
					}
				?>
			</h2>

			<div id="tab_container">
				<form method="post" action="options.php">
					<?php
						$sc_tabs = sc_get_admin_tabs();
						
						foreach( $sc_tabs as $key => $value ) {
							if ( $active_tab == $key ) {
								settings_fields( 'sc_settings_' . $key );
								do_settings_sections( 'sc_settings_' . $key );
								
								$submit_button = get_submit_button();
								$submit_button = apply_filters( 'sc_submit_button_' . $key, $submit_button );
								
								do_action( 'sc_settings_' . $key );
								
								echo $submit_button;
							}
						}
					?>
				</form>
			</div><!-- #tab_container-->
		</div><!-- #sc-settings-content -->

		<div id="sc-settings-sidebar">
			<?php if ( class_exists( 'Stripe_Checkout_Pro' ) ): ?>
				<?php include( 'admin-sidebar-pro.php' ); ?>
			<?php else: ?>
				<?php include( 'admin-sidebar.php' ); ?>
			<?php endif; ?>
		</div>

	</div>
</div><!-- .wrap -->
