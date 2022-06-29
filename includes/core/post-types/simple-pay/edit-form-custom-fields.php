<?php
/**
 * Simple Pay: Edit form custom fields
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get custom field option group labels.
 *
 * @since 3.8.0
 *
 * @return array Group label names.
 */
function get_custom_field_type_groups() {
	$groups = array(
		'payment'  => _x( 'Payment', 'custom field group', 'stripe' ),
		'customer' => _x( 'Customer', 'custom field group', 'stripe' ),
		'standard' => _x( 'Standard', 'custom field group', 'stripe' ),
		'custom'   => _x( 'Custom', 'custom field group', 'stripe' ),
	);

	/**
	 * Filter the labels associated with field groups.
	 *
	 * @since 3.4.0
	 *
	 * @param array $groups optgroup/category keys and associated labels.
	 */
	return apply_filters( 'simpay_custom_field_group_labels', $groups );
}

/**
 * Get the available custom field types.
 *
 * @since 3.8.0
 *
 * @return array $fields Custom fields.
 */
function get_custom_field_types() {
	$fields = array(
		'customer_name'           => array(
			'label'      => esc_html__( 'Name', 'stripe' ),
			'type'       => 'customer_name',
			'category'   => 'customer',
			'active'     => true,
			'repeatable' => false,
		),
		'email'                   => array(
			'label'      => esc_html__( 'Email Address', 'stripe' ),
			'type'       => 'email',
			'category'   => 'customer',
			'active'     => true,
			'repeatable' => false,
		),
		'telephone'               => array(
			'label'      => esc_html__( 'Phone', 'stripe' ),
			'type'       => 'telephone',
			'category'   => 'customer',
			'active'     => true,
			'repeatable' => false,
		),
		'address'                 => array(
			'label'      => esc_html__( 'Address', 'stripe' ),
			'type'       => 'address',
			'category'   => 'customer',
			'active'     => true,
			'repeatable' => false,
		),
		'tax_id'                  => array(
			'label'      => esc_html__( 'Tax ID', 'stripe' ),
			'type'       => 'tax_id',
			'category'   => 'customer',
			'active'     => true,
			'repeatable' => false,
		),

		'plan_select'             => array(
			'label'      => esc_html__( 'Price Selector', 'stripe' ),
			'type'       => 'plan_select',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => false,
		),
		'coupon'                  => array(
			'label'      => esc_html__( 'Coupon', 'stripe' ),
			'type'       => 'coupon',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => false,
		),
		'custom_amount'           => array(
			'label'      => esc_html__( 'Custom Amount Input', 'stripe' ),
			'type'       => 'custom_amount',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => false,
		),
		'recurring_amount_toggle' => array(
			'label'      => esc_html__( 'Recurring Amount Toggle', 'stripe' ),
			'type'       => 'recurring_amount_toggle',
			'category'   => 'payment',
			'active'     => simpay_subscriptions_enabled(),
			'repeatable' => false,
		),
		'total_amount'            => array(
			'label'      => esc_html__( 'Amount Breakdown', 'stripe' ),
			'type'       => 'total_amount',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => true,
		),
		'payment_request_button'  => array(
			'label'      => esc_html__( 'Apple Pay/Google Pay Button', 'stripe' ),
			'type'       => 'payment_request_button',
			'category'   => 'payment',
			'active'     => simpay_can_use_payment_request_button(),
			'repeatable' => false,
		),
		'card'                    => array(
			'label'      => esc_html__( 'Payment Methods', 'stripe' ),
			'type'       => 'card',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => false,
		),
		'checkout_button'         => array(
			'label'      => esc_html__( 'Checkout Button', 'stripe' ),
			'type'       => 'checkout_button',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => false,
		),
		'payment_button'          => array(
			'label'      => esc_html__( 'Payment Button', 'stripe' ),
			'type'       => 'payment_button',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => false,
		),

		'heading'                 => array(
			'label'      => esc_html__( 'Heading', 'stripe' ),
			'type'       => 'heading',
			'category'   => 'standard',
			'active'     => true,
			'repeatable' => true,
		),
		'text'                    => array(
			'label'      => esc_html__( 'Text', 'stripe' ),
			'type'       => 'text',
			'category'   => 'standard',
			'active'     => true,
			'repeatable' => true,
		),
		'dropdown'                => array(
			'label'      => esc_html__( 'Dropdown', 'stripe' ),
			'type'       => 'dropdown',
			'category'   => 'standard',
			'active'     => true,
			'repeatable' => true,
		),
		'radio'                   => array(
			'label'      => esc_html__( 'Radio Select', 'stripe' ),
			'type'       => 'radio',
			'category'   => 'standard',
			'active'     => true,
			'repeatable' => true,
		),
		'date'                    => array(
			'label'      => esc_html__( 'Date', 'stripe' ),
			'type'       => 'date',
			'category'   => 'standard',
			'active'     => true,
			'repeatable' => true,
		),
		'number'                  => array(
			'label'      => esc_html__( 'Number', 'stripe' ),
			'type'       => 'number',
			'category'   => 'standard',
			'active'     => true,
			'repeatable' => true,
		),
		'checkbox'                => array(
			'label'      => esc_html__( 'Checkbox', 'stripe' ),
			'type'       => 'checkbox',
			'category'   => 'standard',
			'active'     => true,
			'repeatable' => true,
		),
		'hidden'                  => array(
			'label'      => esc_html__( 'Hidden', 'stripe' ),
			'type'       => 'hidden',
			'category'   => 'standard',
			'active'     => true,
			'repeatable' => true,
		),
	);

	/**
	 * Filters available custom fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $fields Custom fields.
	 */
	return apply_filters( 'simpay_custom_field_options', $fields );
}

/**
 * Get a grouped list of custom field options.
 *
 * @since 3.8.0
 *
 * @param array $options Flat list of options.
 * @return array $options Grouped list of options.
 */
function get_custom_fields_grouped( $options = array() ) {
	if ( empty( $options ) ) {
		$options = get_custom_field_types();
	}

	$result = array();
	$groups = get_custom_field_type_groups();

	foreach ( $options as $key => $option ) {
		if ( isset( $option['category'] ) ) {
			$result[ $groups[ $option['category'] ] ][ $key ] = $option;
		} else {
			$result[ $groups['custom'] ][ $key ] = $option;
		}
	}

	return $result;
}

/**
 * Retrieves a form's custom fields.
 *
 * Formats legacy data in to a consumable structure.
 * Legacy structure has field types grouped under a `type` index.
 *
 * @see \SimplePay\Pro\Post_Types\Simple_Pay\Util\get_custom_fields()
 *
 * @since 4.4.3
 * @since 4.4.3 Introduced in Core namespace. Keeps duplicate code due to fragility of legacy code.
 *
 * array(2) {
 *  ["text"]=>
 *  array(2) {
 *    [3]=>
 *    array(8) {
 *      ["id"]=>
 *      string(0) ""
 *      ["order"]=>
 *      string(1) "1"
 *    }
 *    [4]=>
 *    array(8) {
 *      ["id"]=>
 *      string(0) ""
 *      ["order"]=>
 *      string(1) "2"
 *    }
 *  }
 *  ["payment_button"]=>
 *  array(1) {
 *    [3]=>
 *    array(6) {
 *      ["id"]=>
 *      string(0) ""
 *      ["order"]=>
 *      string(1) "3"
 *    }
 *  }
 *
 * Create a flat list sorted by each field's `order` key.
 *
 * @param array $custom_fields Custom fields from the database.
 * @return array Flattened and sorted custom fields.
 */
function get_custom_fields_flat( $custom_fields ) {
	$sorted_fields = array();
	$count         = 0;

	if ( ! $custom_fields || ! is_array( $custom_fields ) ) {
		return $sorted_fields;
	}

	foreach ( $custom_fields as $type => $fields ) {
		foreach ( $fields as $field ) {
			$field['type'] = $type;

			if ( ! isset( $field['order'] ) ) {
				$field['order'] = $count;
			}

			if ( 'payment_button' === $field['type'] ) {
				$field['order'] = 9999;
			}

			$sorted_fields[] = $field;
		}

		$count++;
	}

	uasort(
		$sorted_fields,
		function( $a, $b ) {
			if ( floatval( $a['order'] ) === floatval( $b['order'] ) ) {
				return 0;
			}

			return ( floatval( $a['order'] ) < floatval( $b['order'] ) ) ? -1 : 1;
		}
	);

	return $sorted_fields;
}

/**
 * Adds Lite education in the "Form Fields" payment form setting tab.
 *
 * @since 4.4.7
 *
 * @param int $post_id Current Payment Form ID.
 */
function __unstable_add_custom_fields( $post_id ) {
	$field_groups = get_custom_fields_grouped();

	if ( empty( $field_groups ) ) {
		return;
	}

	$upgrade_title = esc_html__(
		'Unlock Custom Fields',
		'stripe'
	);

	$upgrade_description = __(
		'We\'re sorry, adding custom fields is not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
		'stripe'
	);

	$upgrade_url = simpay_pro_upgrade_url(
		'form-custom-fields-settings',
		'Custom fields'
	);

	$upgrade_purchased_url = simpay_docs_link(
		'Custom fields (already purchased)',
		'upgrading-wp-simple-pay-lite-to-pro',
		'form-custom-field-settings',
		true
	);
	?>

	<table>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<label for="custom-field-select">
						<?php esc_html_e( 'Form Fields', 'stripe' ); ?>
					</label>
				</th>
				<td style="border-bottom: 0;">
					<div class="toolbar toolbar-top">
						<select class="simpay-field-select">
							<option value=""><?php esc_html_e( 'Choose a field&hellip;', 'stripe' ); ?></option>
								<?php foreach ( $field_groups as $group => $options ) : ?>
									<optgroup label="<?php echo esc_attr( $group ); ?>">
										<?php foreach ( $options as $option ) : ?>
											<option>
												<?php echo esc_html( $option['label'] ); ?>
											</option>
										<?php endforeach; ?>
									</optgroup>
								<?php endforeach; ?>
							</optgroup>
						</select>

						<button
							id="simpay-add-field-lite"
							type="button"
							class="button add-field"
							data-available="no"
							data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
							data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
							data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
							data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
						>
							<?php esc_html_e( 'Add Field', 'stripe' ); ?>
						</button>
					</div>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<td>
					<div id="simpay-custom-fields-wrap" class="panel simpay-metaboxes-wrapper">
						<div class="simpay-custom-fields simpay-metaboxes ui-sortable">
							<?php __unstable_payment_button_field( $post_id ); ?>
						</div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_meta_form_display_panel',
	__NAMESPACE__ . '\\__unstable_add_custom_fields'
);

/**
 * Outputs the "Payment Button" "custom" field for Lite.
 *
 * @since 4.4.7
 *
 * @param int $post_id Payment form ID.
 * @return void
 */
function __unstable_payment_button_field( $post_id ) {
	$counter = 1;
	$fields  = simpay_get_payment_form_setting(
		$post_id,
		'fields',
		array(),
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$payment_button = wp_list_filter(
		$fields,
		array(
			'type' => 'payment_button',
		)
	);

	$field = ! empty( $payment_button )
		? current( $payment_button )
		: array();
	?>

	<div class="postbox simpay-field-metabox simpay-metabox simpay-custom-field-payment-button" data-type="payment_button" style="border-radius: 4px;">
		<h2 class="simpay-hndle" style="padding: 10px 12px;">
			<span class="custom-field-dashicon dashicons dashicons-menu-alt2" style="cursor: move;"></span>

			<strong class="simpay-price-label-display">
				<?php esc_html_e( 'Payment Button', 'stripe' ); ?>
			</strong>
		</h2>

		<div class="simpay-field-data simpay-metabox-content inside" style="border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">
			<table>
				<tbody>
					<tr class="simpay-panel-field">
						<th>
							<label for="<?php echo esc_attr( 'simpay-payment-button-text-' . $counter ); ?>">
								<?php esc_html_e( 'Button Text', 'stripe' ); ?>
							</label>
						</th>
						<td>
							<?php
							simpay_print_field(
								array(
									'type'        => 'standard',
									'subtype'     => 'text',
									'name'        => '_simpay_custom_field[payment_button][' . $counter . '][text]',
									'id'          => 'simpay-payment-button-text-' . $counter,
									'value'       => isset( $field['text'] ) ? $field['text'] : '',
									'class'       => array(
										'simpay-field-text',
										'simpay-label-input',
									),
									'attributes'  => array(
										'data-field-key' => $counter,
									),
									'placeholder' => esc_attr__( 'Pay with Card', 'stripe' ),
								)
							);
							?>
						</td>
					</tr>

					<tr class="simpay-panel-field">
						<th>
							<label for="<?php echo esc_attr( 'simpay-processing-button-text' . $counter ); ?>">
								<?php esc_html_e( 'Button Processing Text', 'stripe' ); ?>
							</label>
						</th>
						<td>
							<?php
							simpay_print_field(
								array(
									'type'        => 'standard',
									'subtype'     => 'text',
									'name'        => '_simpay_custom_field[payment_button][' . $counter . '][processing_text]',
									'id'          => 'simpay-processing-button-text-' . $counter,
									'value'       => isset( $field['processing_text'] ) ? $field['processing_text'] : '',
									'class'       => array(
										'simpay-field-text',
										'simpay-label-input',
									),
									'attributes'  => array(
										'data-field-key' => $counter,
									),
									'placeholder' => esc_attr__( 'Please Wait...', 'stripe' ),
								)
							);
							?>
						</td>
					</tr>

					<tr class="simpay-panel-field">
						<th>
							<label for="<?php echo esc_attr( 'simpay-payment-button-style-' . $counter ); ?>">
								<?php esc_html_e( 'Button Style', 'stripe' ); ?>
							</label>
						</th>
						<td style="border-bottom: 0;">
							<?php
							simpay_print_field(
								array(
									'type'    => 'radio',
									'name'    => '_simpay_custom_field[payment_button][' . $counter . '][style]',
									'id'      => esc_attr( 'simpay-payment-button-style-' . $counter ),
									'value'   => isset( $field['style'] )
										? $field['style']
										: 'stripe',
									'class'   => array( 'simpay-multi-toggle' ),
									'options' => array(
										'stripe' => esc_html__( 'Stripe blue', 'stripe' ),
										'none'   => esc_html__( 'Default', 'stripe' ),
									),
									'inline'  => 'inline',
								)
							);
							?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<?php
}
