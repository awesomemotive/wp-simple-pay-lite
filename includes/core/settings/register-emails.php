<?php
/**
 * Settings: Emails
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Settings\Emails;

use SimplePay\Core\Emails;
use SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'emails/register-summary-report.php';

/**
 * Removes TinyMCE Media Buttons.
 *
 * This is required because some TinyMCE buttons are not functional on our edit email pages.
 *
 * @since 4.6.5
 *
 * @return void
 */
function remove_extra_media_buttons() {
	remove_all_actions( 'media_buttons' );
	add_filter( 'wpforms_display_media_button', '__return_false' );
	add_action( 'media_buttons', 'media_buttons' );

	// Add default email template CSS to TinyMCE.
	// @todo This should check the actual template being used,
	// but for now the only template is the default, so it's fine.
	add_filter(
		'mce_css',
		function ( $mce_css ) {
			if ( ! empty( $mce_css ) ) {
				$mce_css .= ',';
			}

			$mce_css .= SIMPLE_PAY_URL . 'includes/core/assets/css/simpay-email-template-default.min.css';

			return $mce_css;
		}
	);

	// Enlarge the editor.
	add_filter(
		'tiny_mce_before_init',
		function ( $settings ) {
			if (
				! isset( $_GET['subsection'] ) ||
				'general' === sanitize_text_field( $_GET['subsection'] )
			) {
				return $settings;
			}

			$settings['height'] = '400';

			return $settings;
		}
	);
}
add_action( 'simpay_admin_page_settings_emails_start', __NAMESPACE__ . '\\remove_extra_media_buttons' );
add_action( 'simpay_admin_page_settings_display_start', __NAMESPACE__ . '\\remove_extra_media_buttons' );
add_action( 'simpay_form_settings_confirmation_panel', __NAMESPACE__ . '\\remove_extra_media_buttons', 0 );

/**
 * Registers settings section.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Section_Collection $sections Section collection.
 */
function register_sections( $sections ) {
	$sections->add(
		new Settings\Section(
			array(
				'id'       => 'emails',
				'label'    => esc_html_x(
					'Emails',
					'settings subsection label',
					'stripe'
				),
				'priority' => 60,
			)
		)
	);
}
add_action( 'simpay_register_settings_sections', __NAMESPACE__ . '\\register_sections' );

/**
 * Registers settings subsection(s).
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	// General.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'general',
				'section'  => 'emails',
				'label'    => esc_html_x(
					'General',
					'settings subsection label',
					'stripe'
				),
				'priority' => 10,
			)
		)
	);
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers the general settings for emails.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_general_settings( $settings ) {
	// From name.
	$settings->add(
		new Settings\Setting_Input(
			array(
				'id'          => 'email_from_name',
				'section'     => 'emails',
				'subsection'  => 'general',
				'label'       => esc_html_x(
					'From Name',
					'setting label',
					'stripe'
				),
				'value'       => simpay_get_setting(
					'email_from_name',
					get_site_option( 'blogname' )
				),
				'description' => wpautop(
					esc_html__(
						'The name that emails come from. This is usually your site name.',
						'stripe'
					)
				),
				'classes'     => array(
					'regular-text',
				),
				'priority'    => 20,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);

	// From email.
	$settings->add(
		new Settings\Setting_Input(
			array(
				'id'          => 'email_from_address',
				'section'     => 'emails',
				'subsection'  => 'general',
				'label'       => esc_html_x(
					'From Address',
					'setting label',
					'stripe'
				),
				'value'       => simpay_get_setting(
					'email_from_address',
					get_site_option( 'admin_email' )
				),
				'description' => wpautop(
					esc_html__(
						'The email address to send emails from. This will act as the "from" and "reply-to" address.',
						'stripe'
					)
				),
				'classes'     => array(
					'regular-text',
				),
				'priority'    => 30,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);

	$license = simpay_get_license();

	if ( $license->is_lite() ) {
		return;
	}

	// Email type.
	$settings->add(
		new Settings\Setting(
			array(
				'id'         => 'email_template',
				'section'    => 'emails',
				'subsection' => 'general',
				'label'      => esc_html_x(
					'Template',
					'setting label',
					'stripe'
				),
				'value'      => simpay_get_setting( 'email_template', 'none' ),
				'output'     => __NAMESPACE__ . '\\get_email_template_output',
				'priority'   => 40,
				'schema'     => array(
					'type' => 'string',
				),
				'toggles'    => array(
					'value'    => 'none',
					'compare'  => 'IS NOT',
					'settings' => array(
						'email_header_image_url',
						'email_footer_content',
					),
				),
			)
		)
	);

	// Email header image URL.
	$settings->add(
		new Settings\Setting(
			array(
				'id'         => 'email_header_image_url',
				'section'    => 'emails',
				'subsection' => 'general',
				'label'      => esc_html_x(
					'Header Image',
					'setting label',
					'stripe'
				),
				'value'      => simpay_get_setting( 'email_header_image_url', '' ),
				'output'     => __NAMESPACE__ . '\\get_email_header_image_output',
				'priority'   => 50,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);

	// Email footer content.
	$settings->add(
		new Settings\Setting(
			array(
				'id'         => 'email_footer_content',
				'section'    => 'emails',
				'subsection' => 'general',
				'label'      => esc_html_x(
					'Footer Content',
					'setting label',
					'stripe'
				),
				'output'     => function () {
					wp_editor(
						simpay_get_setting( 'email_footer_content', '' ),
						'email_footer_content',
						array(
							'textarea_name' => 'simpay_settings[email_footer_content]',
							'textarea_rows' => 1,
						)
					);
				},
				'priority'   => 60,
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);
}
add_action(
	'simpay_register_settings',
	__NAMESPACE__ . '\\register_general_settings'
);

/**
 * Outputs the email template setting.
 *
 * @since 4.7.3
 *
 * @return void
 */
function get_email_template_output() {
	$template = simpay_get_setting( 'email_template', 'none' );
	?>

	<fieldset
		class="simpay-settings-visual-toggles"
		style="margin: 0;"
	>
		<legend class="screen-reader-text">
			<?php esc_html_e( 'Use a styled template', 'stripe' ); ?>
		</legend>

		<input
			type="radio"
			value="default"
			name="simpay_settings[email_template]"
			id="simpay-settings-email-template-default"
			<?php checked( 'default', $template ); ?>
		/>
		<label
			for="simpay-settings-email-template-default"
			class="simpay-settings-visual-toggles__toggle"
		>
			<img
				src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/email-template-default.svg' ); ?>"
				alt="<?php esc_attr_e( 'HTML Template', 'stripe' ); ?>"
				class="simpay-settings-visual-toggles__toggle-icon"
			/>

			<span class="simpay-settings-visual-toggles__toggle-label">
				<?php echo esc_html_e( 'Default', 'stripe' ); ?>
				<small>
					<?php echo esc_html_e( 'Styled', 'stripe' ); ?>
				</small>
			</span>
		</label>

		<input
			type="radio"
			value="none"
			name="simpay_settings[email_template]"
			id="simpay-settings-email-template-none"
			<?php checked( 'none', $template ); ?>
		/>
		<label
			for="simpay-settings-email-template-none"
			class="simpay-settings-visual-toggles__toggle"
		>
			<img
				src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/email-template-none.svg' ); ?>"
				alt="<?php esc_attr_e( 'None', 'stripe' ); ?>"
				class="simpay-settings-visual-toggles__toggle-icon"
			/>

			<span class="simpay-settings-visual-toggles__toggle-label">
				<?php esc_html_e( 'None', 'stripe' ); ?>
				<small><?php esc_html_e( 'Plain HTML', 'stripe' ); ?></small>
			</span>
		</label>
	</fieldset>

	<br/ >
	<p class="description">
		<?php
		esc_html_e(
			'Use a template to include a custom header image and footer content in emails sent to customers.',
			'stripe'
		);
		?>
	</p>

	<?php
}


/**
 * Outputs the "Email Header Image" setting.
 *
 * @since 4.7.3
 *
 * @return void
 */
function get_email_header_image_output() {
	$email_header_image_url = simpay_get_setting(
		'email_header_image_url',
		''
	);

	simpay_print_field(
		array(
			'type'    => 'standard',
			'subtype' => 'hidden',
			'name'    => 'simpay_settings[email_header_image_url]',
			'id'      => 'simpay-settings-email-header-image-url',
			'value'   => $email_header_image_url,
			'class'   => array(
				'simpay-field-text',
				'simpay-field-image-url',
			),
		)
	);
	?>

	<div class="simpay-image-preview-wrap" style="display: <?php echo( empty( $email_header_image_url ) ? 'none' : 'block' ); ?>">
		<img src="<?php echo esc_attr( $email_header_image_url ); ?>" class="simpay-image-preview" style="max-width: 300px; margin: 0 0 12px;" />
	</div>

	<div style="display: flex; align-items: center;">
		<button type="button" class="simpay-media-uploader button button-secondary" style="margin-top: 4px;"><?php esc_html_e( 'Choose a Header Image', 'stripe' ); ?></button>

		<button class="simpay-remove-image-preview button button-secondary button-danger button-link" style="margin-left: 8px; display: <?php echo ! empty( $email_header_image_url ) ? 'block' : 'none'; ?>">
			<?php esc_attr_e( 'Remove', 'stripe' ); ?>
		</button>
	</div>

	<p class="description">
		<?php
		esc_html_e(
			'Upload or choose a header image to be displayed at the top of templated emails. Recommended size is 300px × 100px or smaller for best support on all devices.',
			'stripe'
		);
		?>
	</p>

	<?php
}

/**
 * Outputs the email configuration selector.
 *
 * @since 4.4.6
 *
 * @return void
 */
function add_email_selector() {
	$email_groups = array(
		'payment' => array(
			'label'  => __( 'Payments', 'stripe' ),
			'emails' => array(
				Emails\Email\PaymentNotificationEmail::class,
				Emails\Email\PaymentConfirmationEmail::class,
				Emails\Email\UpcomingInvoiceEmail::class,
				Emails\Email\InvoiceConfirmationEmail::class,
				Emails\Email\ManageSubscriptionsEmail::class,
				Emails\Email\PaymentProcessingConfirmationEmail::class,
				Emails\Email\PaymentProcessingNotificationEmail::class,
				Emails\Email\PaymentRefundedConfirmationEmail::class,
				Emails\Email\SubscriptionCancellationConfirmation::class,
				Emails\Email\SubscriptionCancellationNotification::class,
			),
		),
		'general' => array(
			'label'  => __( 'Other', 'stripe' ),
			'emails' => array(
				Emails\Email\SummaryReportEmail::class,
			),
		),
	);

	$subsection = isset( $_GET['subsection'] )
		? sanitize_text_field( $_GET['subsection'] )
		: '';

	$license = simpay_get_license();
	?>

	<form action="" method="GET" class="simpay-settings-emails-configure">
		<select name="subsection">
			<option value="">
				<?php esc_html_e( 'Select an email to configure&hellip;', 'stripe' ); ?>
			</option>
			<?php foreach ( $email_groups as $group ) : ?>
				<optgroup label="<?php echo esc_attr( $group['label'] ); ?>">
					<?php
					foreach ( $group['emails'] as $email ) :
						$email = new $email();

						$upgrade_title = sprintf(
							/* translators: Email label. */
							__(
								'Unlock "%s" Email',
								'stripe'
							),
							esc_html( $email->get_label() )
						);

						$upgrade_description = sprintf(
							/* translators: %1$s Email label. %2$s License level required. */
							__(
								'We\'re sorry, sending and customizing the "%1$s" email is not available on your plan. Please upgrade to the <strong>%2$s</strong> plan or higher to unlock this and other awesome features.',
								'stripe'
							),
							esc_html( $email->get_label() ),
							ucfirst( current( $email->get_licenses() ) )
						);

						$upgrade_url = simpay_pro_upgrade_url(
							'email-settings',
							$email->get_label()
						);

						$upgrade_purchased_url = simpay_docs_link(
							$email->get_label(),
							$license->is_lite()
								? 'upgrading-wp-simple-pay-lite-to-pro'
								: 'activate-wp-simple-pay-pro-license',
							'email-settings',
							true
						);

						echo wp_kses(
							sprintf(
								'<option value="%1$s" %2$s data-available="%3$s" data-upgrade-title="%4$s" data-upgrade-description="%5$s" data-upgrade-url="%6$s" data-upgrade-purchased-url="%7$s" >%8$s</option>',
								esc_attr( $email->get_id() ),
								selected( true, $email->get_id() === $subsection, false ),
								in_array( $license->get_level(), $email->get_licenses(), true )
									? 'yes'
									: 'no',
								esc_attr( $upgrade_title ),
								esc_attr( $upgrade_description ),
								esc_url( $upgrade_url ),
								esc_url( $upgrade_purchased_url ),
								esc_html( $email->get_label() )
							),
							array(
								'option' => array(
									'value'              => true,
									'selected'           => true,
									'data-available'     => true,
									'data-upgrade-title' => true,
									'data-upgrade-description' => true,
									'data-upgrade-url'   => true,
									'data-upgrade-purchased-url' => true,
								),
							)
						);
					endforeach;
					?>
				</optgroup>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="button button-secondary">
			<?php echo esc_html_e( 'Configure', 'stripe' ); ?>
		</button>
		<input type="hidden" name="post_type" value="simple-pay" />
		<input type="hidden" name="page" value="simpay_settings" />
		<input type="hidden" name="tab" value="emails" />
	</form>

	<?php
}
add_action(
	'simpay_admin_page_settings_emails_before',
	__NAMESPACE__ . '\\add_email_selector'
);

/**
 * Sanitizes Email settings.
 *
 * @since 4.0.0
 *
 * @param array $settings Settings to save.
 * @return array $settings Setttings to save.
 */
function sanitize_settings( $settings ) {
	// Ensure a valid "From Address".
	if ( isset( $settings['email_from_address'] ) ) {
		if ( ! is_email( $settings['email_from_address'] ) ) {
			$settings['email_from_address'] = get_bloginfo( 'admin_email' );
		}
	} else {
		$settings['email_from_address'] = get_bloginfo( 'admin_email' );
	}

	return $settings;
}
add_filter( 'simpay_update_settings', __NAMESPACE__ . '\\sanitize_settings' );
