(function($) {
    'use strict';

    // Run when the DOM is ready
    $(document).ready(function() {
        // Initialize the tabbed interface first
        initTabs();
        
        // Initialize theme selection
        initThemeSelection();
        
        // Initialize theme previews with correct colors
        initThemePreviews();
        
        // Initialize color pickers
        initColorPickers();
        
        // Initialize border radius preview
        initBorderRadiusPreview();
        
        // Initialize the reset button
        initResetButton();
        
        // Prevent preview button interactions
        preventPreviewButtonInteractions();
        
        // Prevent form validation errors for hidden tabs
        preventHiddenTabValidation();
    });

    /**
     * Initialize the tabbed interface
     */
    function initTabs() {
        // Store active tab in sessionStorage to restore after save
        var activeTabKey = 'wpspwpsps_active_style_tab';
        
        // Function to switch tabs
        function switchTab(tabId) {
            if (!tabId) {
                return false;
            }
            
            // Remove active class from all tabs and panels
            $('.wpsp-admin-tab-button').removeClass('active');
            $('.wpsp-admin-tab-panel').removeClass('active');
            
            // Add active class to current tab and panel
            var $tabButton = $('.wpsp-admin-tab-button[data-tab="' + tabId + '"]');
            var $tabPanel = $('.wpsp-admin-tab-panel[data-tab-content="' + tabId + '"]');
            
            if ($tabButton.length && $tabPanel.length) {
                $tabButton.addClass('active');
                $tabPanel.addClass('active');
                return true;
            }
            
            return false;
        }
        
        // Check for saved tab in sessionStorage
        var savedTab = sessionStorage.getItem(activeTabKey);
        
        // Check if there's already an active tab (from PHP default)
        var $existingActiveButton = $('.wpsp-admin-tab-button.active');
        var $existingActivePanel = $('.wpsp-admin-tab-panel.active');
        
        // Determine which tab should be active
        var targetTab = 'themes'; // Default fallback
        
        if (savedTab && $('.wpsp-admin-tab-button[data-tab="' + savedTab + '"]').length > 0) {
            // Use saved tab if it exists
            targetTab = savedTab;
        } else if ($existingActiveButton.length > 0 && $existingActivePanel.length > 0) {
            // Keep existing active tab from PHP
            targetTab = $existingActiveButton.data('tab');
        }
        
        // Switch to the target tab (this ensures both button and panel are active)
        switchTab(targetTab);
        
        // Handle tab clicks
        $('.wpsp-admin-tab-button').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get the tab ID
            var tabId = $(this).data('tab');
            
            if (!tabId) {
                return false;
            }
            
            // Store in sessionStorage
            sessionStorage.setItem(activeTabKey, tabId);
            
            // Switch to the selected tab
            switchTab(tabId);
            
            return false;
        });
    }

    /**
     * Initialize theme previews with correct colors
     */
    function initThemePreviews() {
        // Load theme presets from the hidden field
        var themesData = JSON.parse($('#wpsps_theme_presets').val() || '{}');
        
        // Get the currently selected theme
        var currentTheme = $('input[name="wpsps[selected_theme]"]:checked').val() || 'default';
        
        // Initialize each theme card with its correct colors
        $('.wpsp-admin-theme-card').each(function() {
            var $card = $(this);
            var themeId = $card.data('theme-id');
            
            if (themesData[themeId]) {
                var theme = themesData[themeId];
                var themeSettings = getFullThemeSettings(themeId, theme);
                
                // Update preview wrapper background with padding and border radius
                $card.find('.wpsp-admin-preview-wrapper').css({
                    'background-color': themeSettings.form_container_background_color || themeSettings.background_color,
                    'padding': '10px',
                    'border-radius': (themeSettings.form_border_radius || 0) + 'px'
                });

                // Set the preview colors to match the theme with proper font weights
                $card.find('.wpsp-admin-preview-label').css({
                    'color': themeSettings.label_text_color || themeSettings.text_color,
                    'font-weight': themeSettings.label_font_weight || 'normal'
                });
                $card.find('.wpsp-admin-preview-input').css({
                    'background-color': themeSettings.background_color,
                    'color': themeSettings.input_text_color || themeSettings.text_color,
                    'border-color': themeSettings.border_color || '#e6e6e6',
                    'border-radius': (themeSettings.border_radius || 4) + 'px',
                    'padding': '4px 8px'
                });
                $card.find('.wpsp-admin-preview-btn').css({
                    'background-color': themeSettings.button_background_color || themeSettings.primary_color,
                    'color': themeSettings.button_text_color || '#ffffff',
                    'border-radius': (themeSettings.border_radius || 4) + 'px'
                });
            }
        });
        
        // Also initialize the currently selected theme's preview with saved settings
        if (currentTheme && themesData[currentTheme]) {
            var theme = themesData[currentTheme];
            var themeSettings = getFullThemeSettings(currentTheme, theme);
            
            // Update the selected theme's preview with the full theme settings
            var $selectedCard = $('.wpsp-admin-theme-card[data-theme-id="' + currentTheme + '"]');
            if ($selectedCard.length) {
                updateThemeCardPreview(currentTheme, themeSettings);
            }
        }
    }
    
    /**
     * Initialize theme selection functionality
     */
    function initThemeSelection() {
        // Load theme presets from the hidden field
        var themesData = JSON.parse($('#wpsps_theme_presets').val() || '{}');
        
        // Set initial faded state for non-selected themes
        $('.wpsp-admin-theme-card').not('.selected').addClass('faded');
        
        // Handle theme selection
        $('.wpsp-admin-theme-card').on('click', function(e) {
            var $card = $(this);
            var themeId = $card.data('theme-id');
            
            // Skip if this theme is already selected
            if ($card.hasClass('selected')) {
                return;
            }

            // Update UI
            $('.wpsp-admin-theme-card').removeClass('selected');
            $card.addClass('selected');

            // Add fade effect to other themes
            $('.wpsp-admin-theme-card').not('.selected').addClass('faded');
            $card.removeClass('faded');

            // Check the radio button
            $card.find('input[type="radio"]').prop('checked', true);
            
            // Apply theme settings if theme exists in our data
            if (themesData[themeId]) {
                applyThemeSettings(themeId, themesData[themeId]);
            }
        });
        
    }
    
    /**
     * Apply theme settings to form fields
     */
    function applyThemeSettings(themeId, theme) {
        // Get complete theme settings
        var themeSettings = getFullThemeSettings(themeId, theme);
        
        // Apply color settings with a brief delay to ensure color pickers have initialized
        setTimeout(function() {
            // Form container background
            updateColorPicker('#wpsps_form_container_background_color', themeSettings.form_container_background_color);
            
            // Input background
            updateColorPicker('#wpsps_background_color', themeSettings.background_color);
            
            // Text color
            updateColorPicker('#wpsps_text_color', themeSettings.text_color);
            
            // Label text color (if set)
            if (themeSettings.label_text_color) {
                updateColorPicker('#wpsps_label_text_color', themeSettings.label_text_color);
            }
            
            // Input text color (if set)
            if (themeSettings.input_text_color) {
                updateColorPicker('#wpsps_input_text_color', themeSettings.input_text_color);
            }
            
            // Border color
            if (themeSettings.border_color) {
                updateColorPicker('#wpsps_border_color', themeSettings.border_color);
            }
            
            // Primary color
            updateColorPicker('#wpsps_primary_color', themeSettings.primary_color);
            
            // Button background
            updateColorPicker('#wpsps_button_background_color', themeSettings.button_background_color);
            
            // Button text color
            updateColorPicker('#wpsps_button_text_color', themeSettings.button_text_color);
            
            // Button hover background
            updateColorPicker('#wpsps_button_hover_background_color', themeSettings.button_hover_background_color);

            // Title color
            updateColorPicker('#wpsps_title_color', themeSettings.title_color);

            // Description color
            updateColorPicker('#wpsps_description_color', themeSettings.description_color);

            // Update preview elements
            $('.wpsp-admin-button-preview').css('background-color', themeSettings.button_background_color);
            $('.wpsp-admin-button-preview').css('color', themeSettings.button_text_color);
            $('.wpsp-admin-button-hover').css('background-color', themeSettings.button_hover_background_color);
            
            // Update numeric values
            $('#wpsps_form_border_radius').val(themeSettings.form_border_radius).trigger('change');
            $('#wpsps_border_radius').val(themeSettings.border_radius).trigger('change');
            // Update selects
            $('#wpsps_label_font_weight').val(themeSettings.label_font_weight);
            
            // Update border radius previews
            $('.wpsp-admin-form-radius-box').css('border-radius', themeSettings.form_border_radius + 'px');
            $('.wpsp-admin-radius-input-preview, .wpsp-admin-radius-button-preview').css('border-radius', themeSettings.border_radius + 'px');
            
            // Update theme card CSS variables for mini form previews
            updateThemeCardPreview(themeId, themeSettings);
            
            // Theme applied - no notification needed
            
        }, 200);
    }
    
    /**
     * Update theme card mini form previews with theme settings
     */
    function updateThemeCardPreview(themeId, themeSettings) {
        var $card = $('[data-theme-id="' + themeId + '"]');
        
        if ($card.length) {
            // Update CSS variables
            $card.css({
                '--theme-primary': themeSettings.primary_color,
                '--theme-secondary': themeSettings.button_hover_background_color,
                '--theme-background': themeSettings.background_color,
                '--theme-text': themeSettings.text_color,
                '--theme-border': themeSettings.border_color,
                '--theme-input-bg': themeSettings.background_color
            });
            
            // Update preview wrapper background with padding and border radius
            $card.find('.wpsp-admin-preview-wrapper').css({
                'background-color': themeSettings.form_container_background_color || themeSettings.background_color,
                'padding': '10px',
                'border-radius': (themeSettings.form_border_radius || 0) + 'px'
            });
            
            // Update preview labels with font weight
            $card.find('.wpsp-admin-preview-label').css({
                'color': themeSettings.label_text_color || themeSettings.text_color,
                'font-weight': themeSettings.label_font_weight || 'normal'
            });
            
            // Update preview inputs with padding
            $card.find('.wpsp-admin-preview-input').css({
                'background-color': themeSettings.background_color,
                'color': themeSettings.input_text_color || themeSettings.text_color,
                'border-color': themeSettings.border_color || '#e6e6e6',
                'border-radius': (themeSettings.border_radius || 4) + 'px',
                'padding': '4px 8px'
            });
            
            // Update preview button
            $card.find('.wpsp-admin-preview-btn').css({
                'background-color': themeSettings.button_background_color || themeSettings.primary_color,
                'color': themeSettings.button_text_color || '#ffffff',
                'border-radius': (themeSettings.border_radius || 4) + 'px'
            });
        }
    }
    
    /**
     * Get full settings for a theme with default values for all fields
     */
    function getFullThemeSettings(themeId, theme) {
        // Default settings for all themes
        var defaults = {
            form_container_background_color: '#ffffff',
            background_color: '#ffffff',
            text_color: '#32325d',
            title_color: '#32325d',
            description_color: '#666666',
            label_text_color: '', // Will inherit from text_color if empty
            input_text_color: '', // Will inherit from text_color if empty
            border_color: '#e6e6e6',
            primary_color: '#0f8569',
            button_background_color: '#0f8569',
            button_text_color: '#ffffff',
            button_hover_background_color: '#0e7c62',
            form_border_radius: 0, // Default form container radius
            border_radius: 3, // Default should be 3px
            label_font_weight: 'normal'
        };

        // Theme-specific mappings based on the theme's color palette
        var themeSettings = {
            // Map the basic theme colors to specific form elements
            form_container_background_color: theme.colors.background,
            background_color: theme.colors.background,
            text_color: theme.colors.text,
            title_color: theme.colors.text,
            description_color: theme.colors.text,
            primary_color: theme.colors.primary,
            button_background_color: theme.colors.primary,
            button_text_color: '#ffffff', // Usually white for buttons
            button_hover_background_color: theme.colors.secondary
        };
        
        // Create more visually distinct themes by setting specific colors for different elements
        switch (themeId) {
            case 'monochrome':
                // Black and white theme with gray accents
                themeSettings.form_container_background_color = '#f5f5f5'; // Light gray container
                themeSettings.background_color = '#ffffff'; // White inputs
                themeSettings.text_color = '#333333'; // Dark text
                themeSettings.title_color = '#333333'; // Dark title
                themeSettings.description_color = '#555555'; // Medium gray description
                themeSettings.label_text_color = '#555555'; // Medium gray labels
                themeSettings.input_text_color = '#000000'; // Black input text
                themeSettings.border_color = '#cccccc'; // Medium gray borders
                themeSettings.primary_color = '#333333'; // Black primary color
                break;

            case 'sunset':
                // Warm red theme
                themeSettings.form_container_background_color = '#fff5f2'; // Very light peach
                themeSettings.background_color = '#ffffff'; // White inputs
                themeSettings.text_color = '#2c3e50'; // Dark text
                themeSettings.title_color = '#2c3e50'; // Dark title
                themeSettings.description_color = '#5d6d7e'; // Muted description
                themeSettings.label_text_color = '#c0392b'; // Dark red labels
                themeSettings.border_color = '#f2d6d0'; // Light pink borders
                themeSettings.primary_color = '#e74c3c'; // Bright red primary
                break;

            case 'forest':
                // Green theme
                themeSettings.form_container_background_color = '#f2f9f5'; // Very light green
                themeSettings.background_color = '#ffffff'; // White inputs
                themeSettings.text_color = '#2c3e50'; // Dark text
                themeSettings.title_color = '#2c3e50'; // Dark title
                themeSettings.description_color = '#5d6d7e'; // Muted description
                themeSettings.label_text_color = '#1e8449'; // Darker green labels
                themeSettings.border_color = '#d5e9db'; // Light green borders
                themeSettings.primary_color = '#27ae60'; // Medium green primary
                break;

            case 'ocean':
                // Blue theme
                themeSettings.form_container_background_color = '#f0f8fd'; // Very light blue
                themeSettings.background_color = '#ffffff'; // White inputs
                themeSettings.text_color = '#2c3e50'; // Dark text
                themeSettings.title_color = '#2c3e50'; // Dark title
                themeSettings.description_color = '#5d6d7e'; // Muted description
                themeSettings.label_text_color = '#2980b9'; // Medium blue labels
                themeSettings.border_color = '#d6eaf8'; // Light blue borders
                themeSettings.primary_color = '#3498db'; // Bright blue primary
                break;

            case 'sunshine':
                // Yellow theme
                themeSettings.form_container_background_color = '#fffcef'; // Very light yellow
                themeSettings.background_color = '#ffffff'; // White inputs
                themeSettings.text_color = '#34495e'; // Dark blue-gray text
                themeSettings.title_color = '#34495e'; // Dark title
                themeSettings.description_color = '#5d6d7e'; // Muted description
                themeSettings.label_text_color = '#d4ac0d'; // Darker yellow labels
                themeSettings.border_color = '#fcf3cf'; // Light yellow borders
                themeSettings.primary_color = '#f1c40f'; // Bright yellow primary
                themeSettings.button_text_color = '#34495e'; // Dark text for contrast on yellow
                break;

            case 'coral':
                // Orange theme
                themeSettings.form_container_background_color = '#fdf2e9'; // Very light orange
                themeSettings.background_color = '#ffffff'; // White inputs
                themeSettings.text_color = '#2c3e50'; // Dark text
                themeSettings.title_color = '#2c3e50'; // Dark title
                themeSettings.description_color = '#5d6d7e'; // Muted description
                themeSettings.label_text_color = '#d35400'; // Darker orange labels
                themeSettings.border_color = '#fae5d3'; // Light orange borders
                themeSettings.primary_color = '#e67e22'; // Medium orange primary
                break;

            case 'minimal':
                // Minimal white and gray theme
                themeSettings.form_container_background_color = '#ffffff'; // White container
                themeSettings.background_color = '#f9f9f9'; // Very light gray inputs
                themeSettings.text_color = '#2c3e50'; // Dark text
                themeSettings.title_color = '#2c3e50'; // Dark title
                themeSettings.description_color = '#7f8c8d'; // Muted gray description
                themeSettings.label_text_color = '#7f8c8d'; // Medium gray labels
                themeSettings.input_text_color = '#2c3e50'; // Dark input text
                themeSettings.border_color = '#ecf0f1'; // Light borders
                themeSettings.primary_color = '#bdc3c7'; // Medium gray primary
                themeSettings.button_text_color = '#2c3e50'; // Dark text for contrast on gray
                break;

            case 'default':
                // WP Simple Pay's default styling
                themeSettings.form_container_background_color = '#ffffff'; // White container
                themeSettings.background_color = '#ffffff'; // White inputs
                themeSettings.text_color = '#32325d'; // Default text color
                themeSettings.title_color = '#32325d'; // Default title
                themeSettings.description_color = '#666666'; // Default description
                themeSettings.primary_color = '#0f8569'; // Default primary color
                themeSettings.border_color = '#e6e6e6'; // Default borders
                break;
        }
        
        // Add theme-specific layout values
        switch (themeId) {
            case 'monochrome':
                themeSettings.form_border_radius = 0;
                themeSettings.border_radius = 0; // Sharp corners
                break;
            case 'sunset':
            case 'coral':
                themeSettings.form_border_radius = 8;
                themeSettings.border_radius = 5; // More rounded
                break;
            case 'forest':
            case 'sunshine':
                themeSettings.form_border_radius = 6;
                themeSettings.border_radius = 3; // Default roundness
                break;
            case 'ocean':
                themeSettings.form_border_radius = 6;
                themeSettings.border_radius = 4; // Slightly rounded
                break;
            case 'minimal':
                themeSettings.form_border_radius = 0;
                themeSettings.border_radius = 2; // Subtle roundness
                break;
            case 'default':
                themeSettings.form_border_radius = 0;
                themeSettings.border_radius = 3; // Default roundness
                break;
            default:
                themeSettings.form_border_radius = 0;
                themeSettings.border_radius = 3; // Default roundness
        }
        
        // Add theme-specific typography values
        switch (themeId) {
            case 'monochrome':
                themeSettings.label_font_weight = 'bold';
                break;
            case 'minimal':
                themeSettings.label_font_weight = '300'; // Light weight
                break;
            case 'forest':
            case 'ocean':
                themeSettings.label_font_weight = '500'; // Medium weight
                break;
            default:
                themeSettings.label_font_weight = 'normal';
        }
        
        // Merge with defaults
        return $.extend({}, defaults, themeSettings);
    }
    
    /**
     * Update a color picker input with a new value
     */
    function updateColorPicker(selector, color) {
        var $input = $(selector);
        
        // Set the input value
        $input.val(color);
        
        // Update the color picker UI
        if ($input.hasClass('wpspcolor-picker') && $input.wpColorPicker) {
            try {
                // Force a refresh of the picker by triggering change first
                $input.trigger('change');
                $input.wpColorPicker('color', color);
                
                // Additionally update any preview elements
                if ($input.attr('id') === 'wpsps_button_background_color') {
                    $('.wpsp-admin-button-preview').css('background-color', color);
                } else if ($input.attr('id') === 'wpsps_button_text_color') {
                    $('.wpsp-admin-button-preview').css('color', color);
                } else if ($input.attr('id') === 'wpsps_button_hover_background_color') {
                    $('.wpsp-admin-button-hover').css('background-color', color);
                } else if ($input.attr('id') === 'wpsps_form_container_background_color') {
                    // Preview container background - intentionally left empty for now
                }
            } catch (e) {
                console.warn('Failed to update color picker:', e);
                // Fallback approach if WP Color Picker fails
                $input.val(color).trigger('change');
            }
        }
    }
    

    /**
     * Initialize WordPress color pickers with custom options
     */
    function initColorPickers() {
        // Store original values to restore on form submit
        $('.wpspcolor-picker').each(function() {
            $(this).data('original-value', $(this).val());
        });
        
        // Configure standard color pickers
        var colorPickerOptions = {
            // Update button previews when color changes
            change: function(event, ui) {
                var color = ui.color.toString();
                var $input = $(event.target);
                
                // Update button previews if this is a button-related color picker
                if ($input.attr('id') === 'wpsps_button_background_color') {
                    $('.wpsp-admin-button-preview').css('background-color', color);
                } else if ($input.attr('id') === 'wpsps_button_text_color') {
                    $('.wpsp-admin-button-preview').css('color', color);
                } else if ($input.attr('id') === 'wpsps_button_hover_background_color') {
                    $('.wpsp-admin-button-hover').css('background-color', color);
                }
            },
            // Ensure the value gets updated when the color is cleared
            clear: function(event) {
                $(event.target).val('');
                $(event.target).trigger('change');
            }
        };
        
        // Initialize alpha-enabled color pickers
        $('[data-alpha-enabled="true"]').wpColorPicker({
            palettes: true,
            alpha: true,
            change: colorPickerOptions.change,
            clear: colorPickerOptions.clear
        });
        
        // Initialize standard color pickers
        $('.wpspcolor-picker:not([data-alpha-enabled="true"])').wpColorPicker({
            palettes: true,
            change: colorPickerOptions.change,
            clear: colorPickerOptions.clear
        });
    }
    
    /**
     * Initialize the border radius preview
     */
    function initBorderRadiusPreview() {
        var $input = $('#wpsps_border_radius');
        var $formInput = $('#wpsps_form_border_radius');

        // Initialize preview on page load with whatever value is in the input
        var initialRadius = $input.val() || '0';
        $('.wpsp-admin-radius-input-preview, .wpsp-admin-radius-button-preview').css('border-radius', initialRadius + 'px');

        var initialFormRadius = $formInput.val() || '0';
        $('.wpsp-admin-form-radius-box').css('border-radius', initialFormRadius + 'px');

        // Update input & button border radius preview when the input changes
        $input.on('input change', function() {
            var radius = $(this).val() + 'px';
            $('.wpsp-admin-radius-input-preview, .wpsp-admin-radius-button-preview').css('border-radius', radius);
        });

        // Update form border radius preview when the input changes
        $formInput.on('input change', function() {
            var radius = $(this).val() + 'px';
            $('.wpsp-admin-form-radius-box').css('border-radius', radius);
        });
    }
    
    /**
     * Initialize the reset button functionality
     */
    function initResetButton() {
        $('#wpsp-admin-reset-styles').on('click', function(e) {
            e.preventDefault();
            
            // Show confirmation dialog
            if (confirm(simpayFormStyleData.resetConfirmMessage)) {
                // Create a hidden input to signal reset action
                var $resetInput = $('<input>').attr({
                    type: 'hidden',
                    name: 'wpsps_reset',
                    value: 'true'
                });
                
                // Add it to the form and submit
                $(this).closest('form').append($resetInput);
                $('#publish').click(); // Trigger the main form submission
            }
        });
    }
    
    /**
     * Prevent preview button interactions
     */
    function preventPreviewButtonInteractions() {
        // Prevent clicks on preview buttons
        $(document).on('click', '.wpsp-admin-preview-button', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        
        // Prevent keyboard interactions on preview buttons
        $(document).on('keydown', '.wpsp-admin-preview-button', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
    }
    
    /**
     * Prevent validation errors for fields in hidden tabs
     */
    function preventHiddenTabValidation() {
        // Handle form submission
        $('form#post').on('submit', function() {
            // Make sure all numeric fields have valid values before submitting
            $('.wpsp-admin-input-with-unit input[type="number"]').each(function() {
                var $input = $(this);
                if ($input.val() === '' || isNaN(parseInt($input.val()))) {
                    // Set to default value if empty or invalid (only for border radius fields).
                    // Font size fields are left empty intentionally to use theme defaults.
                    if ($input.attr('id') === 'wpsps_border_radius') {
                        $input.val('3');
                    } else if ($input.attr('id') === 'wpsps_form_border_radius') {
                        $input.val('0');
                    }
                }
            });
            
            return true;
        });
    }

})(jQuery); 