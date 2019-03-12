<?php
/**
 * Output table rows containing settings for the Company Name and Description.
 *
 * This is a portable partial that is output in a different area depending on the plugin status.
 *
 * @since 3.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$form_display_type = simpay_get_saved_meta( $post->ID, '_form_display_type', 'embedded' );
?>

<tr class="simpay-panel-field">
	<th>
		<label for="_company_name"><?php esc_html_e( 'Company Name', 'stripe' ); ?></label>
	</th>
	<td>
		<?php
		$company_name = simpay_get_saved_meta( $post->ID, '_company_name', false );

		simpay_print_field( array(
			'type'    => 'standard',
			'subtype' => 'text',
			'name'    => '_company_name',
			'id'      => '_company_name',
			'value'   => false !== $company_name ? $company_name : get_bloginfo( 'name' ),
			'class'   => array(
				'simpay-field-text',
			),
			'description' => __( 'Also used for the form heading.', 'stripe' ),
		) );
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="_item_description"><?php esc_html_e( 'Item Description', 'stripe' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field( array(
			'type'    => 'standard',
			'subtype' => 'text',
			'name'    => '_item_description',
			'id'      => '_item_description',
			'value'   => simpay_get_saved_meta( $post->ID, '_item_description' ),
			'class'   => array(
				'simpay-field-text',
			),
			'description' => __( 'Also used for the form subheading.', 'stripe' ),) );
		?>
	</td>
</tr>

<tr class="simpay-panel-field toggle-_form_display_type-stripe_checkout <?php echo 'stripe_checkout' === $form_display_type ? '' : 'simpay-panel-hidden'; ?>">
	<th></th>
	<td>
		<p class="description">
			<?php printf( wp_kses( __( 'Configure your Stripe Checkout form in the <a href="%1$s" class="%2$s" data-show-tab="%3$s">Stripe Checkout Display</a> options.', 'stripe' ), array(
				'a' => array(
					'href'          => array(),
					'class'         => array(),
					'data-show-tab' => array(),
				),
			) ), '#', 'simpay-tab-link', 'simpay-stripe_checkout' ); ?>
		</p>
	</td>
</tr>
