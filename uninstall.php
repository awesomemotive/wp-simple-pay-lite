<?php
/**
 * Uninstall
 *
 * Runs when WP Simple Pay is deleted if "Save Settings" is unchecked in
 * "Settings > General > Advanced"
 *
 * @package SimplePay
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since unknown
 */

// Exit if not uninstalling from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Do nothing if settings should be saved.
$settings      = get_option( 'simpay_settings' );
$save_settings = isset( $settings[ 'save_settings' ] )
	? $settings['save_settings']
	: 'yes';

if ( 'yes' === $save_settings ) {
	return;
}

global $wpdb;

// Delete pages.
$success_page = isset( $settings['success_page'] )
	? $settings['success_page']
	: '';

$failure_page = isset( $settings['failure_page'] )
	? $settings['failure_page']
	: '';

$cancelled_page = isset( $settings['cancelled_page'] )
	? $settings['cancelled_page']
	: '';

wp_delete_post( $success_page, true );
wp_delete_post( $failure_page, true );
wp_delete_post( $cancelled_page, true );

// Delete options.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'simpay\_%'" );

// Delete Payment Forms.
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'simple-pay'" );
$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );
