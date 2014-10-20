<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sc_upgrade_link()
{
    $page_hook = add_submenu_page( 
				SC_PLUGIN_SLUG, 
				__( 'Upgrade to Pro', 'sc' ), 
				__( 'Upgrade to Pro', 'sc' ), 
				'manage_options', 
				SC_PLUGIN_SLUG . '-upgrade', 
				'sc_upgrade_redirect'
			);
	
    add_action( 'load-' . $page_hook , 'sc_upgrade_ob_start' );
}
add_action( 'admin_menu', 'sc_upgrade_link' );

function sc_upgrade_ob_start() {
    ob_start();
}

function sc_upgrade_redirect()
{
    wp_redirect( sc_ga_campaign_url( SC_WEBSITE_BASE_URL, 'stripe_checkout', 'plugin_menu', 'pro_upgrade' ), 301 );
    exit();
}

function sc_upgrade_link_js()
{
    ?>
    <script type="text/javascript">
    	jQuery(document).ready(function ($) {
            $('a[href="admin.php?page=stripe-checkout-upgrade"]').on('click', function () {
        		$(this).attr('target', '_blank');
            });
        });
    </script>
    <?php 
}
add_action( 'admin_footer', 'sc_upgrade_link_js' );
