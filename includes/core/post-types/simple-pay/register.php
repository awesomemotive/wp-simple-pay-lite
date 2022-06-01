<?php
/**
 * Post Types: Simple Pay
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers `simple-pay` post type.
 *
 * @since 3.8.0
 */
function register() {
	/**
	 * Allows other Post Types to be registered.
	 *
	 * @todo Deprecate. Use core actions.
	 *
	 * @since 3.0.0
	 */
	do_action( 'simpay_register_post_types' );

	if ( true === post_type_exists( 'simple-pay' ) ) {
		return;
	}

	$menu_name = esc_html_x( 'WP Simple Pay', 'post type general name', 'stripe' );

	/**
	 * Filters the text used for the primary WP Simple Pay menu item.
	 *
	 * @since 3.0.0
	 *
	 * @param string $menu_name Menu text.
	 */
	$menu_name = apply_filters( 'simpay_menu_title', $menu_name );

	$labels = array(
		'menu_name'                => $menu_name,
		'name'                     => esc_html__( 'Payment Forms', 'stripe' ),
		'singular_name'            => esc_html_x( 'Payment Form', 'post type singular name', 'stripe' ),
		'add_new'                  => esc_html_x( 'Add New', 'event', 'stripe' ),
		'add_new_item'             => esc_html__( 'Add New Payment Form', 'stripe' ),
		'edit_item'                => esc_html__( 'Edit Payment Form', 'stripe' ),
		'new_item'                 => esc_html__( 'New Payment Form', 'stripe' ),
		'view_item'                => esc_html__( 'Preview Payment Form', 'stripe' ),
		'view_items'               => esc_html__( 'Preview Payment Forms', 'stripe' ),
		'search_items'             => esc_html__( 'Search Payment Forms', 'stripe' ),
		'not_found'                => esc_html__( 'No payment forms found.', 'stripe' ),
		'not_found_in_trash'       => esc_html__( 'No payment forms found in trash.', 'stripe' ),
		'parent_item_colon'        => esc_html__( 'Parent Payment Form:', 'stripe' ),
		'all_items'                => esc_html__( 'Payment Forms', 'stripe' ),
		'archives'                 => esc_html__( 'Payment Form Archives', 'stripe' ),
		'attributes'               => esc_html__( 'Payment Form Attributes', 'stripe' ),
		'insert_into_item'         => esc_html__( 'Insert Into Payment Form', 'stripe' ),
		'uploaded_to_this_item'    => esc_html__( 'Uploaded to this payment form', 'stripe' ),
		'featured_image'           => esc_html__( 'Featured Image', 'stripe' ),
		'set_featured_image'       => esc_html__( 'Set featured image', 'stripe' ),
		'remove_featured_image'    => esc_html__( 'Remove featured image', 'stripe' ),
		'use_featured_image'       => esc_html__( 'Use as featured image', 'stripe' ),
		'filter_items_list'        => esc_html__( 'Filter payment form list', 'stripe' ),
		'items_list_navigation'    => esc_html__( 'Payment Forms list navigation', 'stripe' ),
		'items_list'               => esc_html__( 'Payment Forms list', 'stripe' ),
		'item_published'           => esc_html__( 'Payment Form created', 'stripe' ),
		'item_published_privately' => esc_html__( 'Private payment form created.', 'stripe' ),
		'item_reverted_to_draft'   => esc_html__( 'Payment Form reverted to draft.', 'stripe' ),
		'item_scheduled'           => esc_html__( 'Payment Form scheduled.', 'stripe' ),
		'item_updated'             => esc_html__( 'Payment Form updated.', 'stripe' ),
	);

	/**
	 * Filters the labels used to register the `simple-pay` post type.
	 *
	 * @since 3.8.0
	 *
	 * @param array $labels Post type labels.
	 */
	$labels = apply_filters( 'simpay_simple-pay_post_type_labels', $labels );

	$args = array(
		'labels'               => $labels,
		'menu_icon'            => \simpay_get_svg_icon_url(),
		'public'               => false,
		'publicly_queryable'   => false,
		'exclude_from_search'  => true,
		'show_ui'              => true,
		'show_in_menu'         => true,
		'show_in_nav_menus'    => false,
		'show_in_rest'         => true,
		'show_in_admin_bar'    => true,
		'archive_in_nav_menus' => false,
		'query_var'            => 'simpay-preview',
		'rewrite'              => false,
		'capability_type'      => 'post',
		'map_meta_cap'         => true,
		'has_archive'          => false,
		'hierarchical'         => false,
		'supports'             => array(
			'title',
		),
	);

	register_post_type( 'simple-pay', $args );
}
add_action( 'init', __NAMESPACE__ . '\\register' );

/**
 * Registers additional fields for the REST API response.
 *
 * @since 4.4.2
 *
 * @return void
 */
function add_rest_fields() {
	register_rest_field(
		'simple-pay',
		'payment_form_title',
		array(
			'get_callback' => function( $payment_form ) {
				return get_post_meta( $payment_form['id'], '_company_name', true );
			},
			'schema'       => array(
				'type' => 'string',
			)
		)
	);

	register_rest_field(
		'simple-pay',
		'payment_form_description',
		array(
			'get_callback' => function( $payment_form ) {
				return get_post_meta( $payment_form['id'], '_item_description', true );
			},
			'schema'       => array(
				'type' => 'string',
			)
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\\add_rest_fields' );

/**
 * Filters the Post's (Payment Form) preview link.
 *
 * @since 3.8.0
 *
 * @param string   $link Permalink.
 * @param \WP_Post $post Current Payment Form (post) object.
 * @return string
 */
function previewlink( $link, $post ) {
	if ( 'simple-pay' !== $post->post_type ) {
		return $link;
	}

	$link = add_query_arg(
		array(
			'simpay-preview' => $post->ID,
		),
		home_url()
	);

	return $link;
}
add_filter( 'preview_post_link', __NAMESPACE__ . '\\previewlink', 10, 2 );

/**
 * Filters the Post's (Payment Form) link.
 *
 * @since 3.8.0
 *
 * @param string   $link Permalink.
 * @param \WP_Post $post Current Payment Form (post) object.
 * @return string
 */
function permalink( $link, $post ) {
	return previewlink( $link, $post );
}
add_filter( 'post_type_link', __NAMESPACE__ . '\\permalink', 10, 2 );

/**
 * Adds messages for general actions.
 *
 * @since 3.8.0
 *
 * @param array $messages Payment Form messages.
 * @return array $messages Payment Form mesages.
 */
function updated_messages( $messages ) {
	global $post;

	$open  = '<a href="' . get_permalink( $post->ID ) . '" target="_blank" rel="noopener noreferrer">';
	$close = '</a>';

	$messages['simple-pay'] = array(
		1   => sprintf(
			/* translators: %1$s Opening anchor, do not translate. %2$s Closing anchor, do not translate. */
			__( 'Payment form updated. %1$sPreview payment form%2$s.', 'stripe' ),
			$open,
			$close
		),
		4   => sprintf(
			/* translators: %1$s Opening anchor, do not translate. %2$s Closing anchor, do not translate. */
			__( 'Payment form updated. %1$sPreview payment form%2$s.', 'stripe' ),
			$open,
			$close
		),
		6   => sprintf(
			/* translators: %1$s Opening anchor, do not translate. %2$s Closing anchor, do not translate. */
			__( 'Payment form published. %1$sPreview payment form%2$s.', 'stripe' ),
			$open,
			$close
		),
		7   => sprintf(
			/* translators: %1$s Opening anchor, do not translate. %2$s Closing anchor, do not translate. */
			__( 'Payment form saved. %1$sPreview payment form%2$s.', 'stripe' ),
			$open,
			$close
		),
		8   => sprintf(
			/* translators: %1$s Opening anchor, do not translate. %2$s Closing anchor, do not translate. */
			__( 'Payment form submitted. %1$sPreview payment form%2$s.', 'stripe' ),
			$open,
			$close
		),
		299 => __( 'New payment form created.', 'stripe' ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', __NAMESPACE__ . '\\updated_messages' );

/**
 * Adds messages for Bulk Update actions.
 *
 * @since 3.8.0
 *
 * @param array $bulk_messages Payment Form updated messages.
 * @param array $bulk_counts Payment Form counts.
 * @return array $bulk_messages New Payment Form updated messages.
 */
function bulk_updated_messages( $bulk_messages, $bulk_counts ) {
	$bulk_messages['simple-pay'] = array(
		'updated'   => sprintf(
			/* translators: %1$s count. */
			_n( '%1$s payment form updated.', '%1$s payment forms updated.', $bulk_counts['updated'], 'stripe' ),
			$bulk_counts['updated']
		),
		'locked'    => sprintf(
			/* translators: %1$s count. */
			_n( '%1$s payment form not updated, somebody is editing it.', '%1$s payment forms not updated, somebody is editing them.', $bulk_counts['locked'], 'stripe' ),
			$bulk_counts['locked']
		),
		'deleted'   => sprintf(
			/* translators: %1$s count. */
			_n( '%1$s payment form permanently deleted.', '%1$s payment forms permanently deleted.', $bulk_counts['deleted'], 'stripe' ),
			$bulk_counts['deleted']
		),
		'trashed'   => sprintf(
			/* translators: %1$s count. */
			_n( '%1$s payment forms moved to the Trash.', '%1$s payment forms moved to the Trash.', $bulk_counts['trashed'], 'stripe' ),
			$bulk_counts['trashed']
		),
		'untrashed' => sprintf(
			/* translators: %1$s count. */
			_n( '%1$s payment form restored from the Trash.', '%1$s payment forms restored from the Trash.', $bulk_counts['untrashed'], 'stripe' ),
			$bulk_counts['untrashed']
		),
	);

	return $bulk_messages;
}
add_filter( 'bulk_post_updated_messages', __NAMESPACE__ . '\\bulk_updated_messages', 10, 2 );

/**
 * Remove non-WP Simple Pay metaboxes from the edit screen.
 *
 * @since 4.4.5
 *
 * @param string $post_type Current post type.
 * @return void
 */
function remove_other_metaboxes( $post_type ){
	if ( 'simple-pay' !== $post_type ) {
		return;
	}

	$keep = array(
		'submitdiv',
		'simpay-payment-form-settings'
	);

	global $wp_meta_boxes;

	foreach ( $wp_meta_boxes['simple-pay'] as $context_id => $contexts ) {
		foreach ( $contexts as $priorities ) {
			foreach ( $priorities as $metabox ) {
				if ( ! $metabox ) {
					continue;
				}

				if ( ! in_array( $metabox['id'], $keep, true ) ) {
					remove_meta_box( $metabox['id'], 'simple-pay', $context_id );
				}
			}
		}
	}
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\\remove_other_metaboxes', 99 );
