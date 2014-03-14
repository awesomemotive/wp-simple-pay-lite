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

delete_option( 'sc_settings_general' );
delete_option( 'sc_show_admin_install_notice' );