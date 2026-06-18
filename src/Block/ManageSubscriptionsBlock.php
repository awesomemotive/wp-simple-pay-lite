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

		$asset_file = SIMPLE_PAY_INC . 'pro/assets/js/dist/simpay-block-manage-subscriptions.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$script_data = require $asset_file;

		wp_register_script(
			'simpay-manage-subscriptions',
			SIMPLE_PAY_INC_URL . 'pro/assets/js/dist/simpay-block-manage-subscriptions.js',
			$script_data['dependencies'],
			$script_data['version']
		);

		// Register the view script.
		wp_register_script(
			'simpay-manage-subscriptions-frontend',
			SIMPLE_PAY_INC_URL . 'pro/assets/js/dist/simpay-public-pro-manage-subscriptions.js',
			array( 'wp-api-fetch', 'simpay-shared' ),
			$script_data['version'],
			true
		);

		// Pass REST API url to frontend.
		wp_localize_script(
			'simpay-manage-subscriptions-frontend',
			'simpayManageSubscription',
			array(
				'rest_url' => 'wpsp/__internal__/send/subscriptions',
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

		add_shortcode( 'simpay_manage_subscriptions', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Returns the default attribute strings shared by the block render
	 * callback and the shortcode handler.
	 *
	 * Keeping a single source of truth prevents the two entry points from
	 * drifting out of sync over time.
	 *
	 * @since 4.17.3
	 *
	 * @return array<string, string> Associative array of default strings keyed by
	 *                               attribute identifier (`label`,
	 *                               `email_placeholder`, `button_text`).
	 */
	private function get_defaults() {
		return array(
			'label'             => __( 'Purchase Email Address', 'stripe' ),
			'email_placeholder' => __( 'Enter your email', 'stripe' ),
			'button_text'       => __( 'Manage Subscription', 'stripe' ),
		);
	}

	/**
	 * Renders the [simpay_manage_subscriptions] shortcode output.
	 *
	 * Supports the same optional attributes as the block:
	 *   label             — email field label text
	 *   email_placeholder — email input placeholder
	 *   button_text       — submit button label
	 *
	 * @since 4.17.3
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string Shortcode HTML output.
	 */
	public function render_shortcode( $atts ) {
		$defaults = $this->get_defaults();

		// WordPress passes an empty string for shortcodes used without
		// attributes; normalize to an array for shortcode_atts().
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}

		$atts = shortcode_atts(
			$defaults,
			$atts,
			'simpay_manage_subscriptions'
		);

		wp_enqueue_script( 'simpay-manage-subscriptions-frontend' );

		return $this->render(
			array(
				'label'            => $atts['label'],
				'emailPlaceholder' => $atts['email_placeholder'],
				'buttonText'       => $atts['button_text'],
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
		$defaults = $this->get_defaults();

		/** @var string $label */
		$label = isset( $attributes['label'] ) ? $attributes['label'] : $defaults['label'];
		/** @var string $email_placeholder */
		$email_placeholder = isset( $attributes['emailPlaceholder'] ) ? $attributes['emailPlaceholder'] : $defaults['email_placeholder'];
		/** @var string $button_text */
		$button_text = isset( $attributes['buttonText'] ) ? $attributes['buttonText'] : $defaults['button_text'];

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
			esc_html( $label ),
			esc_attr( $email_placeholder ),
			esc_html( $button_text )
		);
	}
}
