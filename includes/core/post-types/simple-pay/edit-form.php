<?php
/**
 * Simple Pay: Edit form
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

use SimplePay\Core\reCAPTCHA;
use SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs the Payment Form's Preview action in the Publishing actions.
 *
 * The post type is not public so this must be added manually.
 *
 * @since 3.8.0
 *
 * @param \WP_Post $post Current Payment Form \WP_Post object.
 */
function add_preview_action( $post ) {
	// Bail if we are not editing a Payment Form.
	if ( 'simple-pay' !== $post->post_type ) {
		return;
	}

	if ( 'auto-draft' === $post->post_status ) {
		return;
	}

	$preview_link   = esc_url( get_preview_post_link( $post ) );
	$preview_button = sprintf(
		'%1$s<span class="screen-reader-text"> %2$s</span>',
		__( 'Preview', 'stripe' ),
		/* translators: Accessibility text. */
		__( '(opens in a new tab)', 'stripe' )
	);
	?>
	<div id="preview-action">
		<a
			class="preview button"
			href="<?php echo esc_url( $preview_link ); ?>"
			target="wp-preview-<?php echo esc_attr( (int) $post->ID ); ?>"
			id="post-preview"
		>
			<?php echo $preview_button; // WPCS: XSS okay. ?>
		</a>
		<input type="hidden" name="wp-preview" id="wp-preview" value="" />
	</div>
	<?php
}
add_action( 'post_submitbox_minor_actions', __NAMESPACE__ . '\\add_preview_action' );

/**
 * Outputs the Payment Form's shortcode in the Publishing actions.
 *
 * @since 3.8.0
 *
 * @param \WP_Post $post Current Payment Form \WP_Post object.
 */
function add_shortcode_action( $post ) {
	// Bail if we are not editing a Payment Form.
	if ( 'simple-pay' !== $post->post_type ) {
		return;
	}
	?>

	<div class="misc-pub-section simpay-shortcode-section">
		<label for="simpay-shortcode">
			<span class="dashicons dashicons-shortcode"></span>
			<?php esc_html_e( 'Form Shortcode', 'stripe' ); ?>
		</label>

		<?php
		simpay_print_shortcode_tip(
			$post->ID,
			'<span class="dashicons dashicons-clipboard"></span>'
		);
		?>
	</div>

	<?php
}
add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\\add_shortcode_action' );

/**
 * Redirects to the preview during the "Save and Preview" action.
 *
 * @since 4.4.3
 *
 * @param string $location Redirect location.
 * @param int    $post_id Post ID.
 */
function redirect_post_save_preview( $location, $post_id ) {
	if (
		! isset( $_POST['simpay_save_preview'] ) ||
		'preview' !== sanitize_text_field( $_POST['simpay_save_preview'] )
	) {
		return $location;
	}

	$post_type = get_post_type( $post_id );

	if ( 'simple-pay' !== $post_type ) {
		return $location;
	}

	return esc_url( get_preview_post_link( $post_id ) );
}
add_filter(
	'redirect_post_location',
	__NAMESPACE__ . '\\redirect_post_save_preview',
	10,
	2
);

/**
 * Adds the Payment Form settings metabox.
 *
 * @since 3.8.0
 *
 * @param string   $post_type    Current post type.
 * @param \WP_Post $post Current Payment Form \WP_Post object.
 */
function add_form_settings( $post_type, $post ) {
	// Bail if we are not editing a Payment Form.
	if ( 'simple-pay' !== $post_type ) {
		return;
	}

	add_meta_box(
		'simpay-payment-form-settings',
		esc_html__( 'Payment Form', 'stripe' ),
		__NAMESPACE__ . '\\get_form_settings',
		'simple-pay',
		'normal',
		'default',
		array(
			$post,
		)
	);
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\\add_form_settings', 10, 2 );

/**
 * Outputs the Payment Form's settings under the Title input.
 *
 * Previously tab content was loaded via partial view files.
 * Since 3.8.0 the tab content is attached via actions.
 *
 * @since 3.8.0
 *
 * @param \WP_Post $post Current Payment Form \WP_Post object.
 */
function get_form_settings( $post ) {
	// Bail if we are not editing a Payment Form.
	if ( 'simple-pay' !== $post->post_type ) {
		return;
	}

	wp_enqueue_script( 'postboxes' );

	// Backwards compat.
	wp_nonce_field( 'simpay_save_data', 'simpay_meta_nonce' );

	$panel_classes = array(
		'simpay-panel',
		'simpay-panel-hidden',
	);
	?>

<style>
.page-title-action { display: none; }
</style>

<div id="simpay-form-settings">
	<div class="simpay-panels-wrap">
		<input type="hidden" name="simpay_form_id" value="<?php echo esc_attr( $post->ID ); ?>" />
		<input type="hidden" name="simpay_form_settings_tab" value="#form-display-options-settings-panel" />

		<ul class="simpay-tabs">
			<?php settings_tabs( $post ); ?>
		</ul>

		<div class="simpay-panels">
			<span class="spinner is-active" style="margin: 0 auto; align-self: center;"></span>

			<div
				id="payment-options-settings-panel"
				class="<?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				$payment_options_template = '';

				/**
				 * Filters the template file to use for the "Payment Options" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param string $payment_options_template Path to the settings tab panel template.
				 */
				$payment_options_template = apply_filters( 'simpay_payment_options_template', $payment_options_template );

				if ( file_exists( $payment_options_template ) ) {
					include_once $payment_options_template;
				}

				/**
				 * Allows further output after the "Payment Options" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action( 'simpay_form_settings_meta_payment_options_panel', $post->ID );
				?>
			</div>

			<div
				id="form-display-options-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				$form_display_options_template = '';

				/**
				 * Filters the template file to use for the "Form Display Options" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param string $form_display_options_template Path to the settings tab panel template.
				 */
				$form_display_options_template = apply_filters( 'simpay_form_options_template', $form_display_options_template );

				if ( file_exists( $form_display_options_template ) ) {
					include $form_display_options_template;
				}

				/**
				 * Allows further output after the "Form Display Options" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.8.0
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action( 'simpay_form_settings_display_options_panel', $post->ID );
				?>
			</div>

			<div
				id="custom-form-fields-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				$form_display_template = '';

				/**
				 * Filters the template file to use for the "Custom Form Fields" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param string $form_display_template Path to the settings tab panel template.
				 */
				$form_display_template = apply_filters( 'simpay_form_display_template', $form_display_template );

				if ( file_exists( $form_display_template ) ) {
					include_once $form_display_template;
				}

				/**
				 * Allows further output after the "Custom Form Fields" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action( 'simpay_form_settings_meta_form_display_panel', $post->ID );
				?>
			</div>

			<div
				id="stripe-checkout-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				$stripe_checkout_template = '';

				/**
				 * Filters the template file to use for the "Stripe Checkout Display" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param string $stripe_checkout_template Path to the settings tab panel template.
				 */
				$stripe_checkout_template = apply_filters( 'simpay_stripe_checkout_template', $stripe_checkout_template );

				if ( file_exists( $stripe_checkout_template ) ) {
					include_once $stripe_checkout_template;
				}

				/**
				 * Allows further output after the "Stripe Checkout Display" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action( 'simpay_form_settings_meta_stripe_checkout_panel', $post->ID );
				?>
			</div>

			<div
				id="subscription-options-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				$subscription_options_template = '';

				/**
				 * Filters the template file to use for the "Subscription Options" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param string $stripe_checkout_template Path to the settings tab panel template.
				 */
				$subscription_options_template = apply_filters( 'simpay_subscription_options_template', $subscription_options_template );

				if ( file_exists( $subscription_options_template ) ) {
					include_once( $subscription_options_template );
				}

				/**
				 * Allows further output after the "Subscription Options" Payment Form
				 * settings tab panel.
				 *
				 * @since 3.0.0
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action( 'simpay_form_settings_meta_subscription_display_panel', $post->ID );
				?>
			</div>

			<?php
			/**
			 * Allows further output after all Payment Form settings tab panels.
			 *
			 * @since 3.0.0
			 *
			 * @param int $form_id Current Payment Form ID.
			 */
			do_action( 'simpay_form_settings_meta_options_panel', $post->ID );
			?>
		</div>
	</div>
</div>

	<?php
}

/**
 * Outputs the tabs for the Payment Form settings.
 *
 * @since 3.8.0
 *
 * @param WP_Post $post Payment Form \WP_Post object.
 */
function settings_tabs( $post ) {
	$tabs = array(
		'form_display_options' => array(
			'label'  => esc_html__( 'General', 'stripe' ),
			'target' => 'form-display-options-settings-panel',
			'icon'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12,16c2.206,0,4-1.794,4-4s-1.794-4-4-4s-4,1.794-4,4S9.794,16,12,16z M12,10c1.084,0,2,0.916,2,2s-0.916,2-2,2 s-2-0.916-2-2S10.916,10,12,10z"></path><path d="M2.845,16.136l1,1.73c0.531,0.917,1.809,1.261,2.73,0.73l0.529-0.306C7.686,18.747,8.325,19.122,9,19.402V20 c0,1.103,0.897,2,2,2h2c1.103,0,2-0.897,2-2v-0.598c0.675-0.28,1.314-0.655,1.896-1.111l0.529,0.306 c0.923,0.53,2.198,0.188,2.731-0.731l0.999-1.729c0.552-0.955,0.224-2.181-0.731-2.732l-0.505-0.292C19.973,12.742,20,12.371,20,12 s-0.027-0.743-0.081-1.111l0.505-0.292c0.955-0.552,1.283-1.777,0.731-2.732l-0.999-1.729c-0.531-0.92-1.808-1.265-2.731-0.732 l-0.529,0.306C16.314,5.253,15.675,4.878,15,4.598V4c0-1.103-0.897-2-2-2h-2C9.897,2,9,2.897,9,4v0.598 c-0.675,0.28-1.314,0.655-1.896,1.111L6.575,5.403c-0.924-0.531-2.2-0.187-2.731,0.732L2.845,7.864 c-0.552,0.955-0.224,2.181,0.731,2.732l0.505,0.292C4.027,11.257,4,11.629,4,12s0.027,0.742,0.081,1.111l-0.505,0.292 C2.621,13.955,2.293,15.181,2.845,16.136z M6.171,13.378C6.058,12.925,6,12.461,6,12c0-0.462,0.058-0.926,0.17-1.378 c0.108-0.433-0.083-0.885-0.47-1.108L4.577,8.864l0.998-1.729L6.72,7.797c0.384,0.221,0.867,0.165,1.188-0.142 c0.683-0.647,1.507-1.131,2.384-1.399C10.713,6.128,11,5.739,11,5.3V4h2v1.3c0,0.439,0.287,0.828,0.708,0.956 c0.877,0.269,1.701,0.752,2.384,1.399c0.321,0.307,0.806,0.362,1.188,0.142l1.144-0.661l1,1.729L18.3,9.514 c-0.387,0.224-0.578,0.676-0.47,1.108C17.942,11.074,18,11.538,18,12c0,0.461-0.058,0.925-0.171,1.378 c-0.107,0.433,0.084,0.885,0.471,1.108l1.123,0.649l-0.998,1.729l-1.145-0.661c-0.383-0.221-0.867-0.166-1.188,0.142 c-0.683,0.647-1.507,1.131-2.384,1.399C13.287,17.872,13,18.261,13,18.7l0.002,1.3H11v-1.3c0-0.439-0.287-0.828-0.708-0.956 c-0.877-0.269-1.701-0.752-2.384-1.399c-0.19-0.182-0.438-0.275-0.688-0.275c-0.172,0-0.344,0.044-0.5,0.134l-1.144,0.662l-1-1.729 L5.7,14.486C6.087,14.263,6.278,13.811,6.171,13.378z"></path></svg>',
		),
		'payment_options'      => array(
			'label'  => esc_html__( 'Payment', 'stripe' ),
			'target' => 'payment-options-settings-panel',
			'icon'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20,4H4C2.897,4,2,4.897,2,6v12c0,1.103,0.897,2,2,2h16c1.103,0,2-0.897,2-2V6C22,4.897,21.103,4,20,4z M4,6h16v2H4V6z M4,18v-6h16.001l0.001,6H4z"></path><path d="M6 14H12V16H6z"></path></svg>',
		),
	);

	$tabs['form_display'] = array(
		'label'  => esc_html__( 'Form Fields', 'stripe' ),
		'target' => 'custom-form-fields-settings-panel',
		'icon'   => '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>',
	);

	if ( has_action( 'simpay_form_settings_meta_subscription_display_panel' ) ) {
		$tabs['subscription_options'] = array(
			'label'  => esc_html__( 'Subscription Options', 'stripe' ),
			'target' => 'subscription-options-settings-panel',
		);
	}

	$tabs['stripe_checkout'] = array(
		'label'  => esc_html__( 'Stripe Checkout', 'stripe' ),
		'target' => 'stripe-checkout-settings-panel',
		'icon'   => '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
	);

	/**
	 * Filters the tabs for the Payment Form settings.
	 *
	 * @since 3.0.0
	 *
	 * @param array $tabs    Payment Form settings tabs.
	 * @param int   $post_id Current Payment Form ID.
	 */
	$tabs = apply_filters( 'simpay_form_settings_meta_tabs_li', $tabs, $post->ID );

	if ( empty( $tabs ) ) {
		return;
	}

	foreach ( $tabs as $key => $tab ) {
		if ( ! isset( $tab['target'] ) || empty( $tab['target'] ) ) {
			continue;
		}

		if ( ! isset( $tab['label'] ) || empty( $tab['label'] ) ) {
			continue;
		}

		$icon = isset( $tab['icon'] )
			? $tab['icon']
			: '';

		// If using a Dashicon icon name, create an element.
		if ( '<' !== substr( $icon, 0, 1 ) ) {
			$icon = '<i class="dashicons dashicons-' . esc_attr( $icon ) . '"></i>';
		}

		$class = isset( $tab['class'] )
			? $tab['class']
			: array();

		$html = (
			'<a href="#' . esc_attr( $tab['target'] ) . '" class="simpay-tab-item">' .
				$icon .
				'<span>' . esc_html( $tab['label'] ) . '</span>' .
			'</a>'
		);

		/**
		 * Filters the HTML for Payment Form setting tab items.
		 *
		 * @since 3.0.0
		 *
		 * @param string $html HTML markup.
		 * @param string $key  Tab ID.
		 */
		$html = apply_filters( 'simpay_admin_meta_tab_inner_html', $html, $key );

		$html = wp_kses(
			$html,
			array(
				'a'       => array(
					'href'  => true,
					'class' => true,
				),
				'i'       => array(
					'class' => true,
				),
				'span'    => true,
				'svg'     => array(
					'class'        => true,
					'style'        => true,
					'xmlns'        => true,
					'width'        => true,
					'height'       => true,
					'viewbox'      => true,
					'aria-hidden'  => true,
					'role'         => true,
					'focusable'    => true,
					'fill'         => true,
					'fill-rule'    => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
				'path'    => array(
					'fill'            => true,
					'fill-rule'       => true,
					'd'               => true,
					'transform'       => true,
					'stroke'          => true,
					'stroke-width'    => true,
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
				),
				'polygon' => array(
					'fill'      => true,
					'fill-rule' => true,
					'points'    => true,
					'transform' => true,
					'focusable' => true,
				),
			)
		);
		?>

<li
	class="simpay-<?php echo esc_attr( $key ); ?>-settings simpay-<?php echo esc_attr( $key ); ?>-tab <?php echo esc_attr( implode( ' ', $class ) ); ?>"
	data-tab="<?php echo esc_attr( $key ); ?>"
	data-if="_form_type"
	data-is="off-site"
>
		<?php echo $html; // WPCS: XSS okay. ?>
</li>

		<?php
	}
}

/**
 * Adds "Form Display Options" Payment Form settings tab content.
 *
 * @since 4.1.0
 * @access private
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_display_options( $post_id ) {
	?>

	<table>
		<tbody class="simpay-panel-section">

			<?php
			/**
			 * Allow extra setting rows to be added at the bottom of the table.
			 *
			 * @since 3.4.0
			 *
			 * @param int $form_id Current Payment Form ID.
			 */
			do_action( 'simpay_admin_before_form_display_options_rows', $post_id );
			?>

			<tr class="simpay-panel-field">
				<th>
					<label for="_company_name">
						<?php esc_html_e( 'Title', 'stripe' ); ?>
					</label>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$title = simpay_get_payment_form_setting(
						$post_id,
						'title',
						get_bloginfo( 'name' ),
						__unstable_simpay_get_payment_form_template_from_url()
					);

					simpay_print_field(
						array(
							'type'       => 'standard',
							'subtype'    => 'text',
							'name'       => '_company_name',
							'id'         => '_company_name',
							'value'      => $title,
							'class'      => array(
								'simpay-field-text',
							),
							'attributes' => array(
								'required' => true,
							),
						)
					);
					?>

					<p class="description hidden" style="color: red;">
						<?php
						esc_html_e(
							'A payment form title is required.',
							'stripe'
						);
						?>
					</p>
				</td>
			</tr>

			<tr class="simpay-panel-field">
				<th>
					<label for="_item_description">
						<?php esc_html_e( 'Description', 'stripe' ); ?>
					</label>
				</th>
				<td>
					<?php
					$description = simpay_get_payment_form_setting(
						$post_id,
						'description',
						false,
						__unstable_simpay_get_payment_form_template_from_url()
					);

					simpay_print_field(
						array(
							'type'    => 'standard',
							'subtype' => 'text',
							'name'    => '_item_description',
							'id'      => '_item_description',
							'value'   => false === $description
								? get_bloginfo( 'description' )
								: $description,
							'class'   => array(
								'simpay-field-text',
							),
						)
					);
					?>
				</td>
			</tr>

			<tr class="simpay-panel-field">
				<th>
					<label for="_form_type">
						<?php esc_html_e( 'Type', 'stripe' ); ?>
					</label>
				</th>
				<td>
					<?php
					$license = simpay_get_license();
					$type    = simpay_get_payment_form_setting(
						$post_id,
						'type',
						'stripe_checkout',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					$upgrade_title = __(
						'Unlock On-Site Payment Forms',
						'stripe'
					);

					/* translators: %s Payment form type. */
					$upgrade_description = __(
						'We\'re sorry, on-site payment forms are not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
						'stripe'
					);

					$upgrade_url = simpay_pro_upgrade_url(
						'form-general-settings',
						'Form type'
					);

					$upgrade_purchased_url = simpay_docs_link(
						'Form type (already purchased)',
						$license->is_lite()
							? 'upgrading-wp-simple-pay-lite-to-pro'
							: 'activate-wp-simple-pay-pro-license',
						'form-general-settings',
						true
					);
					?>

					<select
						id="form-type-select"
						name="_form_type"
					>
						<option
							data-available="<?php echo $license->is_lite() ? 'no' : 'yes'; ?>"
							value="on-site"
							data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
							data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
							data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
							data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
							data-prev-value="off-site"
							<?php selected( true, 'stripe_checkout' !== $type ); ?>
						>
							<?php
							esc_html_e(
								'On-site payment form',
								'stripe'
							);
							?>
						</option>
						<option
							value="off-site"
							<?php selected( true, 'stripe_checkout' === $type ); ?>
						>
							<?php
							esc_html_e(
								'Off-site Stripe Checkout form',
								'stripe'
							);
							?>
					</select>

					<label
						for="is-overlay-checkbox"
						id="is-overlay"
						style="margin: 10px 0 -4px 0; display: <?php echo 'stripe_checkout' === $type ? 'none' : 'block'; ?>"
					>
						<input
							id="is-overlay-checkbox"
							name="_is_overlay"
							type="checkbox"
							<?php checked( true, 'overlay' === $type ); ?>
						/>

						<?php
						esc_html_e(
							'Open in an overlay modal',
							'stripe'
						);
						?>
					</label>
				</td>
			</tr>

			<?php
			/**
			 * Allow extra setting rows to be added at the bottom of the table.
			 *
			 * @since 3.4.0
			 *
			 * @param int $form_id Current Payment Form ID.
			 */
			do_action( 'simpay_admin_after_form_display_options_rows', $post_id );
			?>

		</tbody>
	</table>
	<?php
}
add_action( 'simpay_form_settings_display_options_panel', __NAMESPACE__ . '\\add_display_options' );

/**
 * Outputs markup for the "Payment Success Page" setting.
 *
 * @since 4.1.0
 * @access private
 *
 * @param int $post_id Current post ID (Payment Form ID).
 */
function _add_payment_success_page( $post_id ) {
	?>
	<tr class="simpay-panel-field">
		<th>
			<label for="_success_redirect_type">
				<?php esc_html_e( 'Payment Success Page', 'stripe' ); ?>
			</label>
		</th>
		<td>
			<?php
			$success_redirect_type = simpay_get_payment_form_setting(
				$post_id,
				'_success_redirect_type',
				'default',
				__unstable_simpay_get_payment_form_template_from_url()
			);

			simpay_print_field(
				array(
					'type'    => 'radio',
					'name'    => '_success_redirect_type',
					'id'      => '_success_redirect_type',
					'class'   => array( 'simpay-multi-toggle' ),
					'options' => array(
						'default'  => __( 'Global Setting', 'stripe' ),
						'page'     => __( 'Specific Page', 'stripe' ),
						'redirect' => __( 'Redirect URL', 'stripe' ),
					),
					'inline'  => 'inline',
					'default' => 'default',
					'value'   => $success_redirect_type,
				)
			);
			?>

			<div class="simpay-show-if" data-if="_success_redirect_type" data-is="default">
				<p class="description">
					<?php _e( 'By default, the payment success page indicated in Simple Pay > Settings > General will be used. This option allows you to specify an alternate page or URL for this payment form only.', 'stripe' ); ?>
				</p>
			</div>

			<div class="simpay-show-if" data-if="_success_redirect_type" data-is="page" style="margin-top: 8px;">
				<?php
				$success_redirect_page = simpay_get_payment_form_setting(
					$post_id,
					'_success_redirect_page',
					'',
					__unstable_simpay_get_payment_form_template_from_url()
				);

				simpay_print_field(
					array(
						'type'        => 'select',
						'page_select' => 'page_select',
						'name'        => '_success_redirect_page',
						'id'          => '_success_redirect_page',
						'value'       => $success_redirect_page,
						'description' => __(
							'Choose a page from your site to redirect to after a successful transaction.',
							'stripe'
						),
					)
				);
				?>
			</div>

			<div class="simpay-show-if" data-if="_success_redirect_type" data-is="redirect" style="margin-top: 8px;">
				<?php
				$success_redirect_url = simpay_get_payment_form_setting(
					$post_id,
					'_success_redirect_url',
					'',
					__unstable_simpay_get_payment_form_template_from_url()
				);

				simpay_print_field(
					array(
						'type'        => 'standard',
						'subtype'     => 'text',
						'name'        => '_success_redirect_url',
						'id'          => '_success_redirect_url',
						'class'       => array(
							'simpay-field-text',
						),
						'placeholder' => 'https://',
						'value'       => $success_redirect_url,
						'description' => __(
							'Enter a custom redirect URL for successful transactions.',
							'stripe'
						),
					)
				);
				?>
			</div>
		</td>
	</tr>

	<?php
}
add_action(
	'simpay_admin_after_form_display_options_rows',
	__NAMESPACE__ . '\\_add_payment_success_page',
	20
);

/**
 * Outputs markup for the "reCAPTCHA Anti-Spam" setting.
 *
 * @since 4.4.0
 * @access private
 *
 * @return void
 */
function __unstable_add_recaptcha() {
	?>
	<tr class="simpay-panel-field">
		<th>
			<label for="_recaptcha">
				<?php esc_html_e( 'reCAPTCHA Anti-Spam', 'stripe' ); ?>
			</label>
		</th>
		<td>
			<?php
			$url = add_query_arg(
				array(
					'render' => reCAPTCHA\get_key( 'site' ),
				),
				'https://www.google.com/recaptcha/api.js'
			);

			wp_enqueue_script( 'simpay-google-recaptcha-v3', esc_url( $url ), array(), 'v3', true );

			wp_localize_script(
				'simpay-google-recaptcha-v3',
				'simpayGoogleRecaptcha',
				array(
					'siteKey' => reCAPTCHA\get_key( 'site' ),
					'i18n'    => array(
						'enabled'  => '<span class="dashicons dashicons-yes"></span>' . esc_html__( 'Enabled', 'stripe' ),
						'disabled' => '<span class="dashicons dashicons-no"></span>' . esc_html__( 'Disabled', 'stripe' ),
					),
				)
			);

			$recaptcha    = reCAPTCHA\has_keys();
			$settings_url = Settings\get_url(
				array(
					'section'    => 'general',
					'subsection' => 'recaptcha',
					'setting'    => 'recaptcha_site_key',
				)
			);

			$description = $recaptcha
				/* translators: %1$s opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				? __(
					'%1$sConfigure reCAPTCHA%2$s to adjust anti-spam protection.',
					'stripe'
				)
				/* translators: %1$s opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				: __(
					'%1$sEnable reCAPTCHA%2$s to add anti-spam protection.',
					'stripe'
				);

			echo wp_kses(
				sprintf(
					'<span class="simpay-recaptcha-payment-form-feedback">%s</span> <span class="simpay-recaptcha-payment-form-description" style="display: none;">- %s</span>',
					esc_html( 'Verifying...', 'simple-pay' ),
					sprintf(
						$description,
						'<a href="' . esc_url( $settings_url ) . '" target="_blank">',
						'</a>'
					)
				),
				array(
					'a'    => array(
						'href'   => true,
						'target' => true,
					),
					'span' => array(
						'class' => true,
						'style' => true,
					),
				)
			);
			echo '</div>';
			?>
		</td>
	</tr>

	<?php
}
add_action(
	'simpay_admin_after_form_display_options_rows',
	__NAMESPACE__ . '\\__unstable_add_recaptcha',
	30
);
