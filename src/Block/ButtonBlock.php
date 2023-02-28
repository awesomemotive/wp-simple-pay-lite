<?php
/**
 * Block: Button block extension
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.7
 */

namespace SimplePay\Core\Block;

/**
 * ButtonBlock class.
 *
 * @since 4.4.7
 */
class ButtonBlock extends AbstractBlock {

	/**
	 * Event manager.
	 *
	 * @since 4.4.7.1
	 * @var \SimplePay\Core\EventManagement\EventManager $events Event manager.
	 */
	private $events;

	/**
	 * ButtonBlock
	 *
	 * @since 4.4.7.1
	 *
	 * @param \SimplePay\Core\EventManagement\EventManager $events Event manager.
	 */
	public function __construct( $events ) {
		$this->events = $events;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$this->events->add_callback(
			'enqueue_block_editor_assets',
			array( $this, 'enqueue_block_editor_assets' )
		);
	}

	/**
	 * Enqueues block editor assets.
	 *
	 * @since 4.4.7.1
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		$asset_file = SIMPLE_PAY_INC . '/core/assets/js/simpay-block-button.min.asset.php'; // @phpstan-ignore-line

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$script_data = require $asset_file;

		wp_enqueue_script(
			'simpay-block-button',
			SIMPLE_PAY_INC_URL . '/core/assets/js/simpay-block-button.min.js', // @phpstan-ignore-line
			$script_data['dependencies'],
			$script_data['version']
		);

		wp_localize_script(
			'simpay-block-button',
			'simpayBlockButton',
			array(
				'paymentForms' => $this->get_payment_form_options(),
			)
		);
	}

	/**
	 * Returns the available payment forms as options for the widget control.
	 *
	 * @since 4.4.7
	 *
	 * @return array<array<string, int|string>>
	 */
	private function get_payment_form_options() {
		/** @var array<array<string, int|string>> $options */
		static $options = array();

		if ( empty( $options ) ) {
			$forms = get_posts(
				array(
					'post_type'      => 'simple-pay',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			foreach ( $forms as $form_id ) {
				if ( false === $this->is_form_type_valid( $form_id ) ) {
					continue;
				}

				array_push(
					$options,
					array(
						'label' => get_the_title( $form_id ),
						'value' => intval( $form_id ),
					)
				);
			};
		}

		return $options;
	}

	/**
	 * Determines if a payment form can be used with the widget.
	 *
	 * Only accepts Stripe Checkout with no custom fields, or Overlay.
	 *
	 * @since 4.4.7
	 *
	 * @param int $form_id Payment form ID.
	 * @return bool
	 */
	private function is_form_type_valid( $form_id ) {
		// Only accept Stripe Checkout or Overlay.
		$type = simpay_get_saved_meta(
			$form_id,
			'_form_display_type',
			'stripe_checkout'
		);

		switch ( $type ) {
			case 'embedded':
				return false;
			case 'overlay':
				return true;
			default:
				/** @var array<string, array<string, array<string>>> $custom_fields */
				$custom_fields = simpay_get_saved_meta(
					$form_id,
					'_custom_fields',
					array()
				);

				$_custom_fields = array();

				foreach ( $custom_fields as $type => $fields ) {
					/** @var array<string, array<string>> $fields */
					foreach ( $fields as $k => $field ) {
						/** @var array<string> $field */
						$field['type']    = $type;
						$_custom_fields[] = $field;
					}
				}

				return count( $_custom_fields ) <= 2;
		}
	}

}
