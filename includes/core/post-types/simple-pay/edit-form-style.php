<?php
/**
 * Form Builder: Form Style
 *
 * @package SimplePay\Core
 * @since 4.17.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds "Form Style" form builder tab content.
 *
 * @since 4.17.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_form_style( $post_id ) {
	do_action( 'simpay_form_style_panel', $post_id );
}

add_action( 'simpay_form_settings_form_style_panel', __NAMESPACE__ . '\add_form_style' );
