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

$settings = get_option( 'sc_settings' );

if( $settings['sc_settings_uninstall_save_settings'] != 1 ) {

	delete_option( 'sc_settings' );
	
	// TODO: since we have changed the settings option I think we need to
	// decide what to do when users upgrade to this version. Maybe we keep the old settings around
	// for a couple of versions until we know it is stable enough to delete them out. Or maybe we 
	// just have a notice that they will be removed if they try to revert they will have to redo them?

	// PD 4/9/2015 - Confirmed. Keep around for a while for downgrades until current versions of SC have a bigger install base.
}
