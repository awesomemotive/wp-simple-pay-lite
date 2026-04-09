<?php
/**
 * Handles frontend filtering and style application.
 *
 * @package SimplePay\Core\Admin\FormBuilder\FormStyle
 */

namespace SimplePay\Core\Admin\FormBuilder\FormStyle;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend handler class.
 *
 * Handles frontend filtering and style application for WP Simple Pay forms.
 * Modifies Stripe Elements configuration and generates custom CSS for forms.
 *
 * @since 1.0.0
 */
class Frontend {

	/**
	 * The single instance of the class.
	 *
	 * @var Frontend
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Stores the IDs of forms rendered on the current page.
	 *
	 * @since 1.0.0
	 * @var array<int>
	 */
	private static $rendered_form_ids = array();

	/**
	 * Get the singleton instance.
	 *
	 * Ensures only one instance of Frontend is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @return Frontend The single instance of this class.
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
	 * Initializes the class and sets up hooks.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		$this->hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function hooks(): void {
		// Hook unconditionally to modify Elements config.
		add_filter( 'simpay_elements_config', array( $this, 'modify_elements_config' ), 10, 2 );

		// Hook to capture rendered form IDs (for inline CSS and for Elements config).
		add_action( 'simpay_form_before_form_top', array( $this, 'capture_rendered_form_id' ), 10, 1 ); // Only need form ID.

		// Hook late to print inline CSS for non-Stripe elements.
		add_action( 'wp_print_footer_scripts', array( $this, 'print_late_frontend_styles' ), 100 ); // Using a late hook.

		// Hook to add data attribute to embedded heading via JavaScript.
		add_action( 'wp_print_footer_scripts', array( $this, 'inject_heading_data_attributes' ), 99 );
	}

	/**
	 * Filters the Stripe Elements configuration array based on saved settings.
	 *
	 * Modifies the Stripe Elements appearance configuration to apply custom styles
	 * based on the saved settings for the current form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config The original Elements configuration.
	 * @param int   $form_id The form ID (optional, for backwards compatibility).
	 * @return array The modified Elements configuration.
	 */
	public function modify_elements_config( $config, $form_id = null ) {
		// Use the provided form ID if available, otherwise fall back to the last rendered form ID.
		if ( null === $form_id ) {
			// Try to get the relevant form ID from the rendered list (backwards compatibility).
			if ( empty( self::$rendered_form_ids ) ) {
				return $config;
			}
			$form_id = end( self::$rendered_form_ids );
			if ( ! $form_id ) {
				return $config;
			}
		}

		// Check if styling is applicable for this form type.
		$display_type = get_post_meta( $form_id, '_form_display_type', true );
		if ( ! in_array( $display_type, array( 'embedded', 'overlay' ), true ) ) {
			return $config;
		}

		// --- Apply styles ---
		// Ensure appearance key exists.
		if ( ! isset( $config['appearance'] ) ) {
			$config['appearance'] = array();
		}
		if ( ! isset( $config['appearance']['variables'] ) ) {
			$config['appearance']['variables'] = array();
		}
		if ( ! isset( $config['appearance']['rules'] ) ) {
			$config['appearance']['rules'] = array();
		}

		// Apply saved styles (Logic remains the same as before).
		$primary_color = Settings::get_setting( $form_id, 'primary_color' );
		if ( $primary_color ) {
			$config['appearance']['variables']['colorPrimary']                   = $primary_color;
			$config['appearance']['rules']['.Tab:focus']['boxShadow']            = 'inset 0 -4px ' . $primary_color;
			$config['appearance']['rules']['.Tab:hover']['boxShadow']            = 'inset 0 -4px ' . $primary_color;
			$config['appearance']['rules']['.Tab--selected']['boxShadow']        = 'inset 0 -2px ' . $primary_color;
			$config['appearance']['rules']['.Tab--selected:focus']['boxShadow']  = 'inset 0 -4px ' . $primary_color;
			$config['appearance']['rules']['.Input:focus']['boxShadow']          = sprintf(
				'0 0 0 1px %1$s, 0 0 0 3px %2$s, 0 1px 2px rgba(0, 0, 0, 0.05)',
				$primary_color,
				self::hex_to_rgba( $primary_color, 0.15 )
			);
			$config['appearance']['rules']['.CodeInput:focus']['boxShadow']      = $config['appearance']['rules']['.Input:focus']['boxShadow'];
			$config['appearance']['rules']['.CheckboxInput:focus']['boxShadow']  = $config['appearance']['rules']['.Input:focus']['boxShadow'];
			$config['appearance']['rules']['.PickerItem--selected']['boxShadow'] = $config['appearance']['rules']['.Input:focus']['boxShadow'];

			// Add focus styling for dropdown selects.
			$config['appearance']['rules']['.p-Select-select:focus']['boxShadow']   = $config['appearance']['rules']['.Input:focus']['boxShadow'];
			$config['appearance']['rules']['.p-Select-select:focus']['borderColor'] = $primary_color;
		}

		$background_color = Settings::get_setting( $form_id, 'background_color' );
		if ( $background_color ) {
			// Don't set colorBackground variable as it affects all elements including labels.
			// Only apply background color to text input fields (text, email, number, etc.), not selects or checkboxes.

			// Add explicit background color for text input types only.
			$config['appearance']['rules']['.Input']['backgroundColor']     = $background_color;
			$config['appearance']['rules']['.CodeInput']['backgroundColor'] = $background_color;

			// Explicitly ensure labels, selects, checkboxes, and other non-text inputs don't get the input background color.
			$config['appearance']['rules']['.Label']['backgroundColor']           = 'transparent';
			$config['appearance']['rules']['.TabLabel']['backgroundColor']        = 'transparent';
			$config['appearance']['rules']['.CheckboxInput']['backgroundColor']   = 'transparent';
			$config['appearance']['rules']['.p-Select-select']['backgroundColor'] = 'transparent';
			$config['appearance']['rules']['.p-Select-option']['backgroundColor'] = 'transparent';
			$config['appearance']['rules']['.p-FauxInput']['backgroundColor']     = 'transparent';
		}

		$text_color = Settings::get_setting( $form_id, 'text_color' );
		if ( $text_color ) {
			$config['appearance']['variables']['colorText']              = $text_color;
			$config['appearance']['rules']['.TabLabel']['color']         = $text_color;
			$config['appearance']['rules']['.Label']['color']            = $text_color;
			$config['appearance']['rules']['.Input']['color']            = $text_color;
			$config['appearance']['rules']['.CodeInput']['color']        = $text_color;
			$config['appearance']['rules']['.PickerItem']['color']       = $text_color;
			$config['appearance']['rules']['.DropdownItem']['color']     = $text_color;
			$config['appearance']['rules']['.TabIcon--selected']['fill'] = $text_color;

			// Add text color for select dropdown and options.
			$config['appearance']['rules']['.p-Select-select']['color'] = $text_color;
			$config['appearance']['rules']['.p-Select-option']['color'] = $text_color;
			$config['appearance']['rules']['option']['color']           = $text_color;
		}

		// Apply label text color (overrides general text color for labels).
		$label_text_color = Settings::get_setting( $form_id, 'label_text_color' );
		if ( $label_text_color ) {
			$config['appearance']['rules']['.Label']['color']    = $label_text_color;
			$config['appearance']['rules']['.TabLabel']['color'] = $label_text_color;
		}

		// Apply input text color (overrides general text color for inputs).
		$input_text_color = Settings::get_setting( $form_id, 'input_text_color' );
		if ( $input_text_color ) {
			$config['appearance']['rules']['.Input']['color']           = $input_text_color;
			$config['appearance']['rules']['.CodeInput']['color']       = $input_text_color;
			$config['appearance']['rules']['.PickerItem']['color']      = $input_text_color;
			$config['appearance']['rules']['.DropdownItem']['color']    = $input_text_color;
			$config['appearance']['rules']['.p-Select-select']['color'] = $input_text_color;
			$config['appearance']['rules']['.p-Select-option']['color'] = $input_text_color;
			$config['appearance']['rules']['option']['color']           = $input_text_color;
			$config['appearance']['rules']['.p-FauxInput']['color']     = $input_text_color;
		}

		// Apply border color.
		$border_color = Settings::get_setting( $form_id, 'border_color' );
		if ( $border_color ) {
			// Add border color to inputs.
			$config['appearance']['rules']['.Input']['boxShadow']             = '0 0 0 1px ' . $border_color . ', 0 1px 2px rgba(0, 0, 0, 0.05)';
			$config['appearance']['rules']['.CodeInput']['boxShadow']         = $config['appearance']['rules']['.Input']['boxShadow'];
			$config['appearance']['rules']['.CheckboxInput']['boxShadow']     = $config['appearance']['rules']['.Input']['boxShadow'];
			$config['appearance']['rules']['.p-Select-select']['boxShadow']   = $config['appearance']['rules']['.Input']['boxShadow'];
			$config['appearance']['rules']['.p-Select-select']['borderColor'] = $border_color;
			$config['appearance']['rules']['.Input']['borderColor']           = $border_color;
		}

		// Apply error border color.
		$error_border_color = Settings::get_setting( $form_id, 'error_border_color' );
		if ( $error_border_color ) {
			$config['appearance']['rules']['.Input--invalid']['boxShadow']   = '0 0 0 1px ' . $error_border_color . ', 0 1px 2px rgba(0, 0, 0, 0.05)';
			$config['appearance']['rules']['.Input--invalid']['borderColor'] = $error_border_color;
		}

		// Apply error text color.
		$error_text_color = Settings::get_setting( $form_id, 'error_text_color' );
		if ( $error_text_color ) {
			$config['appearance']['rules']['.Error']['color']        = $error_text_color;
			$config['appearance']['rules']['.ErrorMessage']['color'] = $error_text_color;
		}

		$border_radius = Settings::get_setting( $form_id, 'border_radius', '' );
		// Only apply border radius if it has been explicitly set.
		if ( '' !== $border_radius && Settings::setting_exists( $form_id, 'border_radius' ) ) {
			$config['appearance']['variables']['borderRadius'] = $border_radius . 'px';

			// Add explicit border radius for select elements.
			$config['appearance']['rules']['.p-Select-select']['borderRadius'] = $border_radius . 'px';
		}

		$input_font_size = Settings::get_setting( $form_id, 'input_font_size' );
		if ( $input_font_size ) {
			$config['appearance']['rules']['.Input']['fontSize']      = $input_font_size . 'px';
			$config['appearance']['rules']['.CodeInput']['fontSize']  = $input_font_size . 'px';
			$config['appearance']['rules']['.PickerItem']['fontSize'] = $input_font_size . 'px';

			// Add font size for select elements.
			$config['appearance']['rules']['.p-Select-select']['fontSize'] = $input_font_size . 'px';
			$config['appearance']['rules']['.p-Select-option']['fontSize'] = $input_font_size . 'px';
			$config['appearance']['rules']['option']['fontSize']           = $input_font_size . 'px';
		}

		$label_font_size = Settings::get_setting( $form_id, 'label_font_size' );
		if ( $label_font_size ) {
			$config['appearance']['rules']['.Label']['fontSize']    = $label_font_size . 'px';
			$config['appearance']['rules']['.TabLabel']['fontSize'] = $label_font_size . 'px';
		}

		$label_font_weight = Settings::get_setting( $form_id, 'label_font_weight' );
		if ( $label_font_weight ) {
			$config['appearance']['rules']['.Label']['fontWeight']    = $label_font_weight;
			$config['appearance']['rules']['.TabLabel']['fontWeight'] = $label_font_weight;
		}
		// --- End Apply styles. ---

		// Remove empty appearance sub-keys so they don't serialize as JSON
		// arrays ([]) instead of objects ({}), and so the JS auto-detection
		// fallback in elements.js can run when no custom styles are configured.
		if ( empty( $config['appearance']['variables'] ) ) {
			unset( $config['appearance']['variables'] );
		}

		if ( empty( $config['appearance']['rules'] ) ) {
			unset( $config['appearance']['rules'] );
		}

		if ( empty( $config['appearance'] ) ) {
			unset( $config['appearance'] );
		}

		return $config;
	}

	/**
	 * Capture the ID of a form being rendered on the page.
	 *
	 * Stores the form ID in a static array to be used later for styling.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id The ID of the form being rendered.
	 * @return void
	 */
	public function capture_rendered_form_id( $form_id ) {
		$form_id = absint( $form_id );
		if ( $form_id > 0 && ! in_array( $form_id, self::$rendered_form_ids, true ) ) {
			self::$rendered_form_ids[] = $form_id;
		}
	}

	/**
	 * Prints inline CSS late in the footer for non-Elements form parts.
	 *
	 * Generates and outputs custom CSS for all rendered forms on the current page.
	 * This CSS targets non-Stripe Elements parts of the form.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print_late_frontend_styles() {
		if ( empty( self::$rendered_form_ids ) ) {
			return;
		}

		$css         = '';
		$preview_css = ''; // Separate CSS for preview wrapper.

		foreach ( self::$rendered_form_ids as $form_id ) {
			$display_type = get_post_meta( $form_id, '_form_display_type', true );
			if ( ! in_array( $display_type, array( 'embedded', 'overlay' ), true ) ) {
				continue;
			}

			// Get the base CSS from our generator method.
			$form_css = $this->generate_custom_css( $form_id );
			$css     .= $form_css;

			// Add legacy CSS selectors for backwards compatibility.
			$live_wrapper_selector = "#simpay-embedded-form-wrap-{$form_id}"; // Target the outer wrapper div by ID.

			// --- Form Container Background Color & Padding
			$form_bg_color = Settings::get_setting( $form_id, 'form_container_background_color' );
			if ( $form_bg_color ) {
				// Apply background to the specific live wrapper ID (no padding).
				$css .= "{$live_wrapper_selector} {
					background-color: " . esc_attr( $form_bg_color ) . " !important;
					margin: 0 auto !important;
				}\n";
				// Apply background only to preview wrapper.
				$preview_css .= 'body.simpay-form-preview .simpay-form-preview-wrap { background: ' . esc_attr( $form_bg_color ) . " !important; }\n";
			}

			// --- Form Container Border Radius for preview wrapper.
			$form_border_radius = Settings::get_setting( $form_id, 'form_border_radius', '' );
			if ( '' !== $form_border_radius && Settings::setting_exists( $form_id, 'form_border_radius' ) ) {
				$preview_css .= 'body.simpay-form-preview .simpay-form-preview-wrap { border-radius: ' . absint( $form_border_radius ) . "px !important; }\n";
			}
		}

		// Combine live CSS and preview CSS.
		$final_css = $preview_css . $css;

		// Output the combined CSS.
		if ( ! empty( $final_css ) ) {
			// Directly print the CSS in the footer.
			echo "\n<style id=\"wpsp-admin-inline-styles-late\">\n";
			echo $final_css; // WPCS: XSS okay.
			echo "</style>\n";
		}
	}

	/**
	 * Get the custom CSS for a specific form.
	 *
	 * Public accessor for generate_custom_css(), used by the Gutenberg block
	 * preview where wp_print_footer_scripts does not fire.
	 *
	 * @since 4.17.0
	 *
	 * @param int $form_id The form ID.
	 * @return string The generated CSS, or empty string if not applicable.
	 */
	public function get_form_css( $form_id ) {
		$display_type = get_post_meta( $form_id, '_form_display_type', true );
		if ( ! in_array( $display_type, array( 'embedded', 'overlay' ), true ) ) {
			return '';
		}

		return $this->generate_custom_css( $form_id );
	}

	/**
	 * Helper function to convert hex color to rgba.
	 *
	 * Converts a hexadecimal color value to an rgba color value with the specified opacity.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @param string $color   The hexadecimal color value.
	 * @param float  $opacity The opacity value (0-1).
	 * @return string The rgba color value.
	 */
	private static function hex_to_rgba( $color, $opacity = 1 ) {
		// If the color is already in rgba/rgb format, return it directly.
		if ( preg_match( '/^rgba?\(/', $color ) ) {
			return $color;
		}

		$color = ltrim( $color, '#' );
		if ( strlen( $color ) === 3 ) {
			$r = hexdec( substr( $color, 0, 1 ) . substr( $color, 0, 1 ) );
			$g = hexdec( substr( $color, 1, 1 ) . substr( $color, 1, 1 ) );
			$b = hexdec( substr( $color, 2, 1 ) . substr( $color, 2, 1 ) );
		} elseif ( strlen( $color ) === 6 ) {
			$r = hexdec( substr( $color, 0, 2 ) );
			$g = hexdec( substr( $color, 2, 2 ) );
			$b = hexdec( substr( $color, 4, 2 ) );
		} else {
			return 'rgba(0,0,0,0)'; // Invalid color.
		}
		$opacity = max( 0, min( 1, $opacity ) );
		return sprintf( 'rgba(%d,%d,%d,%.2f)', $r, $g, $b, $opacity );
	}

	/**
	 * Generate the custom CSS for a form based on its style settings.
	 *
	 * Creates CSS rules for a specific form based on its saved style settings.
	 * This CSS targets standard HTML elements that are not controlled by Stripe Elements.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param int $form_id The form ID.
	 * @return string The generated CSS.
	 */
	private function generate_custom_css( $form_id ) {
		$css = '';

		// Only add container width to make sure the form doesn't get squeezed.
		$css .= "
		/* Form container width */
		#simpay-embedded-form-wrap-{$form_id} {
			max-width: none !important;
		}";

		// Now add the theme-specific styles
		// Add form container background color.
		$form_bg_color = Settings::get_setting( $form_id, 'form_container_background_color' );
		if ( ! empty( $form_bg_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] { background-color: {$form_bg_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} { background-color: {$form_bg_color} !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} { background-color: {$form_bg_color} !important; }\n";
			$css .= ".simpay-checkout-form--embedded[data-form-id=\"{$form_id}\"] { background-color: {$form_bg_color} !important; }\n";
			// Apply to overlay modal content.
			$css .= ".simpay-modal__content[data-form-id=\"{$form_id}\"] { background-color: {$form_bg_color} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content { background-color: {$form_bg_color} !important; }\n";
			$css .= ".simpay-checkout-form--overlay[data-form-id=\"{$form_id}\"] { background-color: {$form_bg_color} !important; }\n";
		}

		// Add form padding.
		$form_padding = Settings::get_setting( $form_id, 'form_padding', '' );
		if ( '' !== $form_padding && Settings::setting_exists( $form_id, 'form_padding' ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] { padding: {$form_padding}px !important; }\n";
			$css .= "#simpay-form-{$form_id} { padding: {$form_padding}px !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} { padding: {$form_padding}px !important; }\n";
			$css .= ".simpay-checkout-form--embedded[data-form-id=\"{$form_id}\"] { padding: {$form_padding}px !important; }\n";
			// Apply to overlay modal content.
			$css .= ".simpay-modal__content[data-form-id=\"{$form_id}\"] { padding: {$form_padding}px !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content { padding: {$form_padding}px !important; }\n";
			$css .= ".simpay-checkout-form--overlay[data-form-id=\"{$form_id}\"] { padding: {$form_padding}px !important; }\n";
		}

		// Add form background color (input fields - text inputs only).
		$bg_color = Settings::get_setting( $form_id, 'background_color' );
		if ( ! empty( $bg_color ) ) {
			// Target only text input types (text, email, number, tel, password), NOT selects, checkboxes, or textareas.
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='text'],\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='email'],\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='tel'],\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='number'],\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='password'],\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='url'],\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='search'] { background-color: {$bg_color} !important; }\n";

			$css .= "#simpay-form-{$form_id} input[type='text'],\n";
			$css .= "#simpay-form-{$form_id} input[type='email'],\n";
			$css .= "#simpay-form-{$form_id} input[type='tel'],\n";
			$css .= "#simpay-form-{$form_id} input[type='number'],\n";
			$css .= "#simpay-form-{$form_id} input[type='password'],\n";
			$css .= "#simpay-form-{$form_id} input[type='url'],\n";
			$css .= "#simpay-form-{$form_id} input[type='search'] { background-color: {$bg_color} !important; }\n";

			// Target Stripe Elements text inputs only (not selects or checkboxes).
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Input { background-color: {$bg_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .CodeInput { background-color: {$bg_color} !important; }\n";

			// Explicitly ensure selects, checkboxes, and other non-text inputs don't get the background color.
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] select { background-color: transparent !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] textarea { background-color: transparent !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='checkbox'] { background-color: transparent !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input[type='radio'] { background-color: transparent !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .p-Select-select { background-color: transparent !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .CheckboxInput { background-color: transparent !important; }\n";

			$css .= "#simpay-form-{$form_id} select { background-color: transparent !important; }\n";
			$css .= "#simpay-form-{$form_id} textarea { background-color: transparent !important; }\n";
			$css .= "#simpay-form-{$form_id} input[type='checkbox'] { background-color: transparent !important; }\n";
			$css .= "#simpay-form-{$form_id} input[type='radio'] { background-color: transparent !important; }\n";
		}

		// Add text color (general text color - applies to all text elements).
		$text_color = Settings::get_setting( $form_id, 'text_color' );
		if ( ! empty( $text_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] * { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-form-control { color: {$text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} { color: {$text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} * { color: {$text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-form-control { color: {$text_color} !important; }\n";

			// Target Stripe Elements text colors - including dropdowns, inputs and labels.
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Input { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement select.Input { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .p-Select-select { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Label { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement input { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement option { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement p { color: {$text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement span { color: {$text_color} !important; }\n";
		}

		// Add label text color (overrides text color for labels).
		$label_text_color = Settings::get_setting( $form_id, 'label_text_color' );
		if ( ! empty( $label_text_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] label { color: {$label_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-label { color: {$label_text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} label { color: {$label_text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-label { color: {$label_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Label { color: {$label_text_color} !important; }\n";
		}

		// Add input text color (overrides text color for inputs).
		$input_text_color = Settings::get_setting( $form_id, 'input_text_color' );
		if ( ! empty( $input_text_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input { color: {$input_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] select { color: {$input_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] textarea { color: {$input_text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} input { color: {$input_text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} select { color: {$input_text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} textarea { color: {$input_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Input { color: {$input_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement select.Input { color: {$input_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .p-Select-select { color: {$input_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement input { color: {$input_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement option { color: {$input_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .p-FauxInput { color: {$input_text_color} !important; }\n";
		}

		// Add border color.
		$border_color = Settings::get_setting( $form_id, 'border_color' );
		if ( ! empty( $border_color ) ) {
			// Native HTML inputs use box-shadow for borders (base CSS sets border: 0).
			$box_shadow_border = "box-shadow: 0 0 0 1px {$border_color}, 0 1px 2px rgba(0, 0, 0, 0.05) !important;";

			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-form-control { border-color: {$border_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-form-control { border-color: {$border_color} !important; }\n";
			$css .= "#simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='text'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='email'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='tel'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='number'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='password'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='url'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='search'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control select, #simpay-form-{$form_id}.simpay-styled .simpay-form-control textarea { {$box_shadow_border} }\n";

			// Add specific style for Stripe elements.
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement { border-color: {$border_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Input { {$box_shadow_border} border-color: {$border_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .CodeInput { {$box_shadow_border} border-color: {$border_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .p-Select-select { border-color: {$border_color} !important; }\n";
		}

		// Add error border color.
		$error_border_color = Settings::get_setting( $form_id, 'error_border_color' );
		if ( ! empty( $error_border_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-form-control.error { border-color: {$error_border_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-form-control.has-error { border-color: {$error_border_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] input.error { border-color: {$error_border_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Input--invalid { box-shadow: 0 0 0 1px {$error_border_color}, 0 1px 2px rgba(0, 0, 0, 0.05) !important; border-color: {$error_border_color} !important; }\n";
		}

		// Add error text color.
		$error_text_color = Settings::get_setting( $form_id, 'error_text_color' );
		if ( ! empty( $error_text_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-error { color: {$error_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-field-error { color: {$error_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Error { color: {$error_text_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .ErrorMessage { color: {$error_text_color} !important; }\n";
		}

		// Add primary color (accent color - used for focus states, links, etc.).
		$primary_color = Settings::get_setting( $form_id, 'primary_color' );
		if ( ! empty( $primary_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-form-control:focus { border-color: {$primary_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] a { color: {$primary_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-form-control:focus { border-color: {$primary_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} a { color: {$primary_color} !important; }\n";

			// Target Stripe Elements focus states.
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Input:focus { border-color: {$primary_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement select.Input:focus { border-color: {$primary_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .p-Select-select:focus { border-color: {$primary_color} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Input:focus { box-shadow: 0 0 0 1px {$primary_color}, 0 0 0 3px " . self::hex_to_rgba( $primary_color, 0.15 ) . ", 0 1px 2px rgba(0, 0, 0, 0.05) !important; }\n";
		}

		// Add form container border radius (separate from input/button border radius).
		$form_border_radius = Settings::get_setting( $form_id, 'form_border_radius', '' );
		if ( '' !== $form_border_radius && Settings::setting_exists( $form_id, 'form_border_radius' ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] { border-radius: {$form_border_radius}px !important; }\n";
			$css .= "#simpay-form-{$form_id} { border-radius: {$form_border_radius}px !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} { border-radius: {$form_border_radius}px !important; }\n";
			$css .= ".simpay-checkout-form--embedded[data-form-id=\"{$form_id}\"] { border-radius: {$form_border_radius}px !important; }\n";
			// Apply to overlay form modal content.
			$css .= ".simpay-modal__content[data-form-id=\"{$form_id}\"] { border-radius: {$form_border_radius}px !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content { border-radius: {$form_border_radius}px !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] { border-radius: {$form_border_radius}px !important; }\n";
		}

		// Add border radius for inputs, buttons, and form controls.
		$border_radius = Settings::get_setting( $form_id, 'border_radius', '' );
		if ( '' !== $border_radius && Settings::setting_exists( $form_id, 'border_radius' ) ) {
			// For form controls.
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-form-control { border-radius: {$border_radius}px !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-form-control { border-radius: {$border_radius}px !important; }\n";
			// Override .simpay-styled .simpay-form-control input/select/textarea border-radius, scoped to this form.
			$css .= "#simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='text'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='email'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='tel'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='number'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='password'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='url'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control input[type='search'], #simpay-form-{$form_id}.simpay-styled .simpay-form-control select, #simpay-form-{$form_id}.simpay-styled .simpay-form-control textarea { border-radius: {$border_radius}px !important; }\n";

			// Target Stripe Elements border radius.
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .Input { border-radius: {$border_radius}px !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement select.Input { border-radius: {$border_radius}px !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .StripeElement .p-Select-select { border-radius: {$border_radius}px !important; }\n";

			// Apply to buttons.
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-payment-btn { border-radius: {$border_radius}px !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-payment-btn { border-radius: {$border_radius}px !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-checkout-btn { border-radius: {$border_radius}px !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-apply-coupon { border-radius: {$border_radius}px !important; }\n";

			// Target buttons with higher specificity to override WP Simple Pay defaults.
			$css .= "body .simpay-form-wrap[data-form-id=\"{$form_id}\"] button.simpay-payment-btn { border-radius: {$border_radius}px !important; }\n";
			$css .= "body #simpay-form-{$form_id} button.simpay-checkout-btn { border-radius: {$border_radius}px !important; }\n";
			$css .= "body #simpay-form-{$form_id} button.simpay-apply-coupon { border-radius: {$border_radius}px !important; }\n";
		}

		// Add button background color.
		$button_bg_color = Settings::get_raw_setting( $form_id, 'button_background_color' );
		if ( ! empty( $button_bg_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-payment-btn { background-color: {$button_bg_color} !important; border-color: {$button_bg_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-payment-btn { background-color: {$button_bg_color} !important; border-color: {$button_bg_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-checkout-btn { background-color: {$button_bg_color} !important; border-color: {$button_bg_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-apply-coupon { background-color: {$button_bg_color} !important; border-color: {$button_bg_color} !important; }\n";
		}

		// Add button text color.
		// Default to white when text_color is set, since the text_color wildcard
		// (* selector) would otherwise override the button's white text from SCSS.
		$button_text_color = Settings::get_raw_setting( $form_id, 'button_text_color' );
		if ( empty( $button_text_color ) && ! empty( $text_color ) ) {
			$button_text_color = '#ffffff';
		}
		if ( ! empty( $button_text_color ) ) {
			// Target the button and all children (e.g. <span> inside <button>)
			// to override the text_color wildcard (* selector).
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-payment-btn, .simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-payment-btn * { color: {$button_text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-payment-btn, #simpay-form-{$form_id} .simpay-payment-btn * { color: {$button_text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-checkout-btn, #simpay-form-{$form_id} .simpay-checkout-btn * { color: {$button_text_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-apply-coupon, #simpay-form-{$form_id} .simpay-apply-coupon * { color: {$button_text_color} !important; }\n";
		}

		// Add button hover background color.
		$button_hover_bg_color = Settings::get_raw_setting( $form_id, 'button_hover_background_color' );
		if ( ! empty( $button_hover_bg_color ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-payment-btn:hover { background-color: {$button_hover_bg_color} !important; border-color: {$button_hover_bg_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-payment-btn:hover { background-color: {$button_hover_bg_color} !important; border-color: {$button_hover_bg_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-checkout-btn:hover { background-color: {$button_hover_bg_color} !important; border-color: {$button_hover_bg_color} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-apply-coupon:hover { background-color: {$button_hover_bg_color} !important; border-color: {$button_hover_bg_color} !important; }\n";
		}

		// Add font sizes.
		$label_font_size = Settings::get_setting( $form_id, 'label_font_size' );
		if ( ! empty( $label_font_size ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] label { font-size: {$label_font_size}px !important; }\n";
			$css .= "#simpay-form-{$form_id} label { font-size: {$label_font_size}px !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-label-wrap { font-size: {$label_font_size}px !important; }\n";
		}

		$input_font_size = Settings::get_setting( $form_id, 'input_font_size' );
		if ( ! empty( $input_font_size ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-form-control { font-size: {$input_font_size}px !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-form-control { font-size: {$input_font_size}px !important; }\n";
		}

		// Add font weights.
		$label_font_weight = Settings::get_setting( $form_id, 'label_font_weight' );
		if ( ! empty( $label_font_weight ) ) {
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] label.simpay-form-control { font-weight: {$label_font_weight} !important; }\n";
			$css .= "#simpay-form-{$form_id} label.simpay-form-control { font-weight: {$label_font_weight} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-label { font-weight: {$label_font_weight} !important; }\n";
			$css .= "#simpay-form-{$form_id} .simpay-label-wrap { font-weight: {$label_font_weight} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] .simpay-label-wrap { font-weight: {$label_font_weight} !important; }\n";
			$css .= ".simpay-form-wrap[data-form-id=\"{$form_id}\"] label { font-weight: {$label_font_weight} !important; }\n";
			// Override .simpay-styled .simpay-form-control legend/label (font-weight: 600), scoped to this form.
			$css .= "#simpay-form-{$form_id}.simpay-styled .simpay-form-control label, #simpay-form-{$form_id}.simpay-styled .simpay-form-control legend { font-weight: {$label_font_weight} !important; }\n";
		}

		// Add form title styling.
		// Fall back to text_color if title_color is not explicitly set,
		// since .simpay-embedded-heading is a sibling of .simpay-form-wrap
		// and won't inherit the text_color wildcard rule.
		$title_color = Settings::get_setting( $form_id, 'title_color' );
		if ( empty( $title_color ) && ! empty( $text_color ) ) {
			$title_color = $text_color;
		}
		if ( ! empty( $title_color ) ) {
			// Embedded form selectors - direct heading with data attribute (added via JS).
			$css .= ".simpay-embedded-heading[data-form-id=\"{$form_id}\"] .simpay-form-title { color: {$title_color} !important; }\n";
			// Embedded form selectors - wrapper-based.
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-form-title { color: {$title_color} !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-embedded-heading .simpay-form-title { color: {$title_color} !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-form-title { color: {$title_color} !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-embedded-heading .simpay-form-title { color: {$title_color} !important; }\n";
			// Form element selectors.
			$css .= "#simpay-form-{$form_id} .simpay-form-title { color: {$title_color} !important; }\n";
			$css .= "[data-simpay-form-id=\"{$form_id}\"] .simpay-form-title { color: {$title_color} !important; }\n";
			// Overlay/modal selectors.
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-form-title { color: {$title_color} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content .simpay-form-title { color: {$title_color} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__body .simpay-modal__content .simpay-form-title { color: {$title_color} !important; }\n";
		}

		$title_font_size = Settings::get_setting( $form_id, 'title_font_size' );
		if ( ! empty( $title_font_size ) ) {
			// Embedded form selectors - direct heading with data attribute (added via JS).
			$css .= ".simpay-embedded-heading[data-form-id=\"{$form_id}\"] .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			// Embedded form selectors - wrapper-based.
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-embedded-heading .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-embedded-heading .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			// Form element selectors.
			$css .= "#simpay-form-{$form_id} .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			$css .= "[data-simpay-form-id=\"{$form_id}\"] .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			// Overlay/modal selectors.
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__body .simpay-modal__content .simpay-form-title { font-size: {$title_font_size}px !important; }\n";
		}

		$title_font_weight = Settings::get_setting( $form_id, 'title_font_weight' );
		if ( ! empty( $title_font_weight ) ) {
			// Embedded form selectors - direct heading with data attribute (added via JS).
			$css .= ".simpay-embedded-heading[data-form-id=\"{$form_id}\"] .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			// Embedded form selectors - wrapper-based.
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-embedded-heading .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-embedded-heading .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			// Form element selectors.
			$css .= "#simpay-form-{$form_id} .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			$css .= "[data-simpay-form-id=\"{$form_id}\"] .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			// Overlay/modal selectors.
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__body .simpay-modal__content .simpay-form-title { font-weight: {$title_font_weight} !important; }\n";
		}

		// Add form description styling.
		// Fall back to text_color if description_color is not explicitly set,
		// since .simpay-embedded-heading is a sibling of .simpay-form-wrap.
		$description_color = Settings::get_setting( $form_id, 'description_color' );
		if ( empty( $description_color ) && ! empty( $text_color ) ) {
			$description_color = $text_color;
		}
		if ( ! empty( $description_color ) ) {
			// Embedded form selectors - direct heading with data attribute (added via JS).
			$css .= ".simpay-embedded-heading[data-form-id=\"{$form_id}\"] .simpay-form-description { color: {$description_color} !important; }\n";
			// Embedded form selectors - wrapper-based.
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-form-description { color: {$description_color} !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-embedded-heading .simpay-form-description { color: {$description_color} !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-form-description { color: {$description_color} !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-embedded-heading .simpay-form-description { color: {$description_color} !important; }\n";
			// Form element selectors.
			$css .= "#simpay-form-{$form_id} .simpay-form-description { color: {$description_color} !important; }\n";
			$css .= "[data-simpay-form-id=\"{$form_id}\"] .simpay-form-description { color: {$description_color} !important; }\n";
			// Overlay/modal selectors.
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-form-description { color: {$description_color} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content .simpay-form-description { color: {$description_color} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__body .simpay-modal__content .simpay-form-description { color: {$description_color} !important; }\n";
		}

		$description_font_size = Settings::get_setting( $form_id, 'description_font_size' );
		if ( ! empty( $description_font_size ) ) {
			// Embedded form selectors - direct heading with data attribute (added via JS).
			$css .= ".simpay-embedded-heading[data-form-id=\"{$form_id}\"] .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			// Embedded form selectors - wrapper-based.
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-embedded-heading .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-embedded-heading .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			// Form element selectors.
			$css .= "#simpay-form-{$form_id} .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			$css .= "[data-simpay-form-id=\"{$form_id}\"] .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			// Overlay/modal selectors.
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__body .simpay-modal__content .simpay-form-description { font-size: {$description_font_size}px !important; }\n";
		}

		$description_font_weight = Settings::get_setting( $form_id, 'description_font_weight' );
		if ( ! empty( $description_font_weight ) ) {
			// Embedded form selectors - direct heading with data attribute (added via JS).
			$css .= ".simpay-embedded-heading[data-form-id=\"{$form_id}\"] .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			// Embedded form selectors - wrapper-based.
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			$css .= "#simpay-embedded-form-wrap-{$form_id} .simpay-embedded-heading .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			$css .= "[data-id=\"simpay-form-{$form_id}-wrap\"] .simpay-embedded-heading .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			// Form element selectors.
			$css .= "#simpay-form-{$form_id} .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			$css .= "[data-simpay-form-id=\"{$form_id}\"] .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			// Overlay/modal selectors.
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__content .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
			$css .= ".simpay-modal[data-form-id=\"{$form_id}\"] .simpay-modal__body .simpay-modal__content .simpay-form-description { font-weight: {$description_font_weight} !important; }\n";
		}

		return $css;
	}

	/**
	 * Injects JavaScript to add data-form-id attribute to embedded headings and form wraps.
	 *
	 * This allows CSS selectors to target the heading and form wrap by form ID.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function inject_heading_data_attributes() {
		if ( empty( self::$rendered_form_ids ) ) {
			return;
		}

		$form_ids = array_unique( self::$rendered_form_ids );
		$js       = '<script type="text/javascript">';
		$js      .= '(function() {';

		foreach ( $form_ids as $form_id ) {
			// Add data-form-id to the form wrap.
			$js .= sprintf(
				'
				var formWrap%d = document.getElementById("simpay-embedded-form-wrap-%d");
				if (formWrap%d) {
					formWrap%d.setAttribute("data-form-id", "%d");
					var heading%d = formWrap%d.previousElementSibling;
					if (heading%d && heading%d.classList && heading%d.classList.contains("simpay-embedded-heading")) {
						heading%d.setAttribute("data-form-id", "%d");
					}
				}
				// Also add to form wrap by class selector for other display types.
				var formWraps%d = document.querySelectorAll(".simpay-form-wrap");
				formWraps%d.forEach(function(wrap) {
					var formId = wrap.getAttribute("data-id");
					if (formId && formId.indexOf("simpay-form-%d-wrap") !== -1) {
						wrap.setAttribute("data-form-id", "%d");
					}
				});
				',
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id,
				$form_id
			);
		}

		$js .= '})();';
		$js .= '</script>';

		echo $js; // WPCS: XSS okay.
	}
}
