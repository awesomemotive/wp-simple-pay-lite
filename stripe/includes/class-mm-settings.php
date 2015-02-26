<?php


if( ! class_exists( 'MM_Settings' ) ) {
	
	class MM_Settings {
		
		public static $class_version = '1.0.0';
		
		// class variables
		public $settings,
			    $prefix,
				$settings_sections = array(),
				$saved_settings = array();
		
		/**
		 * Class constructor
		 * 
		 * @param string $prefix
		 * @param array $settings
		 * 
		 * @since 1.0.0
		 */
		public function __construct( $prefix, $settings ) {
			
			$this->prefix   = $prefix . '_settings';
			$this->settings = $settings;
			
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}
		
		/*
		 * This method returns the SAVED Settings
		 * 
		 * It will loop through all the settings options and return only those that are not empty.
		 * 
		 * Returns as an array. 
		 * 
		 * @since 1.0.0
		 */
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
		
		/**
		 * Get a specific setting
		 * 
		 * @param string $setting
		 * @return Returns the setting if it exists and false if it doesn't
		 * 
		 * @since 1.0.0
		 */
		public function get_setting( $setting ) {
			if ( isset( $this->saved_settings[$setting] ) ) {
				return $this->saved_settings[$setting];
			}
			
			return false;
		}
		
		
		/**
		 * Sorts the settings based on the 'sort' argument
		 * 
		 * @since 1.0.0
		 */
		public function sort_settings() {
			
			foreach( $this->settings as $setting => $options ) {
				
				uasort( $this->settings[$setting], function( $a, $b ) {

					if ( ! isset( $a['sort'] ) ) {
						$a['sort'] = 0;
					}

					if ( ! isset( $b['sort'] ) ) {
						$b['sort'] = 0;
					}

					return $a['sort'] - $b['sort'];
				} );
			}
		}
		
		
		/**
		 * Method to loop through all of the passed in settings array args and connect it with the WP Settings API
		 * 
		 * @since 1.0.0
		 */
		public function register_settings() {
			
			// Sort first
			$this->sort_settings();

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
						method_exists( new MM_Settings_Callbacks( $this ), $v['type'] . '_callback' ) ? array( new MM_Settings_Callbacks( $this ), $v['type'] . '_callback' ) : array( new MM_Settings_Callbacks( $this ), 'missing_callback' ),
						$section,
						$section,
						array_merge( $this->get_settings_field_args( $v, $setting ), array( 'callback' => $v['type'] . '_callback', 'prefix' => $this->prefix . '_' ) )
					);
				}
				
				// Fourth we register the settings so we don't get issues when saving
				register_setting( $section, $section, array( $this, 'sanitize_settings' ) );
			}
		}
		
		/*
		 * Return generic add_settings_field $args parameter array.
		 *
		 * @param   string  $option   Single settings option key.
		 * @param   string  $section  Section of settings apge.
		 * @return  array             $args parameter to use with add_settings_field call.
		 * 
		 * @since 1.0.0
		 */
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
				$settings_args = array_merge( $settings_args, array( 'label_for' => $this->prefix . '_' . $section . '[' . $option['id'] . ']' ) );
			}

			return $settings_args;
		}
		
		/*
		 * Method to sanitize any input
		 * 
		 * @since 1.0.0
		 */
		public function sanitize_settings( $input ) {
			return $input;
		}
	}
	
}
