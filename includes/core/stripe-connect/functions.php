<?php
/**
 * Stripe Connect
 *
 * @package SimplePay\Core\Stripe_Connect
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.4.0
 */

use SimplePay\Core\Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate a URL to begin "Connect with Stripe" process.
 *
 * @since 3.5.0
 *
 * @param string $redirect_url URL to redirect back to. Default "WP Simple Pay > Settings > Stripe".
 * @return string
 */
function simpay_get_stripe_connect_url( $redirect_url = '' ) {
	if ( empty( $redirect_url ) ) {
		$redirect_url = Settings\get_url(
			array(
				'section'    => 'stripe',
				'subsection' => 'account',
			)
		);
	}

	return add_query_arg(
		array(
			'live_mode'         => (int) ! simpay_is_test_mode(),
			'state'             => str_pad( wp_rand( wp_rand(), PHP_INT_MAX ), 100, wp_rand(), STR_PAD_BOTH ),
			'customer_site_url' => urlencode( $redirect_url ),
		),
		'https://wpsimplepay.com/?wpsp_gateway_connect_init=stripe_connect'
	);
}

/**
 * Generate a URL to disconnect a Stripe account (in WordPress).
 *
 * @since 3.5.0
 *
 * @return string
 */
function simpay_get_stripe_disconnect_url() {
	return add_query_arg(
		array(
			'simpay-stripe-disconnect' => true,
			'_wpnonce'                 => wp_create_nonce(
				'simpay-stripe-connect-disconnect'
			),
		),
		Settings\get_url(
			array(
				'section'    => 'stripe',
				'subsection' => 'account',
			)
		)
	);
}

/**
 * Retrieve stored Stripe Account ID (generated from Stripe Connect).
 *
 * @since unknown
 *
 * @return string $account_id Stripe Account ID.
 */
function simpay_get_account_id() {
	$account_id = get_option( 'simpay_stripe_connect_account_id', false );

	if ( ! $account_id || '' === trim( $account_id ) ) {
		return false;
	}

	return trim( $account_id );
}

/**
 * Determine if the current site can manually manage Stripe Keys.
 *
 * If a Stripe Account ID exists, the keys cannot be set manually.
 *
 * @since 3.4.0
 *
 * @return bool
 */
function simpay_can_site_manage_stripe_keys() {
	$can = false;

	// No connection has been made, and keys are already set, let management continue.
	if ( ! simpay_get_account_id() && simpay_get_secret_key() ) {
		$can = true;
	}

	/**
	 * Filter the ability to manually manage Stripe keys.
	 *
	 * @since 3.4.0
	 *
	 * @param bool $can If the keys can be managed.
	 */
	$can = apply_filters( 'simpay_can_site_manage_stripe_keys', $can );

	return $can;
}

/**
 * Returns a Stripe Connect button.
 *
 * @since 4.2.2
 *
 * @param string $redirect_url URL to redirect back to. Default "WP Simple Pay > Settings > Stripe".
 * @return string
 */
function simpay_get_stripe_connect_button( $redirect_url = '' ) {
	$url = simpay_get_stripe_connect_url( $redirect_url );

	ob_start();
	?>

	<a href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr__( 'Connect with Stripe', 'stripe' ); ?>" class="wpsp-stripe-connect">
		<span>
		<?php
		/* translators: Text before Stripe logo for "Connect with Stripe" button. */
		esc_html_e( 'Connect with', 'stripe' );
		?>
		</span>

		<svg width="49" height="20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M48.4718 10.3338c0-3.41791-1.6696-6.11484-4.8607-6.11484-3.2045 0-5.1434 2.69693-5.1434 6.08814 0 4.0187 2.289 6.048 5.5743 6.048 1.6023 0 2.8141-.3604 3.7296-.8678v-2.6702c-.9155.4539-1.9658.7343-3.2987.7343-1.3061 0-2.464-.4539-2.6121-2.0294h6.5841c0-.1735.0269-.8678.0269-1.1882Zm-6.6514-1.26838c0-1.50868.929-2.13618 1.7773-2.13618.8213 0 1.6965.6275 1.6965 2.13618h-3.4738Zm-8.5499-4.84646c-1.3195 0-2.1678.61415-2.639 1.04139l-.1751-.82777h-2.9621V20l3.3661-.7076.0134-3.7784c.4847.3471 1.1984.8411 2.3832.8411 2.4102 0 4.6048-1.9225 4.6048-6.1548-.0134-3.87186-2.235-5.98134-4.5913-5.98134Zm-.8079 9.19894c-.7944 0-1.2656-.2804-1.5888-.6275l-.0134-4.95328c.35-.38719.8348-.65421 1.6022-.65421 1.2253 0 2.0735 1.36182 2.0735 3.11079 0 1.7891-.8347 3.1242-2.0735 3.1242Zm-9.6001-9.98666 3.3796-.72096V0l-3.3796.70761v2.72363Zm0 1.01469h3.3796V16.1282h-3.3796V4.44593Zm-3.6219.98798-.2154-.98798h-2.9083V16.1282h3.3661V8.21095c.7944-1.02804 2.1408-.84112 2.5582-.69426V4.44593c-.4309-.16022-2.0062-.45394-2.8006.98798Zm-6.7322-3.88518-3.2853.69426-.01346 10.69421c0 1.976 1.49456 3.4313 3.48726 3.4313 1.1041 0 1.912-.2003 2.3563-.4406v-2.7103c-.4309.1736-2.5583.7877-2.5583-1.1882V7.28972h2.5583V4.44593h-2.5583l.0135-2.8972ZM3.40649 7.83712c0-.5207.43086-.72096 1.14447-.72096 1.0233 0 2.31588.30707 3.33917.85447V4.83311c-1.11755-.44059-2.22162-.61415-3.33917-.61415C1.81769 4.21896 0 5.63418 0 7.99733c0 3.68487 5.11647 3.09747 5.11647 4.68627 0 .6141-.53858.8144-1.29258.8144-1.11755 0-2.54477-.4539-3.675782-1.0681v3.1776c1.252192.534 2.517842.761 3.675782.761 2.80059 0 4.72599-1.3752 4.72599-3.765-.01346-3.97867-5.14339-3.27106-5.14339-4.76638Z" fill="#fff"/></svg>
	</a>

	<style>
	.wpsp-stripe-connect {
		color: #fff;
		font-size: 15px;
		font-weight: bold;
		text-decoration: none;
		line-height: 1;
		background-color: #635bff;
		border-radius: 3px;
		padding: 10px 20px;
		display: inline-flex;
		align-items: center;
	}

	.wpsp-stripe-connect:focus,
	.wpsp-stripe-connect:hover {
		color: #fff;
		background-color: #0a2540;
	}

	.wpsp-stripe-connect:focus {
		outline: 0;
		box-shadow: inset 0 0 0 1px #fff, 0 0 0 1.5px #0a2540;
	}

	.wpsp-stripe-connect svg {
		margin-left: 5px;
	}
	</style>

	<?php
	return ob_get_clean();
}
