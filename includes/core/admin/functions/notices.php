<?php
/**
 * Notice functionality.
 *
 * @since 3.4.0
 */

namespace SimplePay\Core\Admin\Notices;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function to display error messages if SSL is not enabled.
 *
 * @since 3.5.0
 *
 * @return string
 */
function no_ssl() {
	if ( is_ssl() ) {
		return false;
	}

	ob_start();
?>

<p>
<?php
/**
 * Filter the message shown when no SSL certificate is available.
 *
 * @since unknown
 *
 * @param string $message
 */
echo apply_filters(
	'simpay_ssl_admin_notice_message',
	__( 'SSL (HTTPS) is not enabled. You will not be able to process live Stripe transactions until SSL is enabled.', 'stripe' )
);
?>
</p>

<?php
	return ob_get_clean();
}

/**
 * Function to display an alert to installs that have not authorized through Stripe Connect
 *
 * @return string
 */
function stripe_connect() {
	if ( simpay_is_admin_screen() && isset( $_GET['tab'] ) && 'keys' === $_GET['tab'] ) {
		return false;
	}

	if ( simpay_get_account_id() ) {
		return false;
	}

	simpay_stripe_connect_button_css();

	ob_start();
?>

<p>
	<?php _e( 'WP Simple Pay supports Stripe Connect for easier setup and improved security. Connect now to start accepting payments instantly.', 'stripe' ); ?>
</p>

<p>
	<a href="<?php echo esc_url( simpay_get_stripe_connect_url() ); ?>" class="wpsp-stripe-connect"><span>
		<?php esc_html_e( 'Connect with Stripe', 'stripe' ); ?>
	</span></a>
</p>

<?php

	return ob_get_clean();
}

/**
 * Output the PHP requirement notice.
 *
 * This warns users that the plugin will not be able to function in their
 * environment after a future update.
 *
 * @since 3.4.0
 *
 * @return string
 */
function php_version_56() {
	$future_required_version = 5.6;

	if ( ! version_compare( PHP_VERSION, $future_required_version, '<' ) ) {
		return false;
	}

	ob_start();
?>

<p>
	<strong><?php esc_html_e( 'WP Simple Pay is increasing its PHP version requirement.', 'stripe' ); ?></strong>
</p>

<p>
	<?php echo sprintf( __( 'WP Simple Pay will be increasing its PHP requirement to version <code>%1$s</code> or higher in an upcoming release. It looks like you\'re using version <code>%2$s</code>, which means you will need to upgrade your version of PHP to allow the plugin to continue to function. Newer versions of PHP are both faster and more secure. The version you\'re using <a href="%3$s" target="_blank" rel="noopener noreferrer">no longer receives security updates</a>, which is another great reason to update.', 'stripe' ), $future_required_version, PHP_VERSION, 'http://php.net/eol.php' ); ?>
</p>

<p>
	<strong><?php esc_html_e( 'Which version should I upgrade to?', 'stripe' ); ?></strong>
</p>

<p>
	<?php echo sprintf( __( 'In order to be compatible with future versions of WP Simple Pay, you should update your PHP version to <code>%1$s</code>, <code>7.0</code>, <code>7.1</code>, or <code>7.2</code>. On a normal WordPress site, switching to PHP <code>%1$s</code> should never cause issues. We would however actually recommend you switch to PHP <code>7.1</code> or higher to receive the full speed and security benefits provided to more modern and fully supported versions of PHP. However, some plugins may not be fully compatible with PHP <code>7.x</code>, so more testing may be required.', 'stripe' ), $future_required_version ); ?>
</p>

<p>
	<strong><?php esc_html_e( 'Need help upgrading? Ask your web host!', 'stripe' ); ?></strong>
</p>

<p>
	<?php echo sprintf( __( 'Many web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. If you need to change hosts, please see <a href="%s" target="_blank" rel="noopener noreferrer">our hosting recommendations</a>.', 'stripe' ), 'https://wpsimplepay.com/recommended-wordpress-hosting/' ); ?>
</p>

<?php
	return ob_get_clean();
}
