<?php
/**
 * Admin UI Handler
 *
 * @package SimplePay
 * @since 4.17.0
 */

namespace SimplePay\Core\Admin\FormBuilder\FormStyle;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class AdminUI
 *
 * Handles the admin user interface for WP Simple Pay Styles.
 * Adds style settings to the WP Simple Pay form editor and handles
 * saving and retrieving style settings.
 *
 * @since 4.17.0
 */
class AdminUI {

	/**
	 * Instance.
	 *
	 * @since  4.17.0
	 * @access private
	 * @var    AdminUI The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Ensures only one instance of AdminUI is loaded or can be loaded.
	 *
	 * @since  4.17.0
	 * @access public
	 * @static
	 * @return AdminUI An instance of the class.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 4.17.0
	 * @access private
	 */
	private function __construct() {
		// Add hooks.
		$this->hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 4.17.0
	 * @access private
	 */
	private function hooks(): void {
		// Hook into the WP Simple Pay 'General' tab action.
		add_action( 'simpay_form_settings_form_style_panel', array( $this, 'render_style_settings_in_tab' ), 20, 1 ); // Priority 20 to appear after core fields.

		// Hook into the save action.
		add_action( 'save_post_simple-pay', array( $this, 'save_style_settings' ), 10, 2 );

		// Hook into the enqueue action for scripts/styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue admin scripts and styles (like the color picker).
	 *
	 * Loads necessary CSS and JavaScript files for the admin interface,
	 * but only on the WP Simple Pay form edit screen.
	 *
	 * @since 4.17.0
	 *
	 * @param string $hook_suffix The current admin page.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		global $post_type;

		// Only load on the simple-pay post type edit screen.
		if ( 'simple-pay' === $post_type && ( 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) ) {

			// Enqueue WordPress color picker scripts and styles.
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			// Enqueue color picker alpha addon if available.
			if ( ! wp_script_is( 'wp-color-picker-alpha', 'registered' ) ) {

				wp_register_script(
					'wp-color-picker-alpha',
					SIMPLE_PAY_URL . 'src/Admin/FormBuilder/FormStyle/assets/js/wp-color-picker-alpha.min.js',
					array( 'wp-color-picker' ),
					'3.0.0',
					true
				);
			}
			wp_enqueue_script( 'wp-color-picker-alpha' );
			// Enqueue custom admin styles and scripts.
			wp_enqueue_style(
				'wpsp-admin-admin-css',
				SIMPLE_PAY_URL . 'src/Admin/FormBuilder/FormStyle/assets/css/admin.css',
				array(),
				SIMPLE_PAY_VERSION
			);

			wp_enqueue_script(
				'wpsp-admin-admin-js',
				SIMPLE_PAY_URL . 'src/Admin/FormBuilder/FormStyle/assets/js/admin.js',
				array( 'jquery', 'wp-color-picker', 'wp-color-picker-alpha' ),
				SIMPLE_PAY_VERSION,
				true
			);

			// Pass localized data to the JS.
			wp_localize_script(
				'wpsp-admin-admin-js',
				'simpayFormStyleData',
				array(
					'resetConfirmMessage' => __( 'Are you sure you want to reset all style settings to default values?', 'stripe' ),
				)
			);
		}
	}

	/**
	 * Check if this is a new form without any saved style settings
	 *
	 * Determines if a form has any style settings saved yet.
	 * Used to apply default styles to new forms.
	 *
	 * @since 4.17.0
	 * @access private
	 *
	 * @param int $post_id The post ID to check.
	 * @return bool True if this is a new form, false otherwise
	 */
	private function is_new_form( $post_id ) {
		// Check if any style settings exist for this form.
		foreach ( Settings::get_style_keys() as $key ) {
			if ( Settings::setting_exists( $post_id, $key ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the available theme presets for the form styles UI.
	 *
	 * @since 4.17.0
	 * @access private
	 *
	 * @return array<string, array{name: string, description: string, colors: array{primary: string, secondary: string, text: string, background: string}}> Theme preset configuration.
	 */
	private function get_theme_presets() {
		return array(
			'default'    => array(
				'name'        => __( 'Default', 'stripe' ),
				'colors'      => array(
					'primary'    => '#0f8569',
					'secondary'  => '#0e7c62',
					'text'       => '#32325d',
					'background' => '#ffffff',
				),
				'description' => __( 'WP Simple Pay\'s default styling', 'stripe' ),
			),
			'sunset'     => array(
				'name'        => __( 'Sunset', 'stripe' ),
				'colors'      => array(
					'primary'    => '#e74c3c',
					'secondary'  => '#c0392b',
					'text'       => '#2c3e50',
					'background' => '#ecf0f1',
				),
				'description' => __( 'Warm red accents with light background', 'stripe' ),
			),
			'forest'     => array(
				'name'        => __( 'Forest', 'stripe' ),
				'colors'      => array(
					'primary'    => '#27ae60',
					'secondary'  => '#219955',
					'text'       => '#2c3e50',
					'background' => '#f9f9f9',
				),
				'description' => __( 'Fresh green theme with clean background', 'stripe' ),
			),
			'ocean'      => array(
				'name'        => __( 'Ocean', 'stripe' ),
				'colors'      => array(
					'primary'    => '#3498db',
					'secondary'  => '#2980b9',
					'text'       => '#2c3e50',
					'background' => '#ecf0f1',
				),
				'description' => __( 'Calming blue palette', 'stripe' ),
			),
			'monochrome' => array(
				'name'        => __( 'Monochrome', 'stripe' ),
				'colors'      => array(
					'primary'    => '#333333',
					'secondary'  => '#555555',
					'text'       => '#333333',
					'background' => '#ffffff',
				),
				'description' => __( 'Simple black and white theme', 'stripe' ),
			),
			'sunshine'   => array(
				'name'        => __( 'Sunshine', 'stripe' ),
				'colors'      => array(
					'primary'    => '#f1c40f',
					'secondary'  => '#f39c12',
					'text'       => '#34495e',
					'background' => '#ffffff',
				),
				'description' => __( 'Bright and cheerful yellow accents', 'stripe' ),
			),
			'coral'      => array(
				'name'        => __( 'Coral', 'stripe' ),
				'colors'      => array(
					'primary'    => '#e67e22',
					'secondary'  => '#d35400',
					'text'       => '#2c3e50',
					'background' => '#f9f9f9',
				),
				'description' => __( 'Warm orange palette', 'stripe' ),
			),
			'minimal'    => array(
				'name'        => __( 'Minimal', 'stripe' ),
				'colors'      => array(
					'primary'    => '#bdc3c7',
					'secondary'  => '#95a5a6',
					'text'       => '#2c3e50',
					'background' => '#ffffff',
				),
				'description' => __( 'Clean, minimalist design', 'stripe' ),
			),
		);
	}

	/**
	 * Renders the style settings within the WP Simple Pay 'General' tab.
	 *
	 * Creates the tabbed interface for style settings in the form editor.
	 * Includes tabs for themes, colors, typography, layout, and buttons.
	 *
	 * @since 4.17.0
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public function render_style_settings_in_tab( $post_id ) {

		// Check if the license allows access to form styles (Plus or higher).
		$license = simpay_get_license();
		if ( ! $license->is_pro( 'plus', '>=' ) ) {
			$this->render_style_upsell();
			return;
		}

		// Determine if this is a new form.
		$is_new_form = $this->is_new_form( $post_id );

		// Add a nonce field for security. Must be inside the form.
		wp_nonce_field( 'wpsps_save_styles', 'wpsps_styles_nonce' );

		$style_keys = Settings::get_style_keys();

		// Show a warning when the form type is off-site (Stripe Checkout).
		$form_display_type = simpay_get_saved_meta( $post_id, '_form_display_type', '' );
		$is_offsite        = 'stripe_checkout' === $form_display_type;
		?>

		<div
			class="simpay-show-if notice notice-warning inline"
			data-if="_form_type"
			data-is="off-site"
			<?php echo $is_offsite ? '' : 'style="display:none;"'; ?>
		>
			<p>
				<?php
				esc_html_e(
					'Form Style settings only apply to on-site payment forms. Switch the form type to "On-site payment form" to use these styles.',
					'stripe'
				);
				?>
			</p>
		</div>

		<?php
		// Start the modern tabbed interface.
		?>
		<div class="wpsp-admin-tabs-container">
			<!-- Tabs Navigation -->
			<div class="wpsp-admin-tabs-nav">
				<button type="button" class="wpsp-admin-tab-button active" data-tab="themes">
					<span class="dashicons dashicons-admin-appearance"></span>
					<?php esc_html_e( 'Themes', 'stripe' ); ?>
				</button>
				<button type="button" class="wpsp-admin-tab-button" data-tab="colors">
					<span class="dashicons dashicons-art"></span>
					<?php esc_html_e( 'Colors', 'stripe' ); ?>
				</button>
				<button type="button" class="wpsp-admin-tab-button" data-tab="typography">
					<span class="dashicons dashicons-editor-textcolor"></span>
					<?php esc_html_e( 'Typography', 'stripe' ); ?>
				</button>
				<button type="button" class="wpsp-admin-tab-button" data-tab="design">
					<span class="dashicons dashicons-admin-customizer"></span>
					<?php esc_html_e( 'Design & Layout', 'stripe' ); ?>
				</button>
			</div>

			<!-- Tabs Content -->
			<div class="wpsp-admin-tabs-content">
				<!-- Themes Tab -->
				<div class="wpsp-admin-tab-panel active" data-tab-content="themes">
					<div class="wpsp-admin-theme-instructions">
						<p><?php esc_html_e( 'Select a theme to instantly apply a complete set of coordinated styles. You can customize individual settings in the other tabs after applying a theme.', 'stripe' ); ?></p>
					</div>
					<div class="wpsp-admin-theme-grid">
						<?php
						// Get the current selected theme.
						$current_theme = Settings::get_setting( $post_id, 'selected_theme', 'default' );

						// Define theme presets.
						$themes = $this->get_theme_presets();

						// Store themes in a hidden field for JavaScript access.
						echo '<input type="hidden" id="wpsps_theme_presets" value="' . esc_attr( (string) wp_json_encode( $themes ) ) . '">';

						// Output theme selection cards.
						foreach ( $themes as $theme_id => $theme ) {
							$is_selected = $current_theme === $theme_id;
							?>
							<div class="wpsp-admin-theme-card <?php echo $is_selected ? 'selected' : ''; ?>" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="--theme-primary: <?php echo esc_attr( $theme['colors']['primary'] ); ?>; --theme-secondary: <?php echo esc_attr( $theme['colors']['secondary'] ); ?>; --theme-background: <?php echo esc_attr( $theme['colors']['background'] ); ?>; --theme-text: <?php echo esc_attr( $theme['colors']['text'] ); ?>; --theme-border: #e6e6e6; --theme-input-bg: <?php echo esc_attr( $theme['colors']['background'] ); ?>;">
								<div class="wpsp-admin-theme-preview">
									<div class="wpsp-admin-theme-colors">
										<span class="wpsp-admin-theme-color primary" style="background-color: <?php echo esc_attr( $theme['colors']['primary'] ); ?>" data-color-name="Primary"></span>
										<span class="wpsp-admin-theme-color secondary" style="background-color: <?php echo esc_attr( $theme['colors']['secondary'] ); ?>" data-color-name="Secondary"></span>
										<span class="wpsp-admin-theme-color text" style="background-color: <?php echo esc_attr( $theme['colors']['text'] ); ?>" data-color-name="Text"></span>
										<span class="wpsp-admin-theme-color background" style="background-color: <?php echo esc_attr( $theme['colors']['background'] ); ?>" data-color-name="Background"></span>
									</div>
									<!-- Mini form preview with consistent styling -->
									<div class="wpsp-admin-theme-form-preview wpsp-admin-preview-wrapper" data-theme-id="<?php echo esc_attr( $theme_id ); ?>" style="background-color: <?php echo esc_attr( $theme['colors']['background'] ); ?>;">
										<div class="preview-field">
											<label class="preview-label wpsp-admin-preview-label" data-theme-color="label" style="color: <?php echo esc_attr( $theme['colors']['text'] ); ?>"><?php esc_html_e( 'Email Address', 'stripe' ); ?></label>
											<input type="text" class="preview-input wpsp-admin-preview-input" placeholder="<?php esc_attr_e( 'Enter your email', 'stripe' ); ?>" style="background-color: <?php echo esc_attr( $theme['colors']['background'] ); ?>; color: <?php echo esc_attr( $theme['colors']['text'] ); ?>; border-color: #e6e6e6; border-radius: 4px;">
										</div>
										<div class="preview-field">
											<label class="preview-label wpsp-admin-preview-label" data-theme-color="label" style="color: <?php echo esc_attr( $theme['colors']['text'] ); ?>"><?php esc_html_e( 'Amount', 'stripe' ); ?></label>
											<input type="text" class="preview-input wpsp-admin-preview-input" placeholder="$50.00" style="background-color: <?php echo esc_attr( $theme['colors']['background'] ); ?>; color: <?php echo esc_attr( $theme['colors']['text'] ); ?>; border-color: #e6e6e6; border-radius: 4px;">
										</div>
										<div class="preview-field">
											<button type="button" class="wpsp-admin-theme-button wpsp-admin-preview-button wpsp-admin-preview-btn" data-theme-color="button" style="background-color: <?php echo esc_attr( $theme['colors']['primary'] ); ?>; color: #ffffff; border-radius: 4px;">
												<?php esc_html_e( 'Pay Now', 'stripe' ); ?>
											</button>
										</div>
									</div>
								</div>
								<div class="wpsp-admin-theme-info">
									<h4><?php echo esc_html( $theme['name'] ); ?></h4>
									<p><?php echo esc_html( $theme['description'] ); ?></p>
									<input
										type="radio"
										name="wpsps[selected_theme]"
										value="<?php echo esc_attr( $theme_id ); ?>"
										id="wpsps_theme_<?php echo esc_attr( $theme_id ); ?>"
										<?php checked( $current_theme, $theme_id ); ?>
										class="wpsp-admin-theme-radio"
									>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>

				<!-- Colors Tab -->
				<div class="wpsp-admin-tab-panel" data-tab-content="colors">
					<!-- Background Colors Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-admin-appearance"></span>
							<?php esc_html_e( 'Background Colors', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Form Container Background Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_form_container_background_color">
									<?php esc_html_e( 'Form Background', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<?php
									// Get raw value to show saved value even if empty.
									$form_bg_value = Settings::get_raw_setting( $post_id, 'form_container_background_color' );
									?>
									<input 
										type="text" 
										id="wpsps_form_container_background_color" 
										name="wpsps[form_container_background_color]" 
										value="<?php echo esc_attr( $form_bg_value ); ?>" 
										class="wpspcolor-picker" 
										data-alpha-enabled="true"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Form', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Background color of the entire form container', 'stripe' ); ?>
								</p>
							</div>

							<!-- Input Background Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_background_color">
									<?php esc_html_e( 'Input Background', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<?php
									// Get raw value to show saved value even if empty.
									$input_bg_value = Settings::get_raw_setting( $post_id, 'background_color' );
									?>
									<input 
										type="text" 
										id="wpsps_background_color" 
										name="wpsps[background_color]" 
										value="<?php echo esc_attr( $input_bg_value ); ?>" 
										class="wpspcolor-picker" 
										data-alpha-enabled="true"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Input', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Background color of input fields, selects, and dropdowns', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>

					<!-- Text Colors Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-editor-textcolor"></span>
							<?php esc_html_e( 'Text Colors', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- General Text Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_text_color">
									<?php esc_html_e( 'General Text Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_text_color" 
										name="wpsps[text_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'text_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Text', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Default color for all text elements', 'stripe' ); ?>
								</p>
							</div>

							<!-- Label Text Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_label_text_color">
									<?php esc_html_e( 'Label Text Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_label_text_color" 
										name="wpsps[label_text_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'label_text_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Labels', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Color specifically for field labels (overrides General Text Color)', 'stripe' ); ?>
								</p>
							</div>

							<!-- Input Text Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_input_text_color">
									<?php esc_html_e( 'Input Text Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_input_text_color" 
										name="wpsps[input_text_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'input_text_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Input Text', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Color specifically for text in input fields (overrides General Text Color)', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>

					<!-- Accent & Border Colors Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-art"></span>
							<?php esc_html_e( 'Accent & Border Colors', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Primary Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_primary_color">
									<?php esc_html_e( 'Primary (Accent) Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_primary_color" 
										name="wpsps[primary_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'primary_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Accent', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Used for focus states, highlights, and links', 'stripe' ); ?>
								</p>
							</div>

							<!-- Border Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_border_color">
									<?php esc_html_e( 'Border Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_border_color" 
										name="wpsps[border_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'border_color' ) ); ?>" 
										class="wpspcolor-picker"
										data-alpha-enabled="true"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Border', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Border color for input fields and elements', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>

					<!-- Error Colors Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-warning"></span>
							<?php esc_html_e( 'Error Colors', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Error Border Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_error_border_color">
									<?php esc_html_e( 'Error Border Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_error_border_color" 
										name="wpsps[error_border_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'error_border_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Error Border', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Border color for fields with errors', 'stripe' ); ?>
								</p>
							</div>

							<!-- Error Text Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_error_text_color">
									<?php esc_html_e( 'Error Text Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_error_text_color" 
										name="wpsps[error_text_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'error_text_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Error Text', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Text color for error messages', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>

					<!-- Form Title & Description Colors Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-text"></span>
							<?php esc_html_e( 'Form Title & Description Colors', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Title Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_title_color">
									<?php esc_html_e( 'Title Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_title_color" 
										name="wpsps[title_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'title_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Title', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Color for the form title', 'stripe' ); ?>
								</p>
							</div>

							<!-- Description Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_description_color">
									<?php esc_html_e( 'Description Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_description_color" 
										name="wpsps[description_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'description_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Description', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Color for the form description', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Typography Tab -->
				<div class="wpsp-admin-tab-panel" data-tab-content="typography">
					<!-- Label Typography Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-editor-textcolor"></span>
							<?php esc_html_e( 'Label Typography', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Label Font Size -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_label_font_size">
									<?php esc_html_e( 'Label Font Size', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-input-with-unit">
									<input 
										type="number" 
										id="wpsps_label_font_size" 
										name="wpsps[label_font_size]" 
										value="<?php echo esc_attr( Settings::get_setting( $post_id, 'label_font_size', '14' ) ); ?>" 
										step="1"
									/>
									<span class="wpsp-admin-unit">px</span>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Size of form field labels', 'stripe' ); ?>
								</p>
							</div>

							<!-- Label Font Weight -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_label_font_weight">
									<?php esc_html_e( 'Label Font Weight', 'stripe' ); ?>
								</label>
								<select id="wpsps_label_font_weight" name="wpsps[label_font_weight]" class="wpsp-admin-select">
									<?php
									$current_weight = Settings::get_setting( $post_id, 'label_font_weight' );
									$weight_options = array(
										''       => __( 'Theme Default', 'stripe' ),
										'normal' => __( 'Normal', 'stripe' ),
										'bold'   => __( 'Bold', 'stripe' ),
										'100'    => '100 (Thin)',
										'200'    => '200 (Extra Light)',
										'300'    => '300 (Light)',
										'400'    => '400 (Normal)',
										'500'    => '500 (Medium)',
										'600'    => '600 (Semi Bold)',
										'700'    => '700 (Bold)',
										'800'    => '800 (Extra Bold)',
										'900'    => '900 (Black)',
									);

									foreach ( $weight_options as $value => $label ) {
										printf(
											'<option value="%s" %s>%s</option>',
											esc_attr( (string) $value ),
											selected( $current_weight, $value, false ),
											esc_html( $label )
										);
									}
									?>
								</select>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Font weight for form labels', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>

					<!-- Input Typography Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-edit"></span>
							<?php esc_html_e( 'Input Typography', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Input Font Size -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_input_font_size">
									<?php esc_html_e( 'Input Font Size', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-input-with-unit">
									<input 
										type="number" 
										id="wpsps_input_font_size" 
										name="wpsps[input_font_size]" 
										value="<?php echo esc_attr( Settings::get_setting( $post_id, 'input_font_size', '16' ) ); ?>" 
										step="1"
									/>
									<span class="wpsp-admin-unit">px</span>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Size of text in form inputs', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>

					<!-- Form Title & Description Typography Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-text"></span>
							<?php esc_html_e( 'Form Title & Description Typography', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Title Font Size -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_title_font_size">
									<?php esc_html_e( 'Title Font Size', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-input-with-unit">
									<input 
										type="number" 
										id="wpsps_title_font_size" 
										name="wpsps[title_font_size]" 
										value="<?php echo esc_attr( Settings::get_setting( $post_id, 'title_font_size', '24' ) ); ?>" 
										step="1"
									/>
									<span class="wpsp-admin-unit">px</span>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Font size for the form title', 'stripe' ); ?>
								</p>
							</div>

							<!-- Title Font Weight -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_title_font_weight">
									<?php esc_html_e( 'Title Font Weight', 'stripe' ); ?>
								</label>
								<select id="wpsps_title_font_weight" name="wpsps[title_font_weight]" class="wpsp-admin-select">
									<?php
									$current_weight = Settings::get_setting( $post_id, 'title_font_weight' );
									$weight_options = array(
										''       => __( 'Theme Default', 'stripe' ),
										'normal' => __( 'Normal', 'stripe' ),
										'bold'   => __( 'Bold', 'stripe' ),
										'100'    => '100 (Thin)',
										'200'    => '200 (Extra Light)',
										'300'    => '300 (Light)',
										'400'    => '400 (Normal)',
										'500'    => '500 (Medium)',
										'600'    => '600 (Semi Bold)',
										'700'    => '700 (Bold)',
										'800'    => '800 (Extra Bold)',
										'900'    => '900 (Black)',
									);

									foreach ( $weight_options as $value => $label ) {
										printf(
											'<option value="%s" %s>%s</option>',
											esc_attr( (string) $value ),
											selected( $current_weight, $value, false ),
											esc_html( $label )
										);
									}
									?>
								</select>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Font weight for the form title', 'stripe' ); ?>
								</p>
							</div>

							<!-- Description Font Size -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_description_font_size">
									<?php esc_html_e( 'Description Font Size', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-input-with-unit">
									<input 
										type="number" 
										id="wpsps_description_font_size" 
										name="wpsps[description_font_size]" 
										value="<?php echo esc_attr( Settings::get_setting( $post_id, 'description_font_size', '16' ) ); ?>" 
										step="1"
									/>
									<span class="wpsp-admin-unit">px</span>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Font size for the form description', 'stripe' ); ?>
								</p>
							</div>

							<!-- Description Font Weight -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_description_font_weight">
									<?php esc_html_e( 'Description Font Weight', 'stripe' ); ?>
								</label>
								<select id="wpsps_description_font_weight" name="wpsps[description_font_weight]" class="wpsp-admin-select">
									<?php
									$current_weight = Settings::get_setting( $post_id, 'description_font_weight' );
									$weight_options = array(
										''       => __( 'Theme Default', 'stripe' ),
										'normal' => __( 'Normal', 'stripe' ),
										'bold'   => __( 'Bold', 'stripe' ),
										'100'    => '100 (Thin)',
										'200'    => '200 (Extra Light)',
										'300'    => '300 (Light)',
										'400'    => '400 (Normal)',
										'500'    => '500 (Medium)',
										'600'    => '600 (Semi Bold)',
										'700'    => '700 (Bold)',
										'800'    => '800 (Extra Bold)',
										'900'    => '900 (Black)',
									);

									foreach ( $weight_options as $value => $label ) {
										printf(
											'<option value="%s" %s>%s</option>',
											esc_attr( (string) $value ),
											selected( $current_weight, $value, false ),
											esc_html( $label )
										);
									}
									?>
								</select>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Font weight for the form description', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Design & Layout Tab -->
				<div class="wpsp-admin-tab-panel" data-tab-content="design">
					<!-- Layout Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-layout"></span>
							<?php esc_html_e( 'Layout Settings', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Form Border Radius -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_form_border_radius">
									<?php esc_html_e( 'Form Border Radius', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-input-with-unit">
									<input
										type="number"
										id="wpsps_form_border_radius"
										name="wpsps[form_border_radius]"
										value="<?php echo esc_attr( $is_new_form ? '0' : Settings::get_setting( $post_id, 'form_border_radius', '0' ) ); ?>"
										step="1"
										min="0"
									/>
									<span class="wpsp-admin-unit">px</span>
								</div>
								<div class="wpsp-admin-radius-preview">
									<div class="wpsp-admin-form-radius-box" style="border-radius: <?php echo esc_attr( $is_new_form ? '0' : Settings::get_setting( $post_id, 'form_border_radius', '0' ) ); ?>px;"></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Rounded corners for the form container', 'stripe' ); ?>
								</p>
							</div>

							<!-- Border Radius -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_border_radius">
									<?php esc_html_e( 'Input & Button Border Radius', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-input-with-unit">
									<input
										type="number"
										id="wpsps_border_radius"
										name="wpsps[border_radius]"
										value="<?php echo esc_attr( $is_new_form ? '3' : Settings::get_setting( $post_id, 'border_radius', '0' ) ); ?>"
										step="1"
									/>
									<span class="wpsp-admin-unit">px</span>
								</div>
								<div class="wpsp-admin-radius-preview wpsp-admin-radius-preview--inline">
									<div class="wpsp-admin-radius-input-preview" style="border-radius: <?php echo esc_attr( $is_new_form ? '3' : Settings::get_setting( $post_id, 'border_radius', '0' ) ); ?>px;"><span><?php esc_html_e( 'Email address', 'stripe' ); ?></span></div>
									<div class="wpsp-admin-radius-button-preview" style="border-radius: <?php echo esc_attr( $is_new_form ? '3' : Settings::get_setting( $post_id, 'border_radius', '0' ) ); ?>px;"><?php esc_html_e( 'Pay', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Rounded corners for inputs and buttons', 'stripe' ); ?>
								</p>
							</div>

							<!-- Form Padding -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_form_padding">
									<?php esc_html_e( 'Form Padding', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-input-with-unit">
									<input 
										type="number" 
										id="wpsps_form_padding" 
										name="wpsps[form_padding]" 
										value="<?php echo esc_attr( Settings::get_setting( $post_id, 'form_padding', '' ) ); ?>" 
										step="1"
										min="0"
									/>
									<span class="wpsp-admin-unit">px</span>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Padding around the form container', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>

					<!-- Button Styling Section -->
					<div class="wpsp-admin-color-section">
						<h3 class="wpsp-admin-section-title">
							<span class="dashicons dashicons-button"></span>
							<?php esc_html_e( 'Button Styling', 'stripe' ); ?>
						</h3>
						<div class="wpsp-admin-form-grid">
							<!-- Button Background Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_button_background_color">
									<?php esc_html_e( 'Button Background', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_button_background_color" 
										name="wpsps[button_background_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'button_background_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-button-preview" style="background-color: <?php echo esc_attr( $is_new_form ? '#0f8569' : Settings::get_setting( $post_id, 'button_background_color', '#0f8569' ) ); ?>; color: <?php echo esc_attr( $is_new_form ? '#ffffff' : Settings::get_setting( $post_id, 'button_text_color', '#ffffff' ) ); ?>">
										<?php esc_html_e( 'Button Preview', 'stripe' ); ?>
									</div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Background color for form buttons', 'stripe' ); ?>
								</p>
							</div>

							<!-- Button Text Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_button_text_color">
									<?php esc_html_e( 'Button Text Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_button_text_color" 
										name="wpsps[button_text_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'button_text_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-color-preview-label"><?php esc_html_e( 'Text', 'stripe' ); ?></div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Text color for form buttons', 'stripe' ); ?>
								</p>
							</div>

							<!-- Button Hover Background Color -->
							<div class="wpsp-admin-form-field">
								<label for="wpsps_button_hover_background_color">
									<?php esc_html_e( 'Button Hover Color', 'stripe' ); ?>
								</label>
								<div class="wpsp-admin-color-preview-wrap">
									<input 
										type="text" 
										id="wpsps_button_hover_background_color" 
										name="wpsps[button_hover_background_color]" 
										value="<?php echo esc_attr( Settings::get_raw_setting( $post_id, 'button_hover_background_color' ) ); ?>" 
										class="wpspcolor-picker"
									/>
									<div class="wpsp-admin-button-preview wpsp-admin-button-hover" style="background-color: <?php echo esc_attr( $is_new_form ? '#0e7c62' : Settings::get_setting( $post_id, 'button_hover_background_color', '#0e7c62' ) ); ?>; color: <?php echo esc_attr( $is_new_form ? '#ffffff' : Settings::get_setting( $post_id, 'button_text_color', '#ffffff' ) ); ?>">
										<?php esc_html_e( 'Hover Preview', 'stripe' ); ?>
									</div>
								</div>
								<p class="wpsp-admin-field-description">
									<?php esc_html_e( 'Background color when hovering over buttons', 'stripe' ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Reset Button and Action Area. -->
			<div class="wpsp-admin-actions">
				<button type="button" id="wpsp-admin-reset-styles" class="button button-secondary">
					<span class="dashicons dashicons-image-rotate"></span>
					<?php esc_html_e( 'Reset Styles', 'stripe' ); ?>
				</button>
				<p class="wpsp-admin-reset-description">
					<?php esc_html_e( 'Resets all style settings to default. This action cannot be undone.', 'stripe' ); ?>
				</p>
			</div>
		</div>
		<?php
	}


	/**
	 * Saves the style settings data when the post is saved.
	 *
	 * Handles validation and saving of style settings
	 * when a WP Simple Pay form is saved.
	 *
	 * @since 4.17.0
	 *
	 * @param int      $post_id The post ID.
	 * @param \WP_Post $post    The post object.
	 * @return void
	 */
	public function save_style_settings( $post_id, $post ) {
		// Check if our nonce is set.
		if ( ! isset( $_POST['wpsps_styles_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['wpsps_styles_nonce'], 'wpsps_save_styles' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if the post type is correct.
		if ( 'simple-pay' !== $post->post_type ) {
			return;
		}

		// Check if reset action was triggered.
		$is_reset = isset( $_POST['wpsps_reset'] ) && 'true' === $_POST['wpsps_reset'];

		if ( $is_reset ) {
			// Delete all style settings if reset was requested.
			$style_keys = Settings::get_style_keys();
			foreach ( $style_keys as $key ) {
				Settings::delete_setting( $post_id, $key );
			}
			return;
		}

		$settings_data = isset( $_POST['wpsps'] ) && is_array( $_POST['wpsps'] ) ? $_POST['wpsps'] : array();
		$style_keys    = Settings::get_style_keys();

		foreach ( $style_keys as $key ) {
			// Check if the setting is in the POST data (even if empty).
			if ( array_key_exists( $key, $settings_data ) ) {
				$raw_value = $settings_data[ $key ];

				// Save the setting (even if empty, to clear it).
				// Always save, even if the value is empty, to allow clearing settings.
				$result = Settings::save_setting( $post_id, $key, $raw_value );

			} else {
				// Only delete setting if it's not in POST at all (not just empty).
				// This allows users to clear values by submitting empty strings.
				// Don't delete here - let existing values persist if not in POST.
			}
		}
	}

	/**
	 * Renders the upsell content for form styles when license is insufficient.
	 *
	 * Shows an upgrade notice for users who don't have Plus license or higher.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	private function render_style_upsell() {
		?>
		<div class="simpay-settings-upgrade">
			<div class="simpay-settings-upgrade__inner">
				<span class="dashicons dashicons-admin-appearance" style="font-size: 40px; width: 40px; height: 50px;"></span>
				<h3>
					<?php esc_html_e( 'Unlock Payment Form Styles', 'stripe' ); ?>
				</h3>
				<p>
					<?php
					echo wp_kses(
						__( 'We\'re sorry, payment form styling is not available with your current license. Please upgrade to <strong>WP Simple Pay Pro</strong> or higher to unlock this and other awesome features.', 'stripe' ),
						array( 'strong' => array() )
					);
					?>
				</p>

				<ul>
					<li>
						<div class="dashicons dashicons-yes"></div>
						<?php esc_html_e( 'Professional Color Themes', 'stripe' ); ?>
					</li>
					<li>
						<div class="dashicons dashicons-yes"></div>
						<?php esc_html_e( 'Custom Colors & Typography', 'stripe' ); ?>
					</li>
					<li>
						<div class="dashicons dashicons-yes"></div>
						<?php esc_html_e( 'Button & Layout Customization', 'stripe' ); ?>
					</li>
					<li>
						<div class="dashicons dashicons-yes"></div>
						<?php esc_html_e( 'Match Your Brand Perfectly', 'stripe' ); ?>
					</li>
				</ul>

				<p>
					<a href="<?php echo esc_url( simpay_pro_upgrade_url( 'form-style-settings', 'Form Styles' ) ); ?>" class="button button-primary button-large" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Upgrade to WP Simple Pay Pro', 'stripe' ); ?>
					</a>
				</p>

				<p class="description">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
							__(
								'Already purchased? %1$sRetrieve your license key%2$s and enter it in the plugin settings.',
								'stripe'
							),
							sprintf(
								'<a href="%s" target="_blank" rel="noopener noreferrer">',
								esc_url( 'https://wpsimplepay.com/my-account/licenses/' )
							),
							'</a>'
						),
						array(
							'a' => array(
								'href'   => true,
								'target' => true,
								'rel'    => true,
							),
						)
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

}
