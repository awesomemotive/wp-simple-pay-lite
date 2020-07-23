<?php
/**
 * Simple Pay: Edit form
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

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

<div class="misc-pub-section">
	<label for="simpay-shortcode">
		<?php esc_html_e( 'Payment Form Shortcode', 'stripe' ); ?>
	</label>

	<?php simpay_print_shortcode_tip( $post->ID ); ?>
</div>

	<?php
}
add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\\add_shortcode_action' );

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

	if ( class_exists( '\SimplePay\Pro\Lite_Helper', false ) ) {
		$panel_classes[] = 'simpay-panel--has-help';
	}
	?>

<div id="simpay-form-settings">
	<div class="simpay-panels-wrap">
		<input type="hidden" name="simpay_form_id" value="<?php echo esc_attr( $post->ID ); ?>" />
		<input type="hidden" name="simpay_form_settings_tab" value="#payment-options-settings-panel" />

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
		'payment_options'      => array(
			'label'  => esc_html__( 'Payment Options', 'stripe' ),
			'target' => 'payment-options-settings-panel',
		),
		'form_display'         => array(
			'label'  => esc_html__( 'On-Site Form Display', 'stripe' ),
			'target' => 'custom-form-fields-settings-panel',
		),
		'stripe_checkout'      => array(
			'label'  => esc_html__( 'Stripe Checkout Display', 'stripe' ),
			'target' => 'stripe-checkout-settings-panel',
		),
		'subscription_options' => array(
			'label'  => esc_html__( 'Subscription Options', 'stripe' ),
			'target' => 'subscription-options-settings-panel',
		),
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

		$class = isset( $tab['class'] )
			? $tab['class']
			: array();

		$html = (
			'<a href="#' . esc_attr( $tab['target'] ) . '" class="simpay-tab-item">' .
				'<i class="' . esc_attr( $icon ) . '" ></i>' .
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
				'a'    => array(
					'href'  => true,
					'class' => true,
				),
				'i'    => array(
					'class' => true,
				),
				'span' => true,
			)
		);
		?>

<li
	class="simpay-<?php echo esc_attr( $key ); ?>-settings simpay-<?php echo esc_attr( $key ); ?>-tab <?php echo esc_attr( implode( ' ', $class ) ); ?>"
	data-tab="<?php echo esc_attr( $key ); ?>"
	data-if="_form_display_type"
	data-is="stripe_checkout"
>
		<?php echo $html; // WPCS: XSS okay. ?>
</li>

		<?php
	}
}
