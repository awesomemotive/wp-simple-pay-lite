<?php
/**
 * Admin help: UI
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Help;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * HelpUi class.
 *
 * @since 4.4.6
 */
class HelpUi implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Documentation context.
	 *
	 * @since 4.4.6
	 * @var \SimplePay\Core\Help\HelpDocsContext
	 */
	private $context;

	/**
	 * HelpUi.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Core\Help\HelpDocsContext $context Documentation context.
	 */
	public function __construct( HelpDocsContext $context ) {
		$this->context = $context;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'simpay_admin_branding_bar_actions' => 'output',
		);
	}

	/**
	 * Enqueues remote help scripts and styles, creating the UI.
	 *
	 * @since 4.4.6
	 *
	 * @return void
	 */
	public function output() {
		$docs = $this->context->get_all_docs();

		wp_enqueue_style(
			'simpay-admin-help',
			SIMPLE_PAY_INC_URL . '/core/assets/css/simpay-admin-help.min.css', // @phpstan-ignore-line
			array(
				'wp-components',
			),
			SIMPLE_PAY_VERSION // @phpstan-ignore-line
		);

		if ( empty( $docs ) ) {
			$search_term = $this->context->get_search_term();

			if ( '' === $search_term ) {
				$url = 'https://wpsimplepay.com/docs/';
			} else {
				$url = add_query_arg(
					array(
						's'    => $this->context->get_search_term(),
						'docs' => '1',
					),
					'https://wpsimplepay.com/docs/'
				);
			}

			$url = simpay_ga_url( $url, 'help', 'Help' );

			echo '<div id="simpay-branding-bar-help"><a href="' . esc_url( $url ) . '" target="_blank" class="simpay-branding-bar__actions-button"><svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001c0-4.60002 3.73334-8.33335 8.33334-8.33335 4.59996 0 8.33336 3.73333 8.33336 8.33335 0 4.6-3.7334 8.3333-8.33336 8.3333-4.6 0-8.33334-3.7333-8.33334-8.3333Zm9.1667 3.3333v1.6667H9.1665v-1.6667h1.6667Zm-.83336 3.3333c-3.675 0-6.66667-2.9916-6.66667-6.6666 0-3.67502 2.99167-6.66669 6.66667-6.66669 3.67496 0 6.66666 2.99167 6.66666 6.66669 0 3.675-2.9917 6.6666-6.66666 6.6666ZM6.6665 8.33341c0-1.84166 1.49167-3.33333 3.33334-3.33333 1.84166 0 3.33336 1.49167 3.33336 3.33333 0 1.0691-.6584 1.64444-1.2994 2.20459-.6081.5315-1.2006 1.0493-1.2006 1.9621H9.1665c0-1.5177.7851-2.1195 1.4754-2.64862.5415-.41506 1.0246-.78539 1.0246-1.51807 0-.91666-.75-1.66666-1.66666-1.66666-.91667 0-1.66667.75-1.66667 1.66666H6.6665Z" fill="currentColor"/></svg></a></div>';
			return;
		}

		$asset_file = SIMPLE_PAY_INC . '/core/assets/js/simpay-admin-help.min.asset.php'; // @phpstan-ignore-line

		// Show an icon and link to external docs.
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset_data = require $asset_file;

		wp_enqueue_script(
			'simpay-admin-help',
			SIMPLE_PAY_INC_URL . '/core/assets/js/simpay-admin-help.min.js', // @phpstan-ignore-line
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);

		$seen_help = get_option( 'simpay_has_seen_help_icon', false );

		wp_localize_script(
			'simpay-admin-help',
			'simpayHelp',
			array(
				'hasSeen'        => $seen_help ? 1 : 0,
				'isLite'         => $this->license->is_lite() ? 1 : 0,
				'docs'           => $this->context->get_all_docs(),
				'docsSearchTerm' => $this->context->get_search_term(),
				'docsCategories' => $this->context->get_search_categories(),
			)
		);

		if ( ! $seen_help ) {
			update_option( 'simpay_has_seen_help_icon', true );
		}

		echo '<div id="simpay-branding-bar-help"></div>';
	}

}
