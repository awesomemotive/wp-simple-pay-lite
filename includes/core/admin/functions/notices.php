<?php
/**
 * Admin notices: Callbacks
 *
 * @package SimplePay\Core\Admin\Notices
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
 * Returns a notice message to display when the REST API is disabled.
 *
 * @since 4.0.0
 *
 * @return string
 */
function no_rest_api() {
	if ( true === simpay_is_rest_api_enabled() ) {
		return false;
	}

	return wpautop(
		esc_html__( 'WP Simple Pay requires the WordPress REST API to be enabled to process payments.', 'stripe' )
	);
}

/**
 * Function to display an alert to installs that have not authorized through Stripe Connect
 *
 * @return string
 */
function stripe_connect() {
	if (
		simpay_is_admin_screen() &&
		isset( $_GET['post_type'] ) &&
		'simple-pay' === sanitize_key( $_GET['post_type'] )
	) {
		return false;
	}

	if ( simpay_get_account_id() ) {
		return false;
	}

	ob_start();
	?>

<p>
	<?php esc_html_e( 'WP Simple Pay requires Stripe Connect for the highest reliability and security. Connect now to start accepting payments instantly.', 'stripe' ); ?>
</p>

<p>
	<?php echo simpay_get_stripe_connect_button(); // PHPCS:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<a href="<?php echo esc_url( simpay_docs_link( 'Learn More', 'stripe-setup', 'global-notice', true ) ); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="margin-left: 5px;">
		<?php esc_html_e( 'Learn More', 'stripe' ); ?>
	</a>
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

<?php
echo wp_kses(
	wpautop(
		sprintf(
			/* translators: %1$s Future PHP version requirement. %2$s Current PHP version. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
			__(
				'WP Simple Pay will be increasing its PHP requirement to version %1$s or higher in an upcoming release. It looks like you\'re using version %2$s, which means you will need to upgrade your version of PHP to allow the plugin to continue to function. Newer versions of PHP are both faster and more secure. The version you\'re using %3$sno longer receives security updates%4$s, which is another great reason to update.',
				'stripe'
			),
			'<code>' . $future_required_version . '</code>',
			'<code>' . PHP_VERSION . '</code>',
			'<a href="http://php.net/eol.php" target="_blank" rel="noopener noreferrer">',
			'</a>'
		)
	),
	array(
		'code' => true,
		'a'    => array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
		),
	)
);
?>

<p>
	<strong><?php esc_html_e( 'Which version should I upgrade to?', 'stripe' ); ?></strong>
</p>

<?php
echo wpautop(
	wp_kses(
		sprintf(
			/* translators: %1$s Future PHP version requirement. */
			__(
				'In order to be compatible with future versions of WP Simple Pay, you should update your PHP version to %1$s, <code>7.0</code>, <code>7.1</code>, or <code>7.2</code>. On a normal WordPress site, switching to PHP <code>%1$s</code> should never cause issues. We would however actually recommend you switch to PHP <code>7.1</code> or higher to receive the full speed and security benefits provided to more modern and fully supported versions of PHP. However, some plugins may not be fully compatible with PHP <code>7.x</code>, so more testing may be required.',
				'stripe'
			),
			'<code>' . $future_required_version . '</code>'
		),
		array(
			'code' => true,
		)
	)
);
?>

<p>
	<strong><?php esc_html_e( 'Need help upgrading? Ask your web host!', 'stripe' ); ?></strong>
</p>

<?php
echo wpautop(
	wp_kses(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__(
				'Many web hosts can give you instructions on how/where to upgrade your version of PHP through their control panel, or may even be able to do it for you. If you need to change hosts, please see %1$sour hosting recommendations%2$s.',
				'stripe'
			),
			'<a href="https://www.wpbeginner.com/wordpress-hosting/" target="_blank" rel="noopener noreferrer">',
			'</a>'
		),
		array(
			'a'    => array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
		)
	)
);
?>

	<?php
	return ob_get_clean();
}
