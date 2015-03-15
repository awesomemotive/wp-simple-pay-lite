<?php


// TODO: Add direct file access check

if( ! class_exists( 'MM_Settings' ) ) {
	
	class MM_Settings {
		
		public static $class_version = '1.0.0';
		
		// class variables
		protected $settings = array();
		protected $option;
		
		public $tabs;
		
		
		public function __construct( $option ) {
			$this->option = $option;
			
			if ( false === get_option( $this->option ) ) {
				add_option( $this->option );
			}
			
			$this->set_defaults();
			
			add_action( 'wp_ajax_sc_button_save', array( $this, 'sc_button_save' ) );
			
		}
		
		public function sc_button_save() {
			
			$settings = array();
			
			// Remove the '+' signs for strings with spaces
			$saved = str_replace( '%2B', ' ', $_POST['form_data'] );
			
			$saved = explode( '&', urldecode( $saved ) );
			
			foreach( $saved as $k => $v ) {
				$value = explode( '=', $v );
				$settings[$value[0]] = $value[1];
			}
			
			$this->update_settings( $settings );
			
			die();
		}
		
		protected function set_defaults() {
			$this->settings = array(
				'name'                    => '',
				'currency'                => '',
				'image_url'               => '',
				'checkout_button_label'   => '',
				'payment_button_label'    => '',
				'success_redirect_url'    => '',
				'disable_success_message' => '',
				'failure_redirect_url'    => '',
				'billing'                 => '',
				'verify_zip'              => '',
				'enable_remember'         => 1,
				'disable_css'             => '',
				'always_enqueue'          => '',
				'uninstall_save_settings' => 1,
				'enable_live_key'         => '',
				'test_secret_key'         => '',
				'test_publish_key'        => '',
				'live_secret_key'         => '',
				'live_publish_key'        => '',
			);
		}
		
		public function load_template( $file_name, $ext = '.php' ) {
			include_once( SC_PATH . 'template/' . $file_name . $ext );
		}
		
		public function get_settings() {
			$saved_settings = is_array( get_option( $this->option ) ) ? get_option( $this->option ) : array();
			
			return array_merge( $this->settings, $saved_settings );
		}
		
		public function update_settings( $settings = array() ) {
			$this->settings = get_option( $this->option );
			
			$this->settings = array_merge( $this->settings, $settings );
			
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
		
		public function get_tabs() {
			
			$this->tabs = array(
				'keys'    => __( 'Stripe Keys', 'sc' ),
				'default' => __( 'Default Settings', 'sc' ),
			);
			
			return $this->tabs;
		}
		
		public function get_setting_value( $id ) {
			
			$settings = is_array( get_option( $this->option ) ) ? get_option( $this->option ) : array();
			
			$this->settings = $settings;
			
			$id = $this->get_setting_id( $id );
			
			if( isset( $settings[$id] ) && ! empty( $settings[$id] ) ) {
				return $settings[$id];
			}
			
			return '';
		}
		
		public function get_setting_id( $id ) {
			return $this->option . '_' . $id;
		}
		
		public function load_tabs( $tabs = array() ) {
			
			if( empty( $tabs ) ) {
				$tabs = array( 
					'keys' => array(
								'label'    => __( 'Stripe Keys', 'sc' ),
								'template' => 'keys'
							),
					'default' => array(
								'label'    => __( 'Default Settings', 'sc' ),
								'template' => 'default',
							),
				);
			}
			
			$this->tabs = $tabs;
		}
	}
}
