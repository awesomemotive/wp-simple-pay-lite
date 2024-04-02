<?php
/**
 * Block: Manage Subscription
 *
 * @package SimplePay
 * @subpackage Core
 * @since 4.8.0
 */

namespace SimplePay\Core\Block;

use SimplePay\Core\AntiSpam\Captcha\ScriptUtils;

/**
 * ManageSubscriptionBlock class.
 *
 * @since 4.8.0
 */
class ManageSubscriptionsBlock extends AbstractBlock {

	/**
	 * {@inheritdoc}
	 */
	public function register() {

		$asset_file = SIMPLE_PAY_INC . 'pro/assets/js/dist/simpay-block-manage-subscriptions.asset.php'; // @phpstan-ignore-line

		if ( ! file_exists( $asset_file ) ) {
			error_log( 'file does not exists' );
			return;
		}

		$script_data = require $asset_file;

		wp_register_script(
			'simpay-manage-subscriptions',
			SIMPLE_PAY_INC_URL . 'pro/assets/js/dist/simpay-block-manage-subscriptions.js', // @phpstan-ignore-line
			$script_data['dependencies'],
			$script_data['version']
		);

		// Register the view script.
		wp_register_script(
			'simpay-manage-subscriptions-frontend',
			SIMPLE_PAY_INC_URL . 'pro/assets/js/dist/simpay-public-pro-manage-subscriptions.js', // @phpstan-ignore-line
			array( 'wp-api-fetch' ),
			$script_data['version'],
			true
		);

		// Pass REST API url to frontend.
		wp_localize_script(
			'simpay-manage-subscriptions-frontend',
			'simpayManageSubscription',
			array(
				'rest_url' => esc_url_raw(
					'/wpsp/__internal__/send/subscriptions'
				),
				'messages' => array(
					'wait_message'          => __( 'Please wait...', 'stripe' ),
					'valid_email_warning'   => __( 'Please enter a valid email address.', 'stripe' ),
					'request_error_message' => __( 'Request failed.', 'stripe' ),
					'captcha_error_message' => __( 'Invalid CAPTCHA. Please try again.', 'stripe' ),
				),
			)
		);

		register_block_type(
			'simpay/manage-subscriptions-block',
			array(
				'editor_script'   => 'simpay-manage-subscriptions',
				'view_script'     => 'simpay-manage-subscriptions-frontend',
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Renders the block's output on the server.
	 *
	 * @since 4.7.11
	 *
	 * @param array<mixed> $attributes The block attributes.
	 * @return string Block content.
	 */
	public function render( $attributes ) {

		/** @var string $label */
		$label = isset( $attributes['label'] ) ? $attributes['label'] : __( 'Purchase Email Address', 'stripe' );
		/** @var string $email_placeholder */
		$email_placeholder = isset( $attributes['emailPlaceholder'] ) ? $attributes['emailPlaceholder'] : __( 'Enter your email', 'stripe' );
		/** @var string $button_text */
		$button_text = isset( $attributes['buttonText'] ) ? $attributes['buttonText'] : __( 'Manage Subscription', 'stripe' );

		// Enqueue captcha scripts.

		$action = 'manage-subscriptions';
		ScriptUtils::enqueue_captcha_scripts();
		$captcha_content = ScriptUtils::render_captcha( $action );

		$form_format = '
			<form id="simpay-manage-subscription-form" class="simpay-subscription-management-form">
				<div id="messageContainer" class="form-message-container">
					<div class="form-message"></div>
				</div>
				<p>
					<label for="simpay-ms-email">%1$s</label>
					<input class="form-input-email" id="simpay-ms-email" type="email" placeholder="%2$s" />
				</p>
				' . $captcha_content . '
				<p class="form-submit wp-block-button">
					<input type="submit" id="simpay-ms-submit-btn" class="wp-block-button__link wp-element-button form-button" value="%3$s" />
				</p>
			</form>';

		return sprintf(
			$form_format,
			esc_attr( $label ),
			esc_attr( $email_placeholder ),
			esc_html( $button_text )
		);
	}
}
