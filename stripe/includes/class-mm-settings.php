<?php


if( ! class_exists( 'MM_Settings' ) ) {
	
	class MM_Settings {
		
		// class variables
		public $settings,
			    $prefix,
				$settings_sections = array(),
				$saved_settings = array();
		
		// For example it will look someting like:
		// $settings = new MM_Settings( 'sc', $settings_array );
		public function __construct( $prefix, $settings ) {
			
			$this->prefix   = $prefix . '_settings';
			$this->settings = $settings;
			
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
		
		public function get_settings() {

			foreach( $this->settings as $setting => $options ) {
				$section = $this->prefix . '_' . $setting;
				
				$current = is_array( get_option( $section ) ) ? get_option( $section )  : array();
				
				// Loop through and add only the options that aren't empty
				foreach( $current as $k => $v ) {
					if( ! empty( $v ) ) {
						$this->saved_settings[$k] = $v;
					}
				}
			}
			
			return $this->saved_settings;
		}
		
		
		public function register_settings() {

			foreach( $this->settings as $setting => $options ) {
				
				$section = $this->prefix . '_' . $setting;
				
				$this->settings_sections[] = $section;
				
				// First if the options do not exist then create them
				if ( false === get_option( $section ) ) {
					add_option( $section );
				}
				
				// Second, we add the settings section
				add_settings_section(
					$section,
					$options['section_name'],
					'__return_false',
					$section
				);
				
				unset( $options['section_name'] );
				
				// Third we load all the settings options for this section
				foreach ( $options as $option => $v ) {

					add_settings_field(
						$section . '[' . $v['id'] . ']',
						$v['name'],
						method_exists( $this, $v['type'] . '_callback' ) ? array( $this, $v['type'] . '_callback' ) : array( $this, 'missing_callback' ),
						$section,
						$section,
						$this->get_settings_field_args( $v, $setting )
					);
				}
				
				// Fourth we register the settings so we don't get issues when saving
				register_setting( $section, $section, array( $this, 'sanitize_settings' ) );
			}
		}
		
		public function get_settings_field_args( $option, $section ) {
			$settings_args = array(
				'id'      => $option['id'],
				'desc'    => $option['desc'],
				'name'    => $option['name'],
				'section' => $section,
				'size'    => isset( $option['size'] ) ? $option['size'] : null,
				'options' => isset( $option['options'] ) ? $option['options'] : '',
				'std'     => isset( $option['std'] ) ? $option['std'] : '',
				'product' => isset( $option['product'] ) ? $option['product'] : ''
			);

			// Link label to input using 'label_for' argument if text, textarea, password, select, or variations of.
			// Just add to existing settings args array if needed.
			if ( in_array( $option['type'], array( 'text', 'select', 'textarea', 'password', 'number' ) ) ) {
				$settings_args = array_merge( $settings_args, array( 'label_for' => 'sc_settings_' . $section . '[' . $option['id'] . ']' ) );
			}

			return $settings_args;
		}
		
		public function sanitize_settings( $input ) {
			return $input;
		}
	}
	
}
