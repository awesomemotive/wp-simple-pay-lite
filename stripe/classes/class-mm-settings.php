<?php

/**
 * Base settings class
 *
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MM_Settings' ) ) {
	
	class MM_Settings {
		
		// Class version
		public static $class_version = '1.0.0';
		
		// class variables
		protected $settings = array();
		protected $option;
		
		// public variables
		public $tabs;
		
		/**
		 * Class constructor
		 */
		public function __construct( $option ) {
			$this->option = $option;
			
			// When this class is loaded we initialize the action to use AJAX
			add_action( 'wp_ajax_button_save', array( $this, 'button_save' ) );
		}
		
		/**
		 * AJAX Save
		 */
		public function button_save() {
			
			$settings = array();
			
			// Remove the '+' signs for strings with spaces
			$saved = str_replace( '%2B', ' ', $_POST['form_data'] );
			
			$saved = urldecode( $saved );
			
			// Replace [ and ] with an underscore
			$saved = str_replace( '%5B', '_', $saved );
			$saved = str_replace( '%5D', '', $saved );
			
			$saved = explode( '&', $saved );
			
			// Loop through the serialized form query and break it into a PHP array
			foreach ( $saved as $k => $v ) {
				$value = explode( '=', $v );
				$settings[ $value[0] ] = trim( $value[1] );
			}
			
			$this->update_settings( $settings );
			
			die();
		}
		
		/*
		 * Function to set default options on a fresh install
		 */
		public function set_defaults( $settings = array() ) {
			
			if ( false === get_option( $this->option ) ) {
				
				$this->settings = $settings;
			
				update_option( $this->option, $this->settings );
			}
		}
		
		/*
		 * Loads the specified template file
		 */
		public function load_template( $file ) {
			include_once( $file );
		}
		
		/*
		 * Add a specific setting with a specified value
		 */
		public function add_setting( $setting, $value ) {
			$settings = get_option( $this->option );
			$settings[ $this->get_setting_id( $setting ) ] = $value;
			
			$this->update_settings( $settings );
		}
		
		/*
		 * Return all the settings
		 */
		public function get_settings() {
			$saved_settings = is_array( get_option( $this->option ) ) ? get_option( $this->option ) : array();
			
			return array_merge( $this->settings, $saved_settings );
		}
		
		/*
		 * Updates the settings in the database
		 */
		public function update_settings( $settings = array() ) {
			
			$old_settings = get_option( $this->option );
			
			if ( false === $old_settings ) {
				$old_settings = $this->settings;
			}
			
			$this->settings = array_merge( $old_settings, $settings );
			
			foreach ( $this->settings as $setting ) {
				if ( empty( $setting ) ) {
					unset( $this->settings[ $setting ] );
				}
			}
			
			update_option( $this->option, $this->settings );
		}
		
		/*
		 * Print out the settings to the screen. Mostly used for debugging.
		 */
		public function print_settings() {
			$settings = get_option( $this->option );
			
			echo '<pre>' . print_r( $settings, true ) . '</pre>';
		}
		
		/*
		 * Set the tabs for this class instance
		 */
		public function set_tabs( $tabs ) {
			$this->tabs = $tabs;
		}
		
		/*
		 * Return the tabs of this class instance
		 */
		public function get_tabs() {
			return $this->tabs;
		}
		
		/*
		 * Return a specific setting
		 * 
		 * Will return the setting if successful or will return null if not successful.
		 */
		public function get_setting_value( $id ) {
			
			$settings = is_array( get_option( $this->option ) ) ? get_option( $this->option ) : array();
			
			$this->settings = $settings;
			
			$id = $this->get_setting_id( $id );
			
			// Only return it if it is set and it is not empty
			if ( isset( $settings[ $id ] ) && ! empty( $settings[ $id ] ) ) {
				return $settings[ $id ];
			}
			
			return null;
		}
		
		/*
		 * Create an ID for the specified $id
		 */
		public function get_setting_id( $id ) {
			return $this->option . '_' . $id;
		}
		
		/*
		 * Returns this class' option value
		 */
		public function get_option() {
			return $this->option;
		}
		
		/*
		 * Delete the option out of the database
		 */
		public function delete_option() {
			delete_option( $this->option );
		}
	}
}
