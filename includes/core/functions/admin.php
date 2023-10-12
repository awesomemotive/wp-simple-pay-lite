<?php
/**
 * Functions: Admin
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Checkout supported locales.
 *
 * @since 3.6.0
 *
 * @return array
 */
function simpay_get_stripe_checkout_locales() {
	return SimplePay\Core\i18n\get_stripe_checkout_locales();
}

/**
 * Setup the insert form button
 */
function simpay_insert_form_button() {
	// Do not run if in Elementor context.
	if ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) {
		return;
	}

	global $pagenow, $typenow;

	$allowed_pages = array(
		'post.php',
		'page.php',
		'post-new.php',
		'post-edit.php',
	);

	// Only run in post/page creation and edit screens.
	if ( ! in_array( $pagenow, $allowed_pages, true ) || 'simpay_form' === $typenow ) {
		return;
	}

	$icon = sprintf(
		'<span class="wp-media-buttons-icon" id="simpay-insert-form-button"><img src="%s" width="30" style="margin-left: -12px; margin-top: -7px;" /></span>',
		esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/icon.png' )
	);

	printf(
		'<a href="#TB_inline?height=300&inlineId=simpay-insert-form" title="%1$s" class="thickbox button simpay-thickbox">%2$s</a>',
		esc_attr__( 'Insert Payment Form', 'stripe' ),
		$icon . esc_html__( 'Insert Payment Form', 'stripe' )
	); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
}
add_action( 'media_buttons', 'simpay_insert_form_button', 11 );

/**
 * Load the JS we need for the insert form button
 */
function simpay_admin_footer_insert_form() {
	// Do not run if in Elementor context.
	if ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) {
		return;
	}

	global $pagenow, $typenow;

	$allowed_pages = array(
		'post.php',
		'page.php',
		'post-new.php',
		'post-edit.php',
	);

	// Only run in post/page creation and edit screens.
	if ( ! in_array( $pagenow, $allowed_pages, true ) || 'simpay_form' === $typenow ) {
		return;
	}

	$forms = simpay_get_form_list_options();

	$add_new_url = add_query_arg(
		array(
			'post_type' => 'simple-pay',
		),
		admin_url( 'post-new.php' )
	);
	?>
		<script type="text/javascript">
			function insertSimpayForm() {
				var id = jQuery( '#simpay-form-list' ).val();

				// Send the shortcode to the editor
				window.send_to_editor( '[simpay id="' + id + '"]' );
			}
		</script>

		<div id="simpay-insert-form" style="display: none;">
			<div class="wrap">
				<?php if ( empty( $forms ) ) : ?>
				You have not created any payment forms. Would you like to <a href="<?php echo esc_url( $add_new_url ); ?>">create one</a>?
					<p class="submit">
						<a id="simpay-cancel-insert-form" class="button-secondary" onclick="tb_remove();"><?php esc_html_e( 'Cancel', 'stripe' ); ?></a>
					</p>
				<?php else : ?>
				<p><?php esc_html_e( 'Select a payment form to add to your post or page.', 'stripe' ); ?></p>
				<div>
					<?php echo simpay_get_forms_list(); ?>
				</div>
				<p class="submit">
					<input type="button" id="simpay-insert-form" class="button-primary" value="<?php esc_attr_e( 'Insert Payment Form', 'stripe' ); ?>" onclick="insertSimpayForm();" />
					<a id="simpay-cancel-insert-form" class="button-secondary" onclick="tb_remove();"><?php esc_html_e( 'Cancel', 'stripe' ); ?></a>
				</p>
				<?php endif; ?>
			</div>
		</div>
	<?php
}
add_action( 'admin_footer', 'simpay_admin_footer_insert_form' );


/**
 * Get the list of Simple Pay forms
 *
 * @return string
 */
function simpay_get_forms_list() {
	$forms = simpay_get_form_list_options();

	$options = '';

	if ( ! empty( $forms ) ) {
		foreach ( $forms as $form_id => $form_title ) {
			$options .= sprintf(
				'<option value="%1$s">%2$s</option>',
				esc_attr( $form_id ),
				esc_html( $form_title )
			);
		}
	}
	return '<select id="simpay-form-list">' . $options . '</select>';
}

/**
 * Get settings pages and tabs.
 *
 * @since  3.0.0
 *
 * @return array
 */
function simpay_get_admin_pages() {
	$objects = \SimplePay\Core\SimplePay()->objects;

	return $objects instanceof \SimplePay\Core\Objects ? $objects->get_admin_pages() : array();
}

/**
 * Get a settings page tab.
 *
 * @since  3.0.0
 *
 * @param string $page Admin page slug.
 * @return null|\SimplePay\Core\Abstracts\Admin_Page
 */
function simpay_get_admin_page( $page ) {
	$objects = \SimplePay\Core\SimplePay()->objects;

	return $objects instanceof \SimplePay\Core\Objects ? $objects->get_admin_page( $page ) : null;
}

/**
 * Sanitize a variable of unknown type.
 *
 * Recursive helper function to sanitize a variable from input,
 * which could also be a multidimensional array of variable depth.
 *
 * @since  3.0.0
 *
 * @param  mixed  $var  Variable to sanitize.
 * @param  string $func Function to use for sanitizing text strings (default 'sanitize_text_field').
 *
 * @return array|string Sanitized variable
 */
function simpay_sanitize_input( $var, $func = 'sanitize_text_field' ) {

	if ( is_null( $var ) ) {
		return '';
	}

	if ( is_bool( $var ) ) {
		if ( $var === true ) {
			return 'yes';
		} else {
			return 'no';
		}
	}

	if ( is_string( $var ) || is_numeric( $var ) ) {
		$func = is_string( $func ) && function_exists( $func ) ? $func : 'sanitize_text_field';

		return call_user_func( $func, trim( strval( $var ) ) );
	}

	if ( is_object( $var ) ) {
		$var = (array) $var;
	}

	if ( ! empty( $var ) && is_array( $var ) ) {
		$array = array();
		foreach ( $var as $k => $v ) {
			$array[ $k ] = simpay_sanitize_input( $v );
		}

		return $array;
	}

	return '';
}

/**
 * Check if a screen is a plugin admin view.
 * Returns the screen id if true, false (bool) if not.
 *
 * @since  3.0.0
 *
 * @return string|bool
 */
function simpay_is_admin_screen() {
	$screen = \get_current_screen();

	if (
		'simple-pay' === $screen->post_type ||
		'edit.php?post_type=simple-pay' === $screen->parent_file
	) {
		return true;
	}

	// Check if we are on the "Site Health" page with the "WP Simple Pay" tab active.
	if (
		'site-health' === $screen->base &&
		isset( $_GET['simpay'] )
	) {
		return true;
	}

	if ( isset( $_GET['page'] ) ) {
		if ( 'simpay' == $_GET['page'] ) {
			return 'simpay';
		}

		if ( 'simpay_settings' == $_GET['page'] ) {
			return 'simpay_settings';
		}

		if ( 'simpay_system_status' == $_GET['page'] ) {
			return 'simpay_system_status';
		}
	}

	return false;
}

/**
 * Output the copy/paste shortcode on the forms page.
 *
 * @since 3.0.0
 *
 * @param int    $post_id Payment Form ID.
 * @param string $copy_button_text Text for copy button. Default empty, do not show.
 * @return void
 */
function simpay_print_shortcode_tip( $post_id, $copy_button_text = '' ) {
	$shortcut = __(
		'Click to select. Then press Ctrl&#43;C (&#8984;&#43;C on Mac) to copy.',
		'stripe'
	);

	$shortcode = sprintf( '[simpay id="%s"]', $post_id );

	printf(
		'<textarea type="text" readonly="readonly" id="simpay-shortcode-%1$s" class="simpay-shortcode simpay-form-shortcode simpay-shortcode-tip" title="%2$s">%3$s</textarea>',
		esc_attr( $post_id ),
		esc_attr( $shortcut ),
		esc_attr( $shortcode )
	);

	if ( ! empty( $copy_button_text ) ) {
		printf(
			'<button type="button" class="button button-secondary simpay-copy-button" data-copied="%1$s" data-clipboard-target="%2$s">%3$s</button>',
			esc_attr__( 'Copied!', 'stripe' ),
			sprintf( '#simpay-shortcode-%d', $post_id ),
			wp_kses(
				$copy_button_text,
				array(
					'span' => array(
						'class' => true,
						'style' => true,
					),
				)
			)
		);
	}
}

/**
 * Returns the description for Form Field Label.
 *
 * @since 3.8.0
 *
 * @return string
 */
function simpay_form_field_label_description() {
	return esc_html__( 'A text label displayed above the field.', 'stripe' );
}

/**
 * Returns the description for Placeholder.
 *
 * @since 3.8.0
 *
 * @return string
 */
function simpay_placeholder_description() {
	return esc_html__(
		'A short hint shown when the field is empty.',
		'stripe'
	);
}

/**
 * Returns a string explaining the `required` HTML attribute.
 *
 * @since 3.8.0
 *
 * @return string
 */
function simpay_required_field_description() {
	return esc_html__(
		'Determines if the field must be filled out before submitting the payment form.',
		'stripe'
	);
}

/**
 * Returns the description for Stripe Metadata Label
 *
 * @since 3.8.0
 *
 * @return string
 */
function simpay_metadata_label_description() {
	return esc_html__(
		'Used to identify this field within Stripe payment records. Not displayed on the payment form.',
		'stripe'
	);
}
