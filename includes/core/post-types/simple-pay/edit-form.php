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
			class="preview button simpay-preview-button"
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
			<?php esc_html_e( 'Shortcode', 'stripe' ); ?>
		</label>

		<?php
		simpay_print_shortcode_tip(
			$post->ID,
			__( 'Copy Shortcode', 'stripe' )
		);
		?>
	</div>
	<div id="simpay-payment-url-shortcode-section" class="misc-pub-section simpay-shortcode-section">
		<label for="simpay-shortcode">
			<span class="dashicons dashicons-admin-links"></span>
			<?php esc_html_e( 'Payment Page URL', 'stripe' ); ?>
			<a
				href="#payment-page-settings-panel"
				data-show-tab="simpay-payment_page"
				class="simpay-tab-link edit-post-status"
			>
				<span aria-hidden="true"><?php echo __( 'Edit', 'stripe' ); ?></span>
				<span class="screen-reader-text"><?php echo __( 'Edit', 'stripe' ); ?></span>
			</a>
		</label>
		<?php
		simpay_print_payment_form_permalink( $post->ID );
		?>
	</div>
	<div class="misc-pub-section simpay-shortcode-section">
		<label for="simpay-shortcode">
			<span class="dashicons dashicons-feedback"></span>
			<?php esc_html_e( 'Copy Block', 'stripe' ); ?>
		</label>
		<?php
		simpay_print_copy_block( $post->ID );
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

		$license = simpay_get_license();

		$data_lite = $license->is_lite() ? 'data-lite' : 'data-pro';
		?>

<style>
.page-title-action { display: none; }
</style>

<div id="simpay-form-settings" <?php echo esc_attr( $data_lite ); ?>>
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
				id="payment-page-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				/**
				 * Allows output in the "Payment Page" form settings tab panel.
				 *
				 * @since 4.5.0
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action( 'simpay_form_settings_payment_page_panel', $post->ID );
				?>
			</div>

			<div
				id="purchase-restrictions-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				/**
				 * Allows output in the "Purchase Restrictions" form settings tab panel.
				 *
				 * @since 4.6.4
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action(
					'simpay_form_settings_purchase_restrictions_panel',
					$post->ID
				);
				?>
			</div>

			<div
				id="automations-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				/**
				 * Allows output in the "Automations" form settings tab panel.
				 *
				 * @since 4.7.8
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action(
					'simpay_form_settings_automations_panel',
					$post->ID
				);
				?>
			</div>

			<div
				id="confirmation-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				/**
				 * Allows output in the "Confirmation" form settings tab panel.
				 *
				 * @since 4.7.9
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action(
					'simpay_form_settings_confirmation_panel',
					$post->ID
				);
				?>
			</div>

			<div
				id="notifications-settings-panel"
				class="simpay-panel-hidden <?php echo esc_attr( implode( ' ', $panel_classes ) ); ?>"
			>
				<?php
				/**
				 * Allows output in the "Notifications" form settings tab panel.
				 *
				 * @since 4.7.9
				 *
				 * @param int $form_id Current Payment Form ID.
				 */
				do_action(
					'simpay_form_settings_notifications_panel',
					$post->ID
				);
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
		$tabs    = array();
		$license = simpay_get_license();

		// "Email Notifications" upgrade modal for Lite.
		// @todo This is messy and should be able to be set in the tabs array.
		$upgrade_title = esc_html__( 'Unlock Email Notifications', 'stripe' );

		$upgrade_description = __(
			'We\'re sorry, the customizable email notifications are not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
			'stripe'
		);

		$upgrade_url = simpay_pro_upgrade_url( 'form-notifications-settings' );

		$upgrade_purchased_url = simpay_docs_link(
			'Email Notifications (already purchased)',
			'upgrading-wp-simple-pay-lite-to-pro',
			'form-payment-method-settings',
			true
		);

		// Icons: https://heroicons.com/
		// Mini.

		$tabs['form_display_options'] = array(
			'label'  => esc_html__( 'General', 'stripe' ),
			'target' => 'form-display-options-settings-panel',
			'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54a6.993 6.993 0 0 1 1.93-1.115l.33-1.652zM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" clip-rule="evenodd"/></svg>',
		);

		$tabs['payment_options'] = array(
			'label'  => wp_kses(
				__( 'Payment <span>New!</span>', 'stripe' ),
				array(
					'span' => array(),
				)
			),
			'target' => 'payment-options-settings-panel',
			'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 001 5.5V6h18v-.5A1.5 1.5 0 0017.5 4h-15zM19 8.5H1v6A1.5 1.5 0 002.5 16h15a1.5 1.5 0 001.5-1.5v-6zM3 13.25a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zm4.75-.75a.75.75 0 000 1.5h3.5a.75.75 0 000-1.5h-3.5z" clip-rule="evenodd" /></svg>',
		);

		$tabs['form_display'] = array(
			'label'  => esc_html__( 'Form Fields', 'stripe' ),
			'target' => 'custom-form-fields-settings-panel',
			'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65z"/><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"/></svg>',
		);

		$tabs['purchase_restrictions'] = array(
			'label'  => esc_html__( 'Purchase Restrictions', 'stripe' ),
			'target' => 'purchase-restrictions-settings-panel',
			'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1zm3 8V5.5a3 3 0 1 0-6 0V9h6z" clip-rule="evenodd"/></svg>',
		);

		$tabs['stripe_checkout'] = array(
			'label'  => esc_html__( 'Stripe Checkout', 'stripe' ),
			'target' => 'stripe-checkout-settings-panel',
			'icon'   => '<svg width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg"><g fill="currentColor"><path d="M15.77 2H5.92c-.42 0-.815.215-1.045.57-.11304.17606-.18045.37749-.19616.58612-.01572.20862.02077.41788.10616.60888l2.55 5.73c.15.32.15.69 0 1.01L4 18h11.77c.485 0 .93-.29 1.13-.74l2.99-6.75c.15-.325.15-.695 0-1.02L16.9 2.74c-.0967-.21938-.2548-.40603-.4554-.53737-.2005-.13133-.4349-.20172-.6746-.20263Z" fill-opacity=".8"/><path d="M3.61501 18c-.16944-.0001-.33676-.0377-.49-.11h.02c-.27386-.1221-.49291-.3411-.615-.615l-2.419997-5.51c-.0843758-.1901-.120752-.3981-.10593079-.6056.01482119-.2075.08038589-.4082.19093079-.5844.109865-.1755.262421-.3203.443418-.4208.180998-.1006.384529-.1536.591579-.1542H10.98c.485 0 .92.285 1.115.73l2.4 5.425.34.765c.05.11.115.21.19.3.245.32.6.65.99.755-.055.015-.15.025-.275.025H3.61001h.005Z" fill-opacity=".5"/><path d="M10.985 10c.45 0 .86.25 1.07.65l.04.08 2.4 5.425.34.765c.2521.4607.6596.8169 1.15 1.005l.09.035c-.1.025-.2.04-.305.04H4l3.335-7.5c.075-.16.11-.33.11-.505h3.54V10Z"/></g></svg>',
		);

		$tabs['payment_page'] = array(
			'label'  => esc_html__( 'Payment Page', 'stripe' ),
			'target' => 'payment-page-settings-panel',
			'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M15.621 4.379a3 3 0 0 0-4.242 0l-7 7a3 3 0 0 0 4.241 4.243h.001l.497-.5a.75.75 0 0 1 1.064 1.057l-.498.501-.002.002a4.5 4.5 0 0 1-6.364-6.364l7-7a4.5 4.5 0 0 1 6.368 6.36l-3.455 3.553A2.625 2.625 0 1 1 9.52 9.52l3.45-3.451a.75.75 0 1 1 1.061 1.06l-3.45 3.451a1.125 1.125 0 0 0 1.587 1.595l3.454-3.553a3 3 0 0 0 0-4.242z" clip-rule="evenodd"/></svg>',
		);

		$tabs['confirmation'] = array(
			'label'  => wp_kses(
				__( 'Confirmation Page', 'stripe' ),
				array()
			),
			'target' => 'confirmation-settings-panel',
			'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5z" clip-rule="evenodd"/></svg>',
		);

		$tabs['notifications'] = array(
			'label'  => wp_kses(
				__( 'Email Notifications', 'stripe' ),
				array(
					'span' => array(),
				)
			),
			'target' => 'notifications-settings-panel',
			'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20"><path d="M4.214 3.227a.75.75 0 0 0-1.156-.956 8.97 8.97 0 0 0-1.856 3.826.75.75 0 0 0 1.466.316 7.47 7.47 0 0 1 1.546-3.186zm12.728-.956a.75.75 0 0 0-1.157.956 7.47 7.47 0 0 1 1.547 3.186.75.75 0 0 0 1.466-.316 8.971 8.971 0 0 0-1.856-3.826z"/><path fill-rule="evenodd" d="M10 2a6 6 0 0 0-6 6c0 1.887-.454 3.665-1.257 5.234a.75.75 0 0 0 .515 1.076 32.94 32.94 0 0 0 3.256.508 3.5 3.5 0 0 0 6.972 0 32.933 32.933 0 0 0 3.256-.508.75.75 0 0 0 .515-1.076A11.448 11.448 0 0 1 16 8a6 6 0 0 0-6-6zm0 14.5a2 2 0 0 1-1.95-1.557 33.54 33.54 0 0 0 3.9 0A2 2 0 0 1 10 16.5z" clip-rule="evenodd"/></svg>',
		);

		$tabs['automations'] = array(
			'label'  => wp_kses(
				__( 'Automations', 'stripe' ),
				array(
					'span' => array(),
				)
			),
			'target' => 'automations-settings-panel',
			'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143z" clip-rule="evenodd"/></svg>',
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
				'<span>' . $tab['label'] . '</span>' . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
						'fill-opacity'    => true,
						'd'               => true,
						'transform'       => true,
						'stroke'          => true,
						'stroke-width'    => true,
						'stroke-linecap'  => true,
						'stroke-linejoin' => true,
					),
					'g'       => array(
						'fill' => true,
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
			<?php if ( 'notifications' === $key && $license->is_lite() ) : ?>
		data-available="no"
		data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
		data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
		data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
		data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
	<?php endif; ?>
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
				<td style="border-bottom: 0; padding-bottom: 0;">
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
	 * Outputs markup for the "reCAPTCHA Anti-Spam" setting.
	 *
	 * @since 4.4.0
	 * @access private
	 *
	 * @return void
	 */
	function __unstable_add_recaptcha() {
		$settings_url = Settings\get_url(
			array(
				'section'    => 'general',
				'subsection' => 'recaptcha',
			)
		);

		$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
		$default            = ! empty( $existing_recaptcha )
			? 'recaptcha-v3'
			: '';

		$captcha     = simpay_get_setting( 'captcha_type', $default );
		$has_captcha = ! empty( $captcha ) && 'none' !== $captcha;
		?>

	<tr class="simpay-panel-field">
		<th>
			<?php esc_html_e( 'Spam & Fraud Protection', 'stripe' ); ?>
		</th>
		<td style="border-bottom: 0;">
			<div style="margin: 4px 0 0;">
				<div style="display: flex; align-items: center;">
					<label for="_recaptcha" class="simpay-field-bool">
						<input
							name="_recaptcha"
							type="checkbox"
							id="_recaptcha"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
							<?php checked( true, $has_captcha ); ?>
							<?php if ( $has_captcha ) : ?>
								readonly
							<?php endif; ?>
							data-settings-url="<?php echo esc_attr( $settings_url ); ?>"
						/>

						<?php esc_html_e( 'CAPTCHA', 'stripe' ); ?>
					</label>
				</div>
				<p class="description">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %1$s opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
							__(
								'%1$sConfigure CAPTCHA settings%2$s to adjust anti-spam protection.',
								'stripe'
							),
							'<a href="' . esc_url( $settings_url ) . '" target="_blank">',
							'</a>'
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
					?>

					<span class="simpay-recaptcha-payment-form-feedback">
						<?php if ( $has_captcha ) : ?>
							<span style="color: #15803d;">
								<span class="dashicons dashicons-shield-alt"></span>
								<?php esc_html_e( 'Additional protection enabled!', 'stripe' ); ?>
							</span>
						<?php else : ?>
							<span style="color: #b91c1c;">
								<span class="dashicons dashicons-shield"></span>
								<?php esc_html_e( 'Disabled — missing additional protection!', 'stripe' ); ?>
							</span>
						<?php endif; ?>
					</span>
				</p>
			</div>

				<?php
				/**
				 * Allows further output in the "Spam & Fraud Protection" section.
				 *
				 * @since 4.6.0
				 */
				do_action( '__unstable_simpay_after_form_anti_spam_settings' );
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
