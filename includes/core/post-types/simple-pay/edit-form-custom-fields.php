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
		'fee_recovery_toggle' => array(
			'label'      => esc_html__( 'Fee Recovery Toggle', 'stripe' ),
			'type'       => 'fee_recovery_toggle',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => false,
		),
		'total_amount'            => array(
			'label'      => esc_html__( 'Amount Breakdown', 'stripe' ),
			'type'       => 'total_amount',
			'category'   => 'payment',
			'active'     => true,
			'repeatable' => true,
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

	if ( ! simpay_is_upe() ) {
		$fields['payment_request_button'] = array(
			'label'      => esc_html__( '1-Click Payment Button (Apple Pay / Google Pay)', 'stripe' ),
			'type'       => 'payment_request_button',
			'category'   => 'payment',
			'active'     => simpay_can_use_payment_request_button(),
			'repeatable' => false,
		);
	}

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

			if ( 'checkout_button' === $field['type'] ) {
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
	// Remove the "Customer" field group. These are mostly controlled in the
	// "Stripe Checkout" tab.
	add_filter(
		'simpay_custom_field_group_labels',
		function( $groups ) {
			unset( $groups['customer'] );
			return $groups;
		}
	);

	// Remove the "Customer" fields and update active/labels for the rest.
	add_filter(
		'simpay_custom_field_options',
		function( $fields ) {
			$fields = array_filter(
				$fields,
				function( $field ) {
					$remove = array(
						'customer_name',
						'email',
						'telephone',
						'address',
						'tax_id',
						'payment_button',
						'checkout_button',
					);

					return ! in_array( $field['type'], $remove, true );
				}
			);

			$fields = array_map(
				function( $field ) {
					$lite = array(
						'text',
						'dropdown',
						'number',
					);

					if ( ! in_array( $field['type'], $lite, true ) ) {
						$field['label'] = sprintf(
							'[Pro] %s',
							$field['label']
						);

						$field['active'] = false;
					}

					return $field;
				},
				$fields
			);

			return $fields;
		}
	);

	$field_groups = get_custom_fields_grouped();

	if ( empty( $field_groups ) ) {
		return;
	}

	$fields = simpay_get_payment_form_setting(
		$post_id,
		'fields',
		array(
			array(
				'type' => 'payment_button',
			),
		),
		__unstable_simpay_get_payment_form_template_from_url()
	);

	// Remove the "Price Selector" (plan_select) field if it was added by a template.
	$fields = array_filter(
		$fields,
		function( $field ) {
			return 'plan_select' !== $field['type'];
		}
	);

	$upgrade_title = esc_html__(
		'Unlock Additional Custom Fields',
		'stripe'
	);

	$upgrade_description = __(
		'We\'re sorry, adding additional custom fields is not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
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
						<select id="custom-field-select" class="simpay-field-select">
							<option value=""><?php esc_html_e( 'Choose a field&hellip;', 'stripe' ); ?></option>
								<?php foreach ( $field_groups as $group => $options ) : ?>
									<optgroup label="<?php echo esc_attr( $group ); ?>">
										<?php foreach ( $options as $option ) : ?>
											<option <?php disabled( false, $option['active'] ); ?>>
												<?php echo esc_html( $option['label'] ); ?>
											</option>
										<?php endforeach; ?>
									</optgroup>
								<?php endforeach; ?>
							</optgroup>
						</select>

						<button
							type="button"
							id="lite-add-field"
							class="button add-field"
							data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
							data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
							data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
							data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
						>
							<?php esc_html_e( 'Add Field', 'stripe' ); ?>
						</button>

						<?php
						wp_nonce_field(
							'simpay_custom_fields_nonce',
							'simpay_custom_fields_nonce'
						);
						?>
					</div>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<td>
					<div id="simpay-custom-fields-wrap" class="panel simpay-metaboxes-wrapper">
						<div class="simpay-custom-fields simpay-metaboxes ui-sortable">
							<?php
							foreach ( $fields as $k => $field ) :
								$counter = $k + 1;

								__unstable_get_custom_field(
									$field['type'],
									$counter,
									$field,
									$post_id
								);
							endforeach;
							?>
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
 * Outputs a custom field for Lite.
 *
 * @since 4.7.7
 *
 * @param string $type Field type.
 * @param int    $counter    Field counter.
 * @param int    $post_id    Payment form ID.
 */
function __unstable_get_custom_field( $type, $counter, $field, $post_id ) {
	$field_types = get_custom_field_types();
	$fields      = simpay_get_payment_form_setting(
		$post_id,
		'fields',
		array(),
		__unstable_simpay_get_payment_form_template_from_url()
	);

	// Remove the "Price Selector" (plan_select) field if it was added by a template.
	$fields = array_filter(
		$fields,
		function( $field ) {
			return 'plan_select' !== $field['type'];
		}
	);

	$settings = sprintf(
		'%s/core/post-types/simple-pay/edit-form-custom-fields/custom-fields-%s-html.php',
		SIMPLE_PAY_INC,
		simpay_dashify( $type )
	);

	$only_field = count( $fields ) === 1;

	$uid = isset( $field['uid'] ) ? $field['uid'] : $counter;

	switch ( $type ) {
		case 'payment_button':
			$type_label = esc_html__( 'Payment Button', 'stripe' );
			break;
		default:
			$type_label = $field_types[ $type ]['label'];
	}
	?>

	<div
		class="
			postbox
			simpay-field-metabox
			simpay-metabox
			simpay-custom-field-<?php echo esc_attr( simpay_dashify( $type ) ); ?>
			<?php if ( ! $only_field ) : ?>
				closed
			<?php endif; ?>
		"
		data-type="<?php echo esc_attr( $type ); ?>"
		<?php if ( ! $only_field ) : ?>
		aria-expanded="false"
		<?php endif; ?>
	>
		<button type="button" class="simpay-handlediv">
			<span class="toggle-indicator" aria-hidden="true"></span>
		</button>

		<h2 class="simpay-hndle ui-sortable-handle">
			<span class="custom-field-dashicon dashicons <?php echo 'payment_button' !== $type ? 'dashicons-menu-alt2" style="cursor: move;' : 'dashicons-lock" style="color: #ccc;'; ?>"></span>

			<strong class="simpay-price-label-display">
				<?php echo esc_attr( $type_label ); ?>
			</strong>
		</h2>

		<div class="simpay-field-data simpay-metabox-content inside">
			<table>
				<?php
				simpay_print_field(
					array(
						'type'    => 'standard',
						'subtype' => 'hidden',
						'name'    => '_simpay_custom_field[' . $type . '][' . $counter . '][id]',
						'id'      => 'simpay-' . $type . '-' . $counter . '-id',
						'value'   => ! empty( $field['id'] ) ? $field['id'] : $uid,
					)
				);

				simpay_print_field(
					array(
						'type'    => 'standard',
						'subtype' => 'hidden',
						'id'      => 'simpay-' . $type . '-' . $counter . '-uid',
						'class'   => array( 'field-uid' ),
						'name'    => '_simpay_custom_field[' . $type . '][' . $counter . '][uid]',
						'value'   => $uid,
					)
				);

				include $settings;
				?>
			</table>

			<?php if ( 'payment_button' !== $type ) : ?>
			<div class="simpay-metabox-content-actions" style="padding: 14px 18px;">
				<button type="button" class="button-link simpay-remove-field-link">
					<?php esc_html_e( 'Remove', 'stripe' ); ?>
				</button>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<?php
}
