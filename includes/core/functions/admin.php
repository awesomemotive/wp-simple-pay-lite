<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin functions
 *
 * Functions for the admin back end components only.
 *
 * @package SimplePay/Admin/Functions
 */

/**
 * Stripe Checkout supported locales.
 *
 * @since 3.6.0
 *
 * @return array
 */
function simpay_get_stripe_checkout_locales() {
	return array(
		'auto' => esc_html__( 'Auto-detect', 'stripe' ),
		'da'   => esc_html__( 'Danish (da)', 'stripe' ),
		'nl'   => esc_html__( 'Dutch (nl)', 'stripe' ),
		'en'   => esc_html__( 'English (en)', 'stripe' ),
		'fi'   => esc_html__( 'Finnish (fi)', 'stripe' ),
		'fr'   => esc_html__( 'French (fr)', 'stripe' ),
		'de'   => esc_html__( 'German (de)', 'stripe' ),
		'it'   => esc_html__( 'Italian (it)', 'stripe' ),
		'ja'   => esc_html__( 'Japanese (ja)', 'stripe' ),
		'nb'   => esc_html__( 'Norwegian (nb)', 'stripe' ),
		'pl'   => esc_html__( 'Polish (pl)', 'stripe' ),
		'pt'   => esc_html__( 'Portuguese (pt)', 'stripe' ),
		'zh'   => esc_html__( 'Simplified Chinese (zh)', 'stripe' ),
		'es'   => esc_html__( 'Spanish (es)', 'stripe' ),
		'sv'   => esc_html__( 'Swedish (sv)', 'stripe' ),
	);
}

/**
 * Get the svg icon URL
 *
 * @return string
 */
function simpay_get_svg_icon_url() {
	return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMS4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDQ4OC4yMDEgNDg4LjIwMSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDg4LjIwMSA0ODguMjAxOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjUxMnB4IiBoZWlnaHQ9IjUxMnB4Ij4KPGc+Cgk8Zz4KCQk8cGF0aCBkPSJNMjY1LjIsMzUwLjI1MUgzMy42Yy01LjMsMC05LjYtNC4zLTkuNi05LjZ2LTE4Mi42aDQwOC41djI0YzAsNi42LDUuNCwxMiwxMiwxMnMxMi01LjQsMTItMTJ2LTg2LjUgICAgYzAtMTguNS0xNS4xLTMzLjYtMzMuNi0zMy42SDMzLjZjLTE4LjYsMC0zMy42LDE1LjEtMzMuNiwzMy42djI0NS4xYzAsMTguNSwxNS4xLDMzLjYsMzMuNiwzMy42aDIzMS43YzYuNiwwLDEyLTUuNCwxMi0xMiAgICBTMjcxLjksMzUwLjI1MSwyNjUuMiwzNTAuMjUxeiBNMzMuNiw4NS45NTFoMzg5LjNjNS4zLDAsOS42LDQuMyw5LjYsOS42djM4LjVIMjMuOXYtMzguNUMyMy45LDkwLjI1MSwyOC4zLDg1Ljk1MSwzMy42LDg1Ljk1MXoiIGZpbGw9IiNGRkZGRkYiLz4KCQk8cGF0aCBkPSJNMjQwLjIsMjQ3LjE1MWMwLTYuNi01LjQtMTItMTItMTJIODRjLTYuNiwwLTEyLDUuNC0xMiwxMnM1LjQsMTIsMTIsMTJoMTQ0LjJDMjM0LjksMjU5LjE1MSwyNDAuMiwyNTMuNzUxLDI0MC4yLDI0Ny4xNTEgICAgeiIgZmlsbD0iI0ZGRkZGRiIvPgoJCTxwYXRoIGQ9Ik04NCwyNzguMTUxYy02LjYsMC0xMiw1LjQtMTIsMTJzNS40LDEyLDEyLDEyaDU3LjdjNi42LDAsMTItNS40LDEyLTEycy01LjQtMTItMTItMTJIODR6IiBmaWxsPSIjRkZGRkZGIi8+CgkJPHBhdGggZD0iTTgyLjYsMjE1LjY1MWgxNDQuMmM2LjYsMCwxMi01LjQsMTItMTJzLTUuNC0xMi0xMi0xMkg4Mi42Yy02LjYsMC0xMiw1LjQtMTIsMTJTNzUuOSwyMTUuNjUxLDgyLjYsMjE1LjY1MXoiIGZpbGw9IiNGRkZGRkYiLz4KCQk8cGF0aCBkPSJNNDc2LjMsMjk4LjI1MWgtMTcuNnYtMjhjMC0zNC43LTI4LjMtNjMtNjMtNjNzLTYzLDI4LjMtNjMsNjN2MjhoLTE3LjZjLTYuNiwwLTEyLDUuNC0xMiwxMnYxMDRjMCw2LjYsNS40LDEyLDEyLDEyICAgIGgxNjEuMWM2LjYsMCwxMi01LjQsMTItMTJ2LTEwNEM0ODguMywzMDMuNTUxLDQ4Mi45LDI5OC4yNTEsNDc2LjMsMjk4LjI1MXogTTM1Ni43LDI3MC4xNTFjMC0yMS41LDE3LjUtMzksMzktMzlzMzksMTcuNSwzOSwzOSAgICB2MjhoLTc4VjI3MC4xNTF6IE00NjQuMyw0MDIuMTUxSDMyNy4xdi04MGgxMzcuMXY4MEg0NjQuM3oiIGZpbGw9IiNGRkZGRkYiLz4KCTwvZz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8Zz4KPC9nPgo8L3N2Zz4K';
}

/**
 * Setup the insert form button
 */
function simpay_insert_form_button() {
	global $pagenow, $typenow;
	$output = '';

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array(
			'post.php',
			'page.php',
			'post-new.php',
			'post-edit.php',
		) ) && $typenow != 'simple-pay'
	) {

		$img_url = SIMPLE_PAY_ASSETS . 'images/icon.png';
		$icon    = '<span class="wp-media-buttons-icon" id="simpay-insert-form-button"><img src="' . esc_url( $img_url ) . '" width="30" style="margin-left: -12px; margin-top: -12px;" /></span>';

		// TODO Remove image & use SVG icon eventually.

		$output = '<a href="#TB_inline?height=300&inlineId=simpay-insert-form" title="' . esc_attr__( 'Insert Payment Form', 'stripe' ) . '" class="thickbox button simpay-thickbox">' . $icon . esc_html__( 'Insert Payment Form', 'stripe' ) . '</a>';
	}

	echo $output;
}

add_action( 'media_buttons', 'simpay_insert_form_button', 11 );

/**
 * Load the JS we need for the insert form button
 */
function simpay_admin_footer_insert_form() {
	global $pagenow, $typenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array(
			'post.php',
			'page.php',
			'post-new.php',
			'post-edit.php',
		) ) && $typenow != 'simple-pay'
	) { ?>
		<script type="text/javascript">
			function insertSimpayForm() {
				var id = jQuery( '#simpay-form-list' ).val();

				// Send the shortcode to the editor
				window.send_to_editor( '[simpay id="' + id + '"]' );
			}
		</script>

		<div id="simpay-insert-form" style="display: none;">
			<div class="wrap">
				<p><?php esc_html_e( 'Select a payment form to add to your post or page.', 'stripe' ); ?></p>
				<div>
					<?php echo simpay_get_forms_list(); ?>
				</div>
				<p class="submit">
					<input type="button" id="simpay-insert-form" class="button-primary" value="<?php esc_attr_e( 'Insert Payment Form', 'stripe' ); ?>" onclick="insertSimpayForm();" />
					<a id="simpay-cancel-insert-form" class="button-secondary" onclick="tb_remove();"><?php esc_html_e( 'Cancel', 'stripe' ); ?></a>
				</p>
			</div>
		</div>
		<?php
	}
}

add_action( 'admin_footer', 'simpay_admin_footer_insert_form' );


/**
 * Get the list of Simple Pay forms
 *
 * @return string
 */
function simpay_get_forms_list() {

	$args = array(
		'numberposts' => -1,
		'post_type'   => 'simple-pay',
		'orderby'     => 'title',
		'order'       => 'ASC',
	);

	$forms = get_posts( $args );

	$options = '';

	if ( ! empty( $forms ) ) {
		foreach ( $forms as $k => $v ) {
			/* translators: (no title) is the default placed in the dropdown for the form list button on posts/pages if the form was not named */
			$options .= '<option value="' . esc_attr( $v->ID ) . '">' . ( ! empty( $v->post_title ) ? $v->post_title : esc_html__( '(no title)', 'stripe' ) ) . '</option>';
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
 * @param  string $page
 *
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
 * @param  string $func Function to use for sanitizing text strings (default 'sanitize_text_field')
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
 * Google Analytics campaign URL.
 *
 * @since   3.0.0
 *
 * @param   string $base_url Plain URL to navigate to
 * @param   string $content  GA "content" tracking value
 * @param   bool   $raw      Use esc_url_raw instead (default = false)
 *
 * @return  string  $url        Full Google Analytics campaign URL
 */
function simpay_ga_url( $base_url, $content, $raw = false ) {

	$url = add_query_arg( array(
		'utm_source'   => 'inside-plugin',
		'utm_medium'   => 'link',
		'utm_campaign' => apply_filters( 'simpay_utm_campaign', 'lite-plugin' ),
		'utm_content'  => $content // i.e. 'general-settings', 'form-settings', etc.
	), $base_url );

	if ( $raw ) {
		return esc_url_raw( $url );
	}

	return esc_url( $url );
}

/**
 * URL for upgrading to Pro used in promo links.
 */
function simpay_pro_upgrade_url( $content ) {

	return apply_filters( 'simpay_upgrade_link', simpay_ga_url(SIMPLE_PAY_STORE_URL . 'lite-vs-pro/', $content ) );
}

/**
 * Link with HTML to docs site article & GA campaign values.
 *
 * @param string $text
 * @param string $slug
 * @param string $ga_content
 * @param bool   $plain
 *
 * @return string
 */
function simpay_docs_link( $text, $slug, $ga_content, $plain = false ) {

	// Articles on docs site currently require a base slug themselves.
	$base_url = simpay_get_url( 'docs' ) . 'articles/';

	// Ensure ending slash is included for consistency.
	$url = trailingslashit( $base_url . $slug );

	// If $plain is true we want to return ONLY the link, otherwise return the full HTML.
	// Add GA campaign params in both cases.
	if ( $plain ) {

		return simpay_ga_url( $url, $ga_content, false );

	} else {

		$html = '';
		$html .= '<div class="simpay-docs-link-wrap">';
		$html .= '<a href="' . simpay_ga_url( $url, $ga_content, true ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $text );
		$html .= '<span class="dashicons dashicons-editor-help"></span>';
		$html .= '</a>';
		$html .= '</div>';

		return $html;
	}
}

/**
 * Output the copy/paste shortcode on the forms page
 */
function simpay_print_shortcode_tip( $post_id ) {


	$cmd = 'Ctrl&#43;C (&#8984;&#43;C on Mac)';

	$shortcut  = sprintf( esc_attr__( 'Click to select. Then press %s to copy.', 'stripe' ), $cmd );
	$shortcode = sprintf( '[simpay id="%s"]', $post_id );

	echo "<input type='text' readonly='readonly' id='simpay-shortcode' class='simpay-shortcode simpay-form-shortcode simpay-shortcode-tip' title='" . esc_attr( $shortcut ) . "' " . "onclick='this.select();' value='" . esc_attr( $shortcode ) . "' />";
}
