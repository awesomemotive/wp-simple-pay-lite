<?php
/**
 * Uninstall
 *
 * Runs when WP Simple Pay is deleted if "Save Settings" is unchecked in
 * "Settings > General > Advanced".
 *
 * Removes all data created by the plugin: options, transients, license update
 * caches, payment form posts (and their meta), the plugin pages, user meta
 * (report preferences and dismissed notices), the custom BerlinDB database
 * tables, scheduled events, and the cache and log files the plugin writes to
 * the uploads directory. Multisite installations are handled by cleaning up
 * each site individually.
 *
 * Nothing is removed while another WP Simple Pay edition (Lite or Pro) is
 * still installed, as both editions share the same data.
 *
 * Files uploaded by customers through the File Upload custom field are
 * intentionally kept, as they belong to the store's customers.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

// Prevent direct HTTP access. The function definitions below are side-effect
// free, so they may be safely loaded in isolation (e.g. automated tests); the
// destructive cleanup only runs when WordPress is actually uninstalling a
// plugin (see the guarded call to `simpay_uninstall()` at the bottom).
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) && ! defined( 'ABSPATH' ) ) {
	exit;
}

// Lite and Pro ship this same file (the Lite build copies it from Pro, see
// "Build Lite version" in .github/workflows/build-and-export.yml). When both
// are deleted in one request, WordPress includes each copy, so the functions
// are only declared once.
if ( ! function_exists( 'simpay_uninstall' ) ) {

	/**
	 * Returns the list of custom BerlinDB table names created by the plugin.
	 *
	 * Names are returned without the site table prefix (e.g. `wpsp_transactions`).
	 * Also listed in `SiteHealthDebugInformation::get_custom_database_tables()`.
	 *
	 * @since {next}
	 *
	 * @return string[]
	 */
	function simpay_uninstall_get_tables() {
		return array(
			'wpsp_transactions',
			'wpsp_subscriptions',
			'wpsp_notifications',
			'wpsp_coupons',
			'wpsp_webhooks',
		);
	}

	/**
	 * Determines if another WP Simple Pay edition is still installed.
	 *
	 * Lite (`stripe-checkout.php`) and Pro (`simple-pay.php`) share all of
	 * their data, so it must be kept while the other edition remains. Plugin
	 * directories are scanned directly (instead of using `get_plugins()`) so a
	 * plugin deleted earlier in the same request is not served from a cache.
	 *
	 * @since {next}
	 *
	 * @param string      $plugin_dir   Directory name of the plugin being uninstalled.
	 * @param string|null $plugins_root Optional. Plugins directory to scan.
	 *                                  Default `WP_PLUGIN_DIR`.
	 * @return bool
	 */
	function simpay_uninstall_other_edition_installed( $plugin_dir, $plugins_root = null ) {
		if ( null === $plugins_root ) {
			$plugins_root = WP_PLUGIN_DIR;
		}

		if ( ! is_dir( $plugins_root ) ) {
			return false;
		}

		$dirs = scandir( $plugins_root );

		if ( ! is_array( $dirs ) ) {
			return false;
		}

		foreach ( $dirs as $dir ) {
			if ( '.' === $dir || '..' === $dir || $dir === $plugin_dir ) {
				continue;
			}

			foreach ( array( 'simple-pay.php', 'stripe-checkout.php' ) as $main_file ) {
				$file = trailingslashit( $plugins_root ) . $dir . '/' . $main_file;

				if ( ! is_file( $file ) ) {
					continue;
				}

				$headers = get_file_data( $file, array( 'name' => 'Plugin Name' ) );

				if ( 0 === strpos( $headers['name'], 'WP Simple Pay' ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Deletes the pages created by the plugin (success, failure, cancelled).
	 *
	 * @since {next}
	 *
	 * @param array $settings Plugin settings.
	 * @return void
	 */
	function simpay_uninstall_delete_pages( $settings ) {
		$pages = array( 'success_page', 'failure_page', 'cancelled_page' );

		foreach ( $pages as $page ) {
			// Skip missing, non-numeric (e.g. arrays, "abc"), and zero/negative IDs.
			$page_id = isset( $settings[ $page ] ) && is_numeric( $settings[ $page ] )
				? (int) $settings[ $page ]
				: 0;

			if ( $page_id > 0 ) {
				wp_delete_post( $page_id, true );
			}
		}
	}

	/**
	 * Deletes the payment form posts and their post meta.
	 *
	 * Only meta belonging to `simple-pay` posts is removed. Orphaned meta left
	 * behind by other plugins is intentionally left untouched.
	 *
	 * @since {next}
	 *
	 * @return void
	 */
	function simpay_uninstall_delete_posts() {
		global $wpdb;

		// Delete Payment Form post meta (before the posts, so it can be matched).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta INNER JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.post_type = 'simple-pay'" );

		// Delete Payment Forms.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'simple-pay'" );
	}

	/**
	 * Deletes the version caches stored by the Pro license updater.
	 *
	 * The updater (`Simple_Pay_EDD_SL_Plugin_Updater`) uses the generic
	 * `edd_sl_` prefix shared with other EDD-licensed plugins, so the exact
	 * keys are computed instead of deleting by prefix. Must run before the
	 * `simpay_license_key` option is deleted.
	 *
	 * @since {next}
	 *
	 * @return void
	 */
	function simpay_uninstall_delete_license_cache() {
		// The updater also runs (and caches) before a license key is saved.
		$license_keys = array( '', get_option( 'simpay_license_key', '' ) );

		if ( defined( 'SIMPLE_PAY_LICENSE_KEY' ) ) {
			$license_keys[] = SIMPLE_PAY_LICENSE_KEY;
		}

		$license_keys = array_unique( array_map( 'strval', array_filter( $license_keys, 'is_scalar' ) ) );

		foreach ( $license_keys as $license_key ) {
			// Mirrors the updater's cache key: slug `simple-pay`, betas disabled.
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- Must match the key the updater computes with serialize().
			$hash = md5( serialize( 'simple-pay' . $license_key ) );

			delete_option( 'edd_sl_' . $hash );
			delete_option( 'edd_api_request_' . $hash );
		}

		// Failed request marker, keyed by the store URL.
		delete_option( 'edd_sl_failed_http_' . md5( 'https://wpsimplepay.com/' ) );
	}

	/**
	 * Deletes all plugin options, BerlinDB table versions, and transients.
	 *
	 * @since {next}
	 *
	 * @return void
	 */
	function simpay_uninstall_delete_options() {
		global $wpdb;

		// Plugin options (settings, license, Stripe Connect, achievements, etc.).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'simpay\_%'" );

		// BerlinDB table version options (e.g. `wpdb_wpsp_transactions_version`).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpdb\_wpsp\_%'" );

		// Transients (not matched by the `simpay_` option cleanup above).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_simpay\_%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_simpay\_%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_simpay\_%'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_timeout\_simpay\_%'" );

		// Translation language pack manifest cache (does not use the `simpay_` prefix).
		delete_site_transient( 'simplepay_language_packs' );
	}

	/**
	 * Deletes the cache and log files the plugin writes to the uploads directory.
	 *
	 * The file names mirror `SimplePay\Core\Utils\Rate_Limiting::setup_log_file()`,
	 * `SimplePay\Core\Help\HelpDocsImporter` and
	 * `SimplePay\Core\Admin\FormBuilder\AutomatorIntegrationsImporter`, which
	 * are not loaded during uninstall. The rate limiting log holds IP addresses.
	 *
	 * The rate limiting log is named after `wp_hash( home_url( '/' ) )`, so a
	 * site URL change (e.g. moving to HTTPS) or a salt change leaves the older
	 * logs behind under other hashes. Every `{32 hex}-wpsp-rate-limiting.log`
	 * in this site's own uploads directory is removed. `glob()` does not search
	 * subdirectories, so a multisite subsite's `sites/{id}/` logs are untouched.
	 *
	 * @since {next}
	 *
	 * @return void
	 */
	function simpay_uninstall_delete_files() {
		$upload_dir = wp_upload_dir( null, false );

		if ( empty( $upload_dir['basedir'] ) ) {
			return;
		}

		$basedir = trailingslashit( $upload_dir['basedir'] );
		$files   = array(
			wp_hash( home_url( '/' ) ) . '-wpsp-rate-limiting.log',
			'wpsp-docs.json',
			'wpsp-uncanny-automator-integrations.json',
		);

		$rate_limiting_logs = glob( $basedir . '*-wpsp-rate-limiting.log' );

		foreach ( is_array( $rate_limiting_logs ) ? $rate_limiting_logs : array() as $log ) {
			$log = basename( $log );

			if ( 1 === preg_match( '/^[a-f0-9]{32}-wpsp-rate-limiting\.log$/', $log ) ) {
				$files[] = $log;
			}
		}

		foreach ( array_unique( $files ) as $file ) {
			if ( is_file( $basedir . $file ) ) {
				wp_delete_file( $basedir . $file );
			}
		}
	}

	/**
	 * Deletes the per-site user meta created by the plugin.
	 *
	 * The plugin currently writes no per-site user meta: persistent dismissible
	 * notices default to `'global' => true`, so they are stored without a blog
	 * prefix and are removed by `simpay_uninstall_delete_global_user_meta()`.
	 * This removes any keys stored with the site's table prefix (e.g.
	 * `wp_simpay-form-settings-confirmation-education`), as a dismissible
	 * created with `'global' => false` would be.
	 *
	 * @since {next}
	 *
	 * @return void
	 */
	function simpay_uninstall_delete_site_user_meta() {
		global $wpdb;

		$prefix = $wpdb->esc_like( $wpdb->get_blog_prefix() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s OR meta_key LIKE %s",
				$prefix . 'simpay-%',
				$prefix . 'simpay\_%'
			)
		);
	}

	/**
	 * Deletes the network-wide user meta created by the plugin.
	 *
	 * User meta is shared by every site on a network, so this must only run once
	 * no site preserves its data. Removes:
	 *
	 * - `simpay_` report preferences (e.g. `simpay_dashboard_widget_report_range`).
	 * - `simpay-` persistent dismissibles and their `_eol` expiration keys (e.g.
	 *   `simpay-form-settings-confirmation-education_eol` or
	 *   `simpay-license-upgrade`). `Persistent_Dismissible` stores these
	 *   without a blog prefix.
	 *
	 * Notices whose IDs have no `simpay` marker (`five-star-review`,
	 * `expired-license`, `missing-license`, `stripe-api-error`,
	 * `update-available-{version}`) are dismissed permanently into
	 * `simpay_dismiss_{id}` options, never user meta, so their generic names
	 * (which other plugins sharing `Persistent_Dismissible` may use) are left
	 * alone.
	 *
	 * @since {next}
	 *
	 * @return void
	 */
	function simpay_uninstall_delete_global_user_meta() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'simpay\_%' OR meta_key LIKE 'simpay-%'" );
	}

	/**
	 * Drops the custom BerlinDB database tables created by the plugin.
	 *
	 * @since {next}
	 *
	 * @param string[]|null $tables Optional. Table names (without prefix) to drop.
	 *                              Defaults to the plugin's tables.
	 * @return void
	 */
	function simpay_uninstall_drop_tables( $tables = null ) {
		global $wpdb;

		if ( null === $tables ) {
			$tables = simpay_uninstall_get_tables();
		}

		foreach ( $tables as $table ) {
			$table_name = $wpdb->prefix . $table;

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Dropping the plugin's own tables, named from a hardcoded list, is the purpose of uninstall.
			$wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );
		}
	}

	/**
	 * Clears scheduled events created by the plugin.
	 *
	 * Removes both Action Scheduler actions (group `simpay`) and any WP-Cron
	 * events registered under a `simpay_`/`__unstable_simpay_` hook.
	 *
	 * @since {next}
	 *
	 * @return void
	 */
	function simpay_uninstall_clear_scheduled_events() {
		global $wpdb;

		// Clear Action Scheduler actions belonging to the "simpay" group. The
		// Action Scheduler tables are shared, so only rows for this group are
		// removed and the tables themselves are left intact.
		$actions_table = $wpdb->prefix . 'actionscheduler_actions';
		$groups_table  = $wpdb->prefix . 'actionscheduler_groups';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
		$has_actions_table = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $actions_table )
		);

		if ( $has_actions_table === $actions_table ) {
			// The `slug` column is not unique, so there may be more than one row.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
			$group_ids = $wpdb->get_col(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is a hardcoded, prefixed Action Scheduler table.
					"SELECT group_id FROM `{$groups_table}` WHERE slug = %s",
					'simpay'
				)
			);

			$group_ids = array_values( array_unique( array_filter( array_map( 'absint', $group_ids ) ) ) );

			if ( ! empty( $group_ids ) ) {
				$logs_table   = $wpdb->prefix . 'actionscheduler_logs';
				$placeholders = implode( ', ', array_fill( 0, count( $group_ids ), '%d' ) );

				// Remove the log entries of the actions first, so none are orphaned.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall().
				$wpdb->query(
					$wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Table names are hardcoded, prefixed Action Scheduler tables; one %d placeholder per group ID.
						"DELETE logs FROM `{$logs_table}` logs INNER JOIN `{$actions_table}` actions ON actions.action_id = logs.action_id WHERE actions.group_id IN ( {$placeholders} )",
						$group_ids
					)
				);

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Uninstall has no plugin API or caching layer; the object cache is flushed at the end of simpay_uninstall(). Table names are hardcoded, prefixed Action Scheduler tables.
				$wpdb->query(
					$wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Table name is a hardcoded, prefixed Action Scheduler table; one %d placeholder per group ID.
						"DELETE FROM `{$actions_table}` WHERE group_id IN ( {$placeholders} )",
						$group_ids
					)
				);
			}
		}

		// Clear WP-Cron events registered by the plugin.
		$crons = _get_cron_array();

		if ( ! is_array( $crons ) ) {
			return;
		}

		$hooks = array();

		foreach ( $crons as $cron ) {
			if ( ! is_array( $cron ) ) {
				continue;
			}

			foreach ( array_keys( $cron ) as $hook ) {
				if (
					0 === strpos( (string) $hook, 'simpay_' ) ||
					0 === strpos( (string) $hook, '__unstable_simpay_' )
				) {
					$hooks[ $hook ] = true;
				}
			}
		}

		// Unschedule each hook once; this removes every scheduled occurrence.
		foreach ( array_keys( $hooks ) as $hook ) {
			wp_unschedule_hook( $hook );
		}
	}

	/**
	 * Removes all WP Simple Pay data for the current site.
	 *
	 * Respects the "Save Settings" option: if enabled, no data is removed. A
	 * site without a `simpay_settings` option is left untouched too, but is
	 * reported separately so it doesn't block the network-wide cleanup.
	 *
	 * @since {next}
	 *
	 * @return string `removed` if the site's data was removed, `preserved` if
	 *                "Save Settings" kept it, or `unconfigured` if the site has
	 *                no plugin settings.
	 */
	function simpay_uninstall_site_data() {
		$settings = get_option( 'simpay_settings' );

		if ( false === $settings ) {
			return 'unconfigured';
		}

		$save_settings = isset( $settings['save_settings'] )
			? $settings['save_settings']
			: 'yes';

		// Do nothing if settings should be saved.
		if ( 'yes' === $save_settings ) {
			return 'preserved';
		}

		simpay_uninstall_delete_pages( is_array( $settings ) ? $settings : array() );
		simpay_uninstall_delete_posts();
		simpay_uninstall_delete_license_cache();
		simpay_uninstall_delete_options();
		simpay_uninstall_delete_files();
		simpay_uninstall_delete_site_user_meta();
		simpay_uninstall_drop_tables();
		simpay_uninstall_clear_scheduled_events();

		return 'removed';
	}

	/**
	 * Removes the data of every site on a network, fetching sites in batches.
	 *
	 * Batching keeps memory bounded on large networks.
	 *
	 * @since {next}
	 *
	 * @param int $batch_size Optional. Number of sites fetched per query. Values
	 *                        below 1 fall back to 100. Default 100.
	 * @return string `removed` if at least one site's data was removed and no
	 *                site preserved its data, `mixed` if some sites' data was
	 *                removed and some was preserved, `preserved` if no site's
	 *                data was removed but at least one preserved it, or
	 *                `unconfigured` if no site has plugin settings.
	 */
	function simpay_uninstall_network_data( $batch_size = 100 ) {
		$batch_size    = (int) $batch_size > 0 ? (int) $batch_size : 100;
		$any_removed   = false;
		$any_preserved = false;
		$offset        = 0;

		do {
			$site_ids = get_sites(
				array(
					'fields'                 => 'ids',
					'number'                 => $batch_size,
					'offset'                 => $offset,
					'orderby'                => 'id',
					'order'                  => 'ASC',
					'update_site_meta_cache' => false,
				)
			);

			foreach ( $site_ids as $site_id ) {
				switch_to_blog( (int) $site_id );

				$result = simpay_uninstall_site_data();

				if ( 'removed' === $result ) {
					$any_removed = true;
				} elseif ( 'preserved' === $result ) {
					$any_preserved = true;
				}

				restore_current_blog();
			}

			$offset  += $batch_size;
			$has_more = count( $site_ids ) === $batch_size;
		} while ( $has_more );

		if ( $any_removed ) {
			return $any_preserved ? 'mixed' : 'removed';
		}

		return $any_preserved ? 'preserved' : 'unconfigured';
	}

	/**
	 * Runs the uninstall routine, handling multisite installations.
	 *
	 * Nothing is removed while another WP Simple Pay edition is installed.
	 * Network-wide user meta is only removed when every configured site's data
	 * was removed. When any data was removed, the object cache is flushed
	 * afterwards, as the cleanup uses direct queries that bypass it (and, with
	 * a persistent object cache, transients are stored only in the cache).
	 *
	 * The PHP time limit is lifted where allowed, as the routine can't be
	 * resumed once the plugin files are deleted, so it should run to
	 * completion in this request.
	 *
	 * @since {next}
	 *
	 * @param string|null $plugin_dir   Optional. Directory name of the plugin
	 *                                  being uninstalled. Default the directory
	 *                                  of this file.
	 * @param string|null $plugins_root Optional. Plugins directory to check for
	 *                                  another edition. Default `WP_PLUGIN_DIR`.
	 * @return bool True if the cleanup ran, false if it was skipped because
	 *              another edition is installed.
	 */
	function simpay_uninstall( $plugin_dir = null, $plugins_root = null ) {
		if ( function_exists( 'set_time_limit' ) && false === strpos( (string) ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
			set_time_limit( 0 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Large sites need more than the default limit to delete all plugin data.
		}

		if ( null === $plugin_dir ) {
			$plugin_dir = basename( __DIR__ );
		}

		if ( simpay_uninstall_other_edition_installed( $plugin_dir, $plugins_root ) ) {
			return false;
		}

		$result = is_multisite()
			? simpay_uninstall_network_data()
			: simpay_uninstall_site_data();

		if ( 'removed' === $result ) {
			simpay_uninstall_delete_global_user_meta();
		}

		// Nothing to invalidate if every site kept (or never had) its data.
		if ( 'removed' === $result || 'mixed' === $result ) {
			wp_cache_flush();
		}

		return true;
	}
}

// WordPress defines `WP_UNINSTALL_PLUGIN` before including this file. Only its
// presence is checked: when several plugins are deleted in one request, the
// constant keeps the first plugin's basename. The directory of this copy is
// passed on, as the functions may have been declared by the other edition.
if ( defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	simpay_uninstall( basename( __DIR__ ) );
}
