<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// If uninstall, not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'sc_settings_master' );
delete_option( 'sc_settings_default' );
delete_option( 'sc_settings_keys' );
delete_option( 'sc_show_admin_install_notice' );
delete_option( 'sc_has_run' );