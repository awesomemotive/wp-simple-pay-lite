<?php
/**
 * Payment Form Block
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Block;

use SimplePay\Core\Assets as CoreAssets;
use SimplePay\Core\Block\AbstractBlock;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Pro\Assets as ProAssets;

/**
 * PaymentFormBlock class.
 *
 * @since 4.4.2
 */
class PaymentFormBlock extends AbstractBlock implements LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$asset_file = SIMPLE_PAY_INC . '/core/assets/js/simpay-block-payment-form.min.asset.php'; // @phpstan-ignore-line

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$this->register_assets( $asset_file );

		register_block_type(
			'simpay/payment-form',
			array(
				'title'           => _x(
					'WP Simple Pay',
					'block title',
					'stripe'
				),
				'description'     => _x(
					'Display a WP Simple Pay payment form.',
					'block description',
					'stripe'
				),
				'category'        => 'widgets',
				'keywords'        => array(
					_x( 'form', 'block keyword', 'stripe' ),
					_x( 'payment', 'block keyword', 'stripe' ),
					_x( 'stripe', 'block keyword', 'stripe' ),
					_x( 'simple pay', 'block keyword', 'stripe' ),
					_x( 'wp simple pay', 'block keyword', 'stripe' ),
				),
				'attributes'      => array(
					'formId'          => array(
						'type' => 'integer',
					),
					'showTitle'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showDescription' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'preview'         => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
				'example'         => array(
					'attributes' => array(
						'preview' => true,
					)
				),
				'supports'        => array(
					'html'  => false,
					'align' => array( 'center' ),
				),
				'editor_script'   => 'simpay-block-payment-form',
				'editor_style'    => 'simpay-block-payment-form',
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Renders the block's output on the server.
	 *
	 * @since 4.4.2
	 *
	 * @param array<mixed> $attributes The block attributes.
	 * @return string Block content.
	 */
	public function render( $attributes ) {
		if ( ! isset( $attributes['formId'] ) || 0 === $attributes['formId'] ) {
			return '';
		}

		/** @var int $form_id */
		$form_id = $attributes['formId'];
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return '';
		}

		$is_frontend = (
			! defined( 'REST_REQUEST' ) ||
			( defined( 'REST_REQUEST' ) && ! REST_REQUEST )
		);

		$vars               = $this->get_form_vars( $form );
		$styles             = $this->generate_inline_styles( $attributes );
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'id' => 'simpay-block-payment-form-' . $form_id,
			)
		);

		return sprintf(
			'<div %1$s data-form-id="%2$s" data-form-vars=\'%3$s\'>%4$s</div><style>%5$s</style>',
			$wrapper_attributes,
			$form_id,
			// Only attach form variables in the block editor to prevent possible conflicts on the frontend.
			$is_frontend ? '' : wp_json_encode( $vars, JSON_HEX_QUOT | JSON_HEX_APOS ),
			do_shortcode( sprintf( '[simpay id="%d"]', $form_id ) ),
			$styles
		);
	}

	/**
	 * Registers assets required to render the payment form in the block editor.
	 *
	 * @since 4.4.2
	 *
	 * @param string $asset_file Block script asset file.
	 * @return void
	 */
	private function register_assets( $asset_file ) {
		// Register frontend payment form assets.
		$assets = new CoreAssets;

		if ( false === $this->license->is_lite() ) {
			new ProAssets;
		}

		$assets->register();

		$script_data = require $asset_file;

		// Register block editor payment form assets.
		wp_register_script(
			'simpay-block-payment-form',
			SIMPLE_PAY_INC_URL . '/core/assets/js/simpay-block-payment-form.min.js', // @phpstan-ignore-line
			array_merge(
				$script_data['dependencies'],
				array_keys( $assets->scripts )
			),
			$script_data['version']
		);

		wp_localize_script(
			'simpay-block-payment-form',
			'simpayBlockPaymentForm',
			array(
				'isLite'   => $this->license->is_lite() ? 1 : 0,
				'previews' => array(
					'pro'  => SIMPLE_PAY_INC_URL . '/core/assets/images/blocks/payment-form-preview-pro.png', // @phpstan-ignore-line
					'lite' => SIMPLE_PAY_INC_URL . '/core/assets/images/blocks/payment-form-preview-lite.png', // @phpstan-ignore-line
				)
			)
		);

		wp_register_style(
			'simpay-block-payment-form',
			SIMPLE_PAY_INC_URL . '/core/assets/css/simpay-block-payment-form.min.css', // @phpstan-ignore-line
			array_keys( $assets->styles ),
			$script_data['version']
		);

		simpay_shared_script_variables();
	}

	/**
	 * Returns a list of variables used to manually initialize the payment form in the block editor.
	 *
	 * This list is messy, weird, and confusing. You are not crazy. It is a way to simulate the
	 * form of the `var simplePayForms = []` script data normally output on the frontend.
	 *
	 * @link https://github.com/awesomemotive/wp-simple-pay-pro/issues/860
	 *
	 * @since 4.4.2
	 *
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @return array<mixed>
	 */
	private function get_form_vars( $form ) {
		$vars = array(
			'id'     => $form->id,
			'type'   => 'stripe_checkout' === $form->get_display_type()
				? 'stripe-checkout'
				: 'stripe-elements',
			'form'   => $form->get_form_script_variables(), // @phpstan-ignore-line
			'stripe' => array_merge(
				array(
					'amount'  => $form->total_amount, // @phpstan-ignore-line
					'country' => $form->country,
				),
				$form->get_stripe_script_variables()
			),
		);

		if ( false === $this->license->is_lite() ) {
			$temp = array();
			$temp[ $form->id ] = $vars;

			$pro = $form->pro_get_form_script_variables( $temp, $form->id ); // @phpstan-ignore-line

			$vars = wp_parse_args(
				$vars['form'],
				$pro[ $form->id ]
			);
		}

		return $vars;
	}

	/**
	 * Generates CSS to show/hide pieces of the payemnt form.
	 *
	 * @since 4.4.2
	 *
	 * @param array<mixed> $attributes The block attributes.
	 * @return string
	 */
	private function generate_inline_styles( $attributes ) {
		$styles  = '';
		/** @var int $form_id */
		$form_id = isset( $attributes['formId'] ) ? $attributes['formId'] : 0;

		// Hide the title.
		if (
			isset( $attributes['showTitle'] ) &&
			false === $attributes['showTitle']
		) {
			$styles .= sprintf(
				'[data-form-id="%d"] .simpay-form-title { display: none; }',
				$form_id
			);
		}

		// Hide the description.
		if (
			isset( $attributes['showDescription'] ) &&
			false === $attributes['showDescription']
		) {
			$styles .= sprintf(
				'[data-form-id="%d"] .simpay-form-description { display: none; }',
				$form_id
			);
		}

		// If both title and description are hidden, hide the header wrapper (embedded only).
		if (
			(
				isset( $attributes['showTitle'] ) &&
				false === $attributes['showTitle']
			) &&
			(
				isset( $attributes['showDescription'] ) &&
				false === $attributes['showDescription']
			)
		) {
			$styles .= sprintf(
				'[data-form-id="%d"] .simpay-embedded-heading { display: none; }',
				$form_id
			);
		}

		// Alignment.
		if ( isset( $attributes['align'] ) && 'center' === $attributes['align'] ) {
			$styles .= sprintf( '
				[data-form-id="%1$d"] .simpay-checkout-form,
				[data-form-id="%1$d"] .simpay-payment-btn,
				[data-form-id="%1$s"] .simpay-modal-control-open,
				[data-form-id="%1$s"] .simpay-embedded-heading {
					display: block;
					margin-left: auto;
					margin-right: auto;
				}
				[data-form-id="%1$s"] .simpay-embedded-heading.simpay-styled {
					max-width: 400px;
				}
				[data-form-id="%1$d"] .simpay-test-mode-badge-container {
					text-align: center;
				}',
				$form_id
			);
		}

		return $styles;
	}

}
