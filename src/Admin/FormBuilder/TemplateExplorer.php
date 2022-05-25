<?php
/**
 * Form builder: Template explorer
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Admin\FormBuilder;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * TemplateExplorer class.
 *
 * @since 4.4.3
 */
class TemplateExplorer implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		if ( false === $this->is_license_valid() ) {
			return array();
		}

		return array(
			'admin_init'    => 'maybe_restrict_template',
			'edit_form_top' => 'maybe_show_template_explorer',
			'admin_notices' => 'maybe_show_previewing_template',
			'init'          => array( 'maybe_set_post_type_queryable', 9 ),
		);
	}

	/**
	 * Restricts template usage to the current license type.
	 *
	 * Override the global $_GET to ensure the template is only used with the current license.
	 *
	 * @todo This is hacky and should be refactored once templates are applied to the form itself
	 * vs. injecting in to form settings individually.
	 *
	 * @since 4.4.3
	 *
	 * @return void
	 */
	public function maybe_restrict_template() {
		if ( ! isset( $_GET['simpay-template'] ) ) {
			return;
		}

		$template_id = sanitize_text_field( $_GET['simpay-template'] );
		$template    = __unstable_simpay_get_payment_form_template(
			$template_id
		);

		if ( null === $template ) {
			$_GET['simpay-template'] = null;
			return;
		}

		/** @var array<string> $licenses */
		$licenses      = $template['license'];
		$license_level = $this->license->get_level();

		if ( ! in_array( $license_level, $licenses, true ) ) {
			$_GET['simpay-template'] = null;
			return;
		}
	}

	/**
	 * Outputs a notice that a template is being used before the form is saved.
	 *
	 * @since 4.4.3
	 *
	 * @return void
	 */
	function maybe_show_previewing_template() {
		$screen = get_current_screen();

		if ( null === $screen ) {
			return;
		}

		if ( 'simple-pay' !== $screen->id || 'add' !== $screen->action ) {
			return;
		}

		if ( ! isset( $_GET['simpay-template'] ) ) {
			return;
		}

		$template_id = sanitize_text_field( $_GET['simpay-template'] );
		$template    = __unstable_simpay_get_payment_form_template(
			$template_id
		);

		if ( null === $template ) {
			return;
		}

		$new_url = add_query_arg(
			array(
				'post_type' => 'simple-pay',
			),
			admin_url( 'post-new.php' )
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-notice-template-preview.php'; // @phpstan-ignore-line
	}

	/**
	 * Outputs the template explorer when adding a new form.
	 *
	 * @since 4.4.3
	 *
	 * @param \WP_Post $post Payment form post object.
	 * @return void
	 */
	public function maybe_show_template_explorer( $post ) {
		// We are not adding a payment form, do not show the explorer.
		if (
			! isset( $_GET['post_type'] ) ||
			'simple-pay' !== sanitize_text_field( $_GET['post_type'] )
		) {
			return;
		}

		// A payment form template is in the URL, do not show the explorer.
		if ( isset( $_GET['simpay-template'] ) ) {
			return;
		}

		// The payment form has already been created, do not show the explorer.
		if ( 'auto-draft' !== $post->post_status ) {
			return;
		}

		$asset_file = SIMPLE_PAY_INC . '/core/assets/js/simpay-admin-form-template-explorer.min.asset.php'; // @phpstan-ignore-line

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset_data = require $asset_file;

		wp_enqueue_script(
			'simpay-admin-form-template-explorer',
			SIMPLE_PAY_INC_URL . '/core/assets/js/simpay-admin-form-template-explorer.min.js', // @phpstan-ignore-line
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);

		$is_lite = $this->license->is_lite();

		wp_localize_script(
			'simpay-admin-form-template-explorer',
			'simpayFormBuilderTemplateExplorer',
			array(
				'licenseLevel'        => $this->license->get_level(),
				'addNewUrl'           => add_query_arg(
					array(
						'post_type' => 'simple-pay',
					),
					admin_url( 'post-new.php' )
				),
				'suggestUrl'          => simpay_ga_url(
					'https://wpsimplepay.com/payment-form-template-suggestion/',
					'template-explorer',
					'suggest a template'
				),
				'upgradeUrl'          => simpay_ga_url(
					(
						$is_lite
							? 'https://wpsimplepay.com/lite-vs-pro/'
							// add_query_arg escapes this, which doesn't play nicely on the client.
							: 'https://wpsimplepay.com/pricing/?license_key=' . $this->license->get_key()
					),
					'template-explorer',
					$is_lite ? 'Upgrade to Pro' : 'Upgrade Now'
				),
				'alreadyPurchasedUrl' => simpay_docs_link(
					'Already purchased?',
					(
						$is_lite
							? 'upgrading-wp-simple-pay-lite-to-pro'
							: 'activate-wp-simple-pay-pro-license'
					),
					'template-explorer',
					true
				),
				'templates'           => array_values(
					__unstable_simpay_get_payment_form_templates()
				),
			)
		);

		wp_set_script_translations(
			'simpay-admin-form-template-explorer',
			'simple-pay',
			SIMPLE_PAY_DIR . '/languages' // @phpstan-ignore-line
		);

		wp_enqueue_style(
			'simpay-admin-form-template-explorer',
			SIMPLE_PAY_INC_URL . '/core/assets/css/simpay-admin-form-template-explorer.min.css', // @phpstan-ignore-line
			array(
				'wp-components',
			),
			$asset_data['version']
		);

		echo '<div id="simpay-form-template-explorer"></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}


	/**
	 * Temporarily sets the `simple-pay` post type `publicaly_queryable` to `true`
	 * when a payment form template is detected.
	 *
	 * @since 4.4.3
	 *
	 * @return void
	 */
	public function maybe_set_post_type_queryable() {
		if ( ! isset( $_GET['simpay-template'], $_GET['post_type'] ) ) {
			return;
		}

		if ( 'simple-pay' !== sanitize_text_field( $_GET['post_type'] ) ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		add_filter(
			'register_post_type_args',
			function( $args, $post_type ) {
				if ( 'simple-pay' !== $post_type ) {
					return $args;
				}

				$args['publicly_queryable'] = true;

				return $args;
			},
			10,
			2
		);
	}

	/**
	 * Determines if the current license is valid and the Templat Explorer can be used.
	 *
	 * @since 4.4.6
	 *
	 * @return bool
	 */
	private function is_license_valid() {
		if ( $this->license->is_lite() ) {
			return true;
		}

		if ( empty( $this->license->get_key() ) ) {
			return false;
		}

		switch ( $this->license->get_status() ) {
			case 'expired':
				if ( ! $this->license->is_in_grace_period() ) {
					return false;
				}
			case 'invalid':
				return false;
		}

		return true;
	}

}
