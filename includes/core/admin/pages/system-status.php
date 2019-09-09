<?php

namespace SimplePay\Core\Admin\Pages;

use SimplePay\Core\Abstracts\Admin_Page;
/**
 * System Status class
 *
 * Generates reports on current installation, for debugging or support purposes.
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * System Status.
 */
class System_Status extends Admin_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

	}

	public function add_fields() {
		return array();
	}

	public function add_sections() {
		return array();
	}

	/**
	 * Stripe TLS requirement.
	 * https://support.stripe.com/questions/how-do-i-upgrade-my-stripe-integration-from-tls-1-0-to-tls-1-2
	 */
	private static function stripe_tls_check( $for_export ) {


		$test_key = simpay_get_setting( 'secret_key' );

		// If test key isn't set...
		if ( empty( $test_key ) ) {

			$retval = __( 'Cannot test TLS 1.2 support until your Stripe Test Secret Key is entered.', 'stripe' );

			if ( $for_export ) {
				return $retval;
			} else {
				return '<mark class="error">' . $retval . '</mark>';
			}
		}

		// Set Stripe API key. Force test key.
		\Stripe\Stripe::setApiKey( $test_key );

		try {
			\Stripe\Charge::all();

			$retval = __( 'TLS 1.2 is supported. No action required.', 'stripe' );

			if ( $for_export ) {
				return $retval;
			} else {
				return '<mark class="ok">' . $retval . '</mark>';
			}
		} catch ( \Stripe\Error\ApiConnection $e ) {

			$retval = __( 'TLS 1.2 is not supported. You will need to upgrade your integration.', 'stripe' );

			if ( $for_export ) {
				return $retval . ' ' . sprintf( __( 'See %1$s.', 'stripe' ), 'https://stripe.com/blog/upgrading-tls' );
			} else {
				return '<mark class="error">' . $retval . ' ' . sprintf( __( '<a href="%s" target="_blank" rel="noopener noreferrer">Please read this</a> for more information.', 'stripe' ), 'https://support.stripe.com/questions/how-do-i-upgrade-my-stripe-integration-from-tls-1-0-to-tls-1-2' ) . '</mark>';
			}
		}
	}

	/**
	 * HTML output
	 */
	public static function html() {
		?>

		<div class="wrap">
			<div id="simpay-settings">
				<div id="simpay-settings-content">

					<h1><?php _e( 'System Report', 'stripe' ); ?></h1>

					<div id="simpay-system-status-report">
						<p>
							<?php _e( 'Please copy and paste this information when contacting support:', 'stripe' ); ?>
						</p>

						<textarea readonly="readonly" onclick="this.select();"></textarea>

						<p>
							<?php _e( 'You can also download your information as a text file to attach, or simply view it below.', 'stripe' ); ?>
						</p>
						<p>
							<a href="#" id="simpay-system-status-report-download"
							   class="button button-primary"><?php _e( 'Download System Report', 'stripe' ); ?></a>
						</p>
					</div>
					<hr>
					<?php

					global $wpdb, $wp_version;

					$sections = array();
					$panels   = array(
						'wordpress' => array(
							'label'  => __( 'WordPress Installation', 'stripe' ),
							'export' => 'WordPress Installation',
						),
						'simpay'    => array(
							'label'  => sprintf( __( '%s Settings', 'stripe' ), SIMPLE_PAY_PLUGIN_NAME ),
							'export' => SIMPLE_PAY_PLUGIN_NAME . ' Settings',
						),
						'theme'     => array(
							'label'  => __( 'Active Theme', 'stripe' ),
							'export' => 'Active Theme',
						),
						'plugins'   => array(
							'label'  => __( 'Active Plugins', 'stripe' ),
							'export' => 'Active Plugins',
						),
						'server'    => array(
							'label'  => __( 'Server Environment', 'stripe' ),
							'export' => 'Server Environment',
						),
						'client'    => array(
							'label'  => __( 'Client Information', 'stripe' ),
							'export' => 'Client Information',
						),
					);

					$sections['simpay'] = array();

					// Add "Lite" to plugin name if not Pro.
					$plugin_name = SIMPLE_PAY_PLUGIN_NAME;
					if ( ! class_exists( 'SimplePay\Pro\SimplePayPro' ) ) {
						$plugin_name .= ' Lite';
					}

					// Show version from base class.
					$sections['simpay']['version'] = array(
						'label'        => sprintf( __( '%s Version', 'stripe' ), $plugin_name ),
						'label_export' => $plugin_name . ' Version',
						'result'       => SIMPLE_PAY_VERSION,
					);

					// Show Stripe TLS check.
					$sections['simpay']['stripe_tls'] = array(
						'label'         => __( 'Stripe TLS', 'stripe' ),
						'label_export'  => 'Stripe TLS',
						'result'        => self::stripe_tls_check( false ),
						'result_export' => self::stripe_tls_check( true ),
					);

					// Show friendlier Stripe Test/Live mode.
					$test_or_live_mode      = __( 'Test Mode', 'stripe' );
					$test_or_live_mode_html = '<mark class="warning">' . $test_or_live_mode . '</mark>';

					if ( ! simpay_is_test_mode() ) {
						$test_or_live_mode      = __( 'Live Mode', 'stripe' );
						$test_or_live_mode_html = '<mark class="ok">' . $test_or_live_mode . '</mark>';
					}

					$sections['simpay']['test_or_live_mode'] = array(
						'label'         => __( 'Simple Pay Test or Live Mode', 'stripe' ),
						'label_export'  => 'Simple Pay Test or Live Mode',
						'result'        => $test_or_live_mode_html,
						'result_export' => $test_or_live_mode,
					);

					/**
					 * WordPress Installation
					 * ======================
					 */
					$debug_mode = $script_debug = __( 'No', 'stripe' );
					if ( defined( 'WP_DEBUG' ) ) {
						$debug_mode = ( true === WP_DEBUG ? __( 'Yes', 'stripe' ) : $debug_mode );
					}
					if ( defined( 'SCRIPT_DEBUG' ) ) {
						$script_debug = ( true === SCRIPT_DEBUG ? __( 'Yes', 'stripe' ) : $script_debug );
					}

					$memory        = self::let_to_num( WP_MEMORY_LIMIT );
					$memory_export = size_format( $memory );

					if ( $memory < 41943040 ) {
						$memory = '<mark class="error">' . sprintf( __( '%1$s - It is recommendend to set memory to at least 40MB. See: <a href="%2$s" target="_blank" rel="noopener noreferrer">Increasing memory allocated to PHP</a>', 'stripe' ), $memory_export, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
					} else {
						$memory = '<mark class="ok">' . $memory_export . '</mark>';
					}

					$permalinks = get_option( 'permalink_structure' );
					$permalinks = empty( $permalinks ) ? '/?' : $permalinks;

					$is_multisite = is_multisite();

					$sections['wordpress'] = array(
						'name'         => array(
							'label'        => __( 'Site Name', 'stripe' ),
							'label_export' => 'Site Name',
							'result'       => get_bloginfo( 'name' ),
						),
						'home_url'     => array(
							'label'        => __( 'Home URL', 'stripe' ),
							'label_export' => 'Home URL',
							'result'       => home_url(),
						),
						'site_url'     => array(
							'label'        => __( 'Site URL', 'stripe' ),
							'label_export' => 'Site URL',
							'result'       => site_url(),
						),
						// SSL check.
						'ssl_enabled'  => array(
							'label'         => __( 'SSL/HTTPS Enabled', 'stripe' ),
							'label_export'  => 'SSL/HTTPS Enabled',
							'result'        => is_ssl() ? '<mark class="ok">' . __( 'Yes', 'stripe' ) . '</mark>' : '<mark class="error">' . __( 'No. SSL is required by Stripe for Live mode.', 'stripe' ) . '</mark>',
							'result_export' => is_ssl() ? 'Yes' : 'No',
						),
						'version'      => array(
							'label'        => __( 'Version', 'stripe' ),
							'label_export' => 'Version',
							'result'       => $wp_version,
						),
						'locale'       => array(
							'label'        => __( 'Locale', 'stripe' ),
							'label_export' => 'Locale',
							'result'       => get_locale(),
						),
						'multisite'    => array(
							'label'         => __( 'Multisite', 'stripe' ),
							'label_export'  => 'Multisite',
							'result'        => $is_multisite ? __( 'Yes', 'stripe' ) : __( 'No', 'stripe' ),
							'result_export' => $is_multisite ? 'Yes' : 'No',
						),
						'permalink'    => array(
							'label'         => __( 'Permalinks', 'stripe' ),
							'label_export'  => 'Permalinks',
							'result'        => '<code>' . $permalinks . '</code>',
							'result_export' => $permalinks,
						),
						'memory_limit' => array(
							'label'         => __( 'WP Memory Limit', 'stripe' ),
							'label_export'  => 'WP Memory Limit',
							'result'        => $memory,
							'result_export' => $memory_export,
						),
						'debug_mode'   => array(
							'label'        => __( 'WP Debug Mode', 'stripe' ),
							'label_export' => 'WP Debug Mode',
							'result'       => $debug_mode,
						),
						'script_debug' => array(
							'label'        => __( 'Script Debug', 'stripe' ),
							'label_export' => 'Script Debug',
							'result'       => $script_debug,
						),
					);

					/**
					 * Active Theme
					 * ============
					 */
					include_once( ABSPATH . 'wp-admin/includes/theme-install.php' );

					if ( version_compare( $wp_version, '3.4', '<' ) ) {
						$active_theme  = get_theme_data( get_stylesheet_directory() . '/style.css' );
						$theme_name    = '<a href="' . $active_theme['URI'] . '" target="_blank" rel="noopener noreferrer">' . $active_theme['Name'] . '</a>';
						$theme_version = $active_theme['Version'];
						$theme_author  = '<a href="' . $active_theme['AuthorURI'] . '" target="_blank" rel="noopener noreferrer">' . $active_theme['Author'] . '</a>';
						$theme_export  = $active_theme['Name'] . ' - ' . $theme_version;
					} else {
						$active_theme  = wp_get_theme();
						$theme_name    = '<a href="' . $active_theme->ThemeURI . '" target="_blank" rel="noopener noreferrer">' . $active_theme->Name . '</a>';
						$theme_version = $active_theme->Version;
						$theme_author  = $active_theme->Author;
						$theme_export  = $active_theme->Name . ' - ' . $theme_version;
					}

					$theme_update_version = $theme_version;

					$api = themes_api( 'theme_information', array(
						'slug'   => get_template(),
						'fields' => array( 'sections' => false, 'tags' => false ),
					) );

					if ( $api && ! is_wp_error( $api ) ) {
						$theme_update_version = $api->version;
					}

					if ( version_compare( $theme_version, $theme_update_version, '<' ) ) {
						$theme_version = '<mark class="error">' . $theme_version . ' (' . sprintf( __( '%s is available', 'stripe' ), esc_html( $theme_update_version ) ) . '</mark>';
					} else {
						$theme_version = '<mark class="ok">' . $theme_version . '</mark>';
					}

					$theme = '<dl>';
					$theme .= '<dt>' . __( 'Name', 'stripe' ) . '</dt>';
					$theme .= '<dd>' . $theme_name . '</dd>';
					$theme .= '<dt>' . __( 'Author', 'stripe' ) . '</dt>';
					$theme .= '<dd>' . $theme_author . '</dd>';
					$theme .= '<dt>' . __( 'Version', 'stripe' ) . '</dt>';
					$theme .= '<dd>' . $theme_version . '</dd>';
					$theme .= '</dl>';

					$is_child_theme = is_child_theme();
					$parent_theme   = $parent_theme_export = '-';

					if ( $is_child_theme ) {
						if ( version_compare( $wp_version, '3.4', '<' ) ) {

							$parent_theme = $parent_theme_export = $active_theme['Template'];

						} else {

							$parent       = wp_get_theme( $active_theme->Template );
							$parent_theme = '<dl>';
							$parent_theme .= '<dt>' . __( 'Name', 'stripe' ) . '</dt>';
							$parent_theme .= '<dd>' . $parent->Name . '</dd>';
							$parent_theme .= '<dt>' . __( 'Author', 'stripe' ) . '</dt>';
							$parent_theme .= '<dd>' . $parent->Author . '</dd>';
							$parent_theme .= '<dt>' . __( 'Version', 'stripe' ) . '</dt>';
							$parent_theme .= '<dd>' . $parent->Version . '</dd>';
							$parent_theme .= '</dl>';

							$parent_theme_export = strip_tags( $parent->Name ) . ' - ' . $parent->Version;
						}
					}

					$sections['theme'] = array(
						'theme'        => array(
							'label'         => __( 'Theme Information', 'stripe' ),
							'label_export'  => 'Theme',
							'result'        => $theme,
							'result_export' => $theme_export,
						),
						'theme_child'  => array(
							'label'         => __( 'Child Theme', 'stripe' ),
							'label_export'  => 'Child Theme',
							'result'        => $is_child_theme ? __( 'Yes', 'stripe' ) : __( 'No', 'stripe' ),
							'result_export' => $is_child_theme ? 'Yes' : 'No',
						),
						'theme_parent' => array(
							'label'         => __( 'Parent Theme', 'stripe' ),
							'label_export'  => 'Parent Theme',
							'result'        => $parent_theme,
							'result_export' => $parent_theme_export,
						),
					);

					/**
					 * Active Plugins
					 * ==============
					 */
					include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

					$active_plugins = (array) get_option( 'active_plugins', array() );
					if ( is_multisite() ) {
						$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
					}

					foreach ( $active_plugins as $plugin ) {

						$plugin_data = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );

						if ( ! empty( $plugin_data['Name'] ) ) {

							$plugin_name    = $plugin_data['Title'];
							$plugin_author  = $plugin_data['Author'];
							$plugin_version = $plugin_update_version = $plugin_data['Version'];

							// Afraid that querying many plugins may risk a timeout.
							if ( count( $active_plugins ) <= 10 ) {
								$api = plugins_api( 'plugin_information', array(
									'slug'   => $plugin_data['Name'],
									'fields' => array(
										'version' => true,
									),
								) );
								if ( $api && ! is_wp_error( $api ) ) {
									if ( ! empty ( $api->version ) ) {
										$plugin_update_version = $api->version;
										if ( version_compare( $plugin_version, $plugin_update_version, '<' ) ) {
											$plugin_version = '<mark class="error">' . $plugin_version . ' (' . sprintf( __( '%s is available', 'stripe' ), esc_html( $plugin_update_version ) ) . '</mark>';
										} else {
											$plugin_version = '<mark class="ok">' . $plugin_version . '</mark>';
										}
									}
								}
							}

							$plugin = '<dl>';
							$plugin .= '<dt>' . __( 'Author', 'stripe' ) . '</dt>';
							$plugin .= '<dd>' . $plugin_author . '</dd>';
							$plugin .= '<dt>' . __( 'Version', 'stripe' ) . '</dt>';
							$plugin .= '<dd>' . $plugin_version . '</dd>';
							$plugin .= '</dl>';

							$sections['plugins'][ sanitize_key( strip_tags( $plugin_name ) ) ] = array(
								'label'         => $plugin_name,
								'label_export'  => strip_tags( $plugin_data['Title'] ),
								'result'        => $plugin,
								'result_export' => $plugin_data['Version'],
							);
						}
					}

					if ( isset( $sections['plugins'] ) ) {
						rsort( $sections['plugins'] );
					}

					/**
					 * Server Environment
					 * ==================
					 */
					if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
						$php = '<mark>' . PHP_VERSION . ' - ' . __( 'WordPress.org recommends upgrading to PHP 7 or higher for better security.', 'stripe' ) . ' <a href="https://wordpress.org/about/requirements/" target="_blank" rel="noopener noreferrer">' . __( 'Read more.', 'stripe' ) . '</a>' . '</mark>';
					} else {
						$php = '<mark class="ok">' . PHP_VERSION . '</mark>';
					}

					if ( $wpdb->use_mysqli ) {
						$mysql = $mysql_export = @mysqli_get_server_info( $wpdb->dbh );
					} else {
						$mysql_export = __( 'Cannot connect to MySQL database.', 'stripe' );
						$mysql        = '<mark class="error">' . $mysql_export . '</mark>';
					}

					$host = $_SERVER['SERVER_SOFTWARE'];
					if ( defined( 'WPE_APIKEY' ) ) {
						$host .= ' (WP Engine)';
					} elseif ( defined( 'PAGELYBIN' ) ) {
						$host .= ' (Pagely)';
					}

					$default_timezone = $server_timezone_export = date_default_timezone_get();
					if ( 'UTC' !== $default_timezone ) {
						$server_timezone = '<mark class="error">' . sprintf( __( 'Server default timezone is %s - it should be UTC', 'stripe' ), $default_timezone ) . '</mark>';
					} else {
						$server_timezone = '<mark class="ok">UTC</mark>';
					}

					// WP Remote POST test.
					$response = wp_safe_remote_post( 'https://www.paypal.com/cgi-bin/webscr', array(
						'timeout' => 60,
						'body'    => array(
							'cmd' => '_notify-validate',
						),
					) );
					if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
						$wp_post_export = 'Yes';
						$wp_post        = '<mark class="ok">' . __( 'Yes', 'stripe' ) . '</mark>';
					} else {
						$wp_post_export = 'No';
						$wp_post        = '<mark class="error">' . __( 'No', 'stripe' );
						if ( is_wp_error( $response ) ) {
							$error          = ' (' . $response->get_error_message() . ')';
							$wp_post        .= $error;
							$wp_post_export .= $error;
						} else {
							$error          = ' (' . $response['response']['code'] . ')';
							$wp_post        .= $error;
							$wp_post_export .= $error;
						}
						$wp_post .= '</mark>';
					}

					// WP Remote GET test.
					$response = wp_safe_remote_get( get_home_url( '/?p=1' ) );
					if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
						$wp_get_export = 'Yes';
						$wp_get        = '<mark class="ok">' . __( 'Yes', 'stripe' ) . '</mark>';
					} else {
						$wp_get_export = 'No';
						$wp_get        = '<mark class="error">' . __( 'No', 'stripe' );
						if ( is_wp_error( $response ) ) {
							$error         = ' (' . $response->get_error_message() . ')';
							$wp_get        .= $error;
							$wp_get_export .= $error;
						} else {
							$error         = ' (' . $response['response']['code'] . ')';
							$wp_get        .= $error;
							$wp_get_export .= $error;
						}
						$wp_get .= '</mark>';
					}

					$php_memory_limit        = ini_get( 'memory_limit' );
					$php_max_upload_filesize = ini_get( 'upload_max_filesize' );
					$php_post_max_size       = ini_get( 'post_max_size' );
					$php_max_execution_time  = ini_get( 'max_execution_time' );
					$php_max_input_vars      = ini_get( 'max_input_vars' );

					$curl_version = '';
					if ( function_exists( 'curl_version' ) ) {
						$curl_version = curl_version();
						$curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
					}

					$sections['server'] = array(
						'host'                => array(
							'label'        => __( 'Web Server', 'stripe' ),
							'label_export' => 'Web Server',
							'result'       => $host,
						),
						'php_version'         => array(
							'label'         => __( 'PHP Version', 'stripe' ),
							'label_export'  => 'PHP Version',
							'result'        => $php,
							'result_export' => PHP_VERSION,
						),
						'mysql_version'       => array(
							'label'         => __( 'MySQL Version', 'stripe' ),
							'label_export'  => 'MySQL Version',
							'result'        => version_compare( $mysql, '5.5', '>' ) ? '<mark class="ok">' . $mysql . '</mark>' : $mysql,
							'result_export' => $mysql_export,
						),
						'server_timezone'     => array(
							'label'         => __( 'Server Timezone', 'stripe' ),
							'label_export'  => 'Server Timezone',
							'result'        => $server_timezone,
							'result_export' => $server_timezone_export,
						),
						'display_errors'      => array(
							'label'         => __( 'Display Errors', 'stripe' ),
							'label_export'  => 'Display Errors',
							'result'        => ( ini_get( 'display_errors' ) ) ? __( 'Yes', 'stripe' ) . ' (' . ini_get( 'display_errors' ) . ')' : '-',
							'result_export' => ( ini_get( 'display_errors' ) ) ? 'Yes' : 'No',
						),
						'php_memory_limit'    => array(
							'label'        => __( 'Server PHP Memory Limit', 'stripe' ),
							'label_export' => 'Server PHP Memory Limit',
							'result'       => $php_memory_limit ? $php_memory_limit : '-',
						),
						'upload_max_filesize' => array(
							'label'        => __( 'Upload Max Filesize', 'stripe' ),
							'label_export' => 'Upload Max Filesize',
							'result'       => $php_max_upload_filesize ? $php_max_upload_filesize : '-',
						),
						'post_max_size'       => array(
							'label'        => __( 'Post Max Size', 'stripe' ),
							'label_export' => 'Post Max Size',
							'result'       => $php_post_max_size ? $php_post_max_size : '-',
						),
						'max_execution_time'  => array(
							'label'        => __( 'Max Execution Time', 'stripe' ),
							'label_export' => 'Max Execution Time',
							'result'       => $php_max_execution_time ? $php_max_execution_time : '-',
						),
						'max_input_vars'      => array(
							'label'        => __( 'Max Input Vars', 'stripe' ),
							'label_export' => 'Max Input Vars',
							'result'       => $php_max_input_vars ? $php_max_input_vars : '-',
						),
						'curl_init'           => array(
							'label'         => __( 'cURL Enabled', 'stripe' ),
							'result'        => function_exists( 'curl_init' ) ? '<mark class="ok">' . __( 'Yes', 'stripe' ) . '</mark>' : '<mark class="error">' . __( 'No', 'stripe' ) . '</mark>',
							'result_export' => function_exists( 'curl_init' ) ? 'Yes' : 'No',
						),
						'curl_version'         => array(
							'label'         => __( 'cURL Version', 'stripe' ),
							'result'        => $curl_version,
							'result_export' => $curl_version,
						),
						'mbstring'             => array(
							'label'         => 'mbstring (' . __( 'Multibyte String', 'stripe' ) . ') ' . __( 'Enabled', 'stripe' ),
							'result'        => extension_loaded( 'mbstring' ) ? '<mark class="ok">' . __( 'Yes', 'stripe' ) . '</mark>' : '<mark class="error">' . __( 'No', 'stripe' ) . '</mark>',
							'result_export' => extension_loaded( 'mbstring' ) ? 'Yes' : 'No',
						),
						'fsockopen'           => array(
							'label'         => 'fsockopen',
							'result'        => function_exists( 'fsockopen' ) ? __( 'Yes', 'stripe' ) : __( 'No', 'stripe' ),
							'result_export' => function_exists( 'fsockopen' ) ? 'Yes' : 'No',
						),
						'soap'                => array(
							'label'         => 'SOAP',
							'result'        => class_exists( 'SoapClient' ) ? __( 'Yes', 'stripe' ) : __( 'No', 'stripe' ),
							'result_export' => class_exists( 'SoapClient' ) ? 'Yes' : 'No',
						),
						'suhosin'             => array(
							'label'         => 'SUHOSIN',
							'result'        => extension_loaded( 'suhosin' ) ? __( 'Yes', 'stripe' ) : __( 'No', 'stripe' ),
							'result_export' => extension_loaded( 'suhosin' ) ? 'Yes' : 'No',
						),
						'wp_remote_post'      => array(
							'label'         => __( 'WP Remote POST', 'stripe' ),
							'label_export'  => 'WP Remote POST',
							'result'        => $wp_post,
							'result_export' => $wp_post_export,
						),
						'wp_remote_get'       => array(
							'label'         => __( 'WP Remote GET', 'stripe' ),
							'label_export'  => 'WP Remote GET',
							'result'        => $wp_get,
							'result_export' => $wp_get_export,
						),
					);

					/**
					 * Client Information
					 * ==================
					 */

					$user_client = new \SimplePay\Core\Browser();


					$browser = '<dl>';
					$browser .= '<dt>' . __( 'Name:', 'stripe' ) . '</dt>';
					$browser .= '<dd>' . $user_client->getBrowser() . '</dd>';
					$browser .= '<dt>' . __( 'Version:', 'stripe' ) . '</dt>';
					$browser .= '<dd>' . $user_client->getVersion() . '</dd>';
					$browser .= '<dt>' . __( 'User Agent:', 'stripe' ) . '</dt>';
					$browser .= '<dd>' . $user_client->getUserAgent() . '</dd>';
					$browser .= '<dt>' . __( 'Platform:', 'stripe' ) . '</dt>';
					$browser .= '<dd>' . $user_client->getPlatform() . '</dd>';
					$browser .= '</dl>';

					$browser_export = $user_client->getBrowser() . ' ' . $user_client->getVersion() . ' (' . $user_client->getPlatform() . ')';


					$sections['client'] = array(
						'user_ip' => array(
							'label'        => __( 'IP Address', 'stripe' ),
							'label_export' => 'IP Address',
							'result'       => $_SERVER['SERVER_ADDR'],
						),
						'browser' => array(
							'label'         => __( 'Browser', 'stripe' ),
							'result'        => $browser,
							'result_export' => $browser_export,
						),
					);

					/**
					 * Final Output
					 * ============
					 */
					$panels   = apply_filters( 'sc_system_status_report_panels', $panels );
					$sections = apply_filters( 'sc_system_status_report_sections', $sections );

					foreach ( $panels as $panel => $v ) {

						if ( isset( $sections[ $panel ] ) ) {
							?>
							<table class="widefat simpay-system-status-report-panel">
								<thead class="<?php echo $panel; ?>">
								<tr>
									<th colspan="3" data-export="<?php echo $v['export']; ?>"><?php echo $v['label']; ?></th>
								</tr>
								</thead>
								<tbody>
								<?php foreach ( $sections[ $panel ] as $row => $cell ) { ?>
									<tr>
										<?php
										$label_export  = isset( $cell['label_export'] ) ? $cell['label_export'] : $cell['label'];
										$result_export = isset( $cell['result_export'] ) ? $cell['result_export'] : $cell['result'];
										?>
										<td class="label" data-export="<?php echo trim( $label_export ); ?>"><?php echo $cell['label']; ?></td>
										<td class="result" data-export="<?php echo trim( $result_export ); ?>"><?php echo $cell['result']; ?></td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							<?php
						}
					}

					do_action( 'sc_system_status_report' );

					?>
					<script type="text/javascript">

						var report = '';

						jQuery( '.simpay-system-status-report-panel thead, .simpay-system-status-report-panel tbody' ).each( function() {

							if ( jQuery( this ).is( 'thead' ) ) {

								var label = jQuery( this ).find( 'th' ).data( 'export' );
								report = report + '\n### ' + jQuery.trim( label ) + ' ###\n\n';

							} else {

								jQuery( 'tr', jQuery( this ) ).each( function() {

									var label = jQuery( this ).find( 'td:eq(0)' ).data( 'export' );
									var the_name = jQuery.trim( label ).replace( /(<([^>]+)>)/ig, '' ); // Remove HTML
									var image = jQuery( this ).find( 'td:eq(2)' ).find( 'img' ); // Get WP 4.2 emojis
									var prefix = ( undefined === image.attr( 'alt' ) ) ? '' : image.attr( 'alt' ) + ' '; // Remove WP 4.2 emojis
									var the_value = jQuery.trim( prefix + jQuery( this ).find( 'td:eq(1)' ).data( 'export' ) );
									var value_array = the_value.split( ', ' );
									if ( value_array.length > 1 ) {
										var temp_line = '';
										jQuery.each( value_array, function( key, line ) {
											temp_line = temp_line + line + '\n';
										} );
										the_value = temp_line;
									}

									if ( the_name.trim() !== '' ) {
										report = report + '' + the_name.trim() + ': ' + the_value.trim() + '\n';
									}
								} );

							}

						} );

						try {
							jQuery( '#simpay-system-status-report textarea' ).val( report ).focus().select();
						} catch ( e ) {
							console.log( e );
						}

						function downloadReport( text, name, type ) {
							var a = document.getElementById( 'simpay-system-status-report-download' );
							var file = new Blob( [ text ], { type: type } );
							a.href = URL.createObjectURL( file );
							a.download = name;
						}

						jQuery( '#simpay-system-status-report-download' ).on( 'click', function() {
							var file = new Blob( [ report ], { type: 'text/plain' } );
							jQuery( this ).attr( 'href', URL.createObjectURL( file ) );
							jQuery( this ).attr( 'download', '<?php echo sanitize_title( str_replace( array(
									'http://',
									'https://',
								), '', get_bloginfo( 'url' ) ) . '-system-report-' . date( 'Y-m-d', time() ) ); ?>' );
						} );

					</script>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Output page markup.
	 *
	 * @param string $page
	 * @param string $tab
	 */

	/**
	 * PHP sizes conversions.
	 *
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 *
	 * @access private
	 *
	 * @param  string $size
	 *
	 * @return int|string
	 */
	private static function let_to_num( $size ) {

		$l   = substr( $size, -1 );
		$ret = substr( $size, 0, -1 );

		// Note: do not insert break or default in this switch loop.
		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;;
		}

		return $ret;
	}
}
