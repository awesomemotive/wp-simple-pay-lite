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

if( ! class_exists( 'MM_Settings' ) ) {
	
	class MM_Settings {
		
		public static $class_version = '1.0.0';
		
		// class variables
		protected $settings = array();
		protected $option;
		
		public $tabs;
		
		
		public function __construct( $option ) {
			$this->option = $option;
			
			add_action( 'wp_ajax_sc_button_save', array( $this, 'sc_button_save' ) );
			
		}
		
		public function sc_button_save() {
			
			$settings = array();
			
			// Remove the '+' signs for strings with spaces
			$saved = str_replace( '%2B', ' ', $_POST['form_data'] );
			
			$saved = urldecode( $saved );
			
			// Replace [ and ] with an underscore
			$saved = str_replace( '%5B', '_', $saved );
			$saved = str_replace( '%5D', '', $saved );
			
			$saved = explode( '&', $saved );
			
			foreach( $saved as $k => $v ) {
				$value = explode( '=', $v );
				$settings[$value[0]] = $value[1];
			}
			
			$this->update_settings( $settings );
			
			die();
		}
		
		public function set_defaults( $settings = array() ) {
			
			if ( false === get_option( $this->option ) ) {
				
				$this->settings = $settings;
			
				update_option( $this->option, $this->settings );
			}
		}
		
		
		public function load_template( $file ) {
			include_once( $file );
		}
		
		public function add_setting( $setting, $value ) {
			$settings = get_option( $this->option );
			$settings[ $this->get_setting_id( $setting ) ] = $value;
			
			$this->update_settings( $settings );
		}
		
		public function get_settings() {
			$saved_settings = is_array( get_option( $this->option ) ) ? get_option( $this->option ) : array();
			
			return array_merge( $this->settings, $saved_settings );
		}
		
		public function update_settings( $settings = array() ) {
			
			$old_settings = get_option( $this->option );
			
			if ( false === $old_settings ) {
				$old_settings = $this->settings;
			}
			
			$this->settings = array_merge( $old_settings, $settings );
			
			foreach( $this->settings as $setting ) {
				if ( empty( $setting ) ) {
					unset( $this->settings[$setting] );
				}
			}
			
			update_option( $this->option, $this->settings );
		}
		
		public function print_settings() {
			$settings = get_option( $this->option );
			
			echo '<pre>' . print_r( $settings, true ) . '</pre>';
		}
		
		public function set_tabs( $tabs ) {
			$this->tabs = $tabs;
		}
		
		public function get_tabs() {
			return $this->tabs;
		}
		
		public function get_setting_value( $id ) {
			
			$settings = is_array( get_option( $this->option ) ) ? get_option( $this->option ) : array();
			
			$this->settings = $settings;
			
			$id = $this->get_setting_id( $id );
			
			if( isset( $settings[$id] ) && ! empty( $settings[$id] ) ) {
				return $settings[$id];
			}
			
			return null;
		}
		
		public function get_setting_id( $id ) {
			return $this->option . '_' . $id;
		}
		
		public function get_option() {
			return $this->option;
		}
		
		public function delete_option() {
			delete_option( $this->option );
		}
	}
}
