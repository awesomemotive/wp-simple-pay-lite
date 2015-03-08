<?php


// TODO: Add direct file access check

if( ! class_exists( 'MM_Settings' ) ) {
	
	class MM_Settings {
		
		public static $class_version = '1.0.0';
		
		// class variables
		protected $settings;
		protected $option;
		
		public $tabs;
		
		/**
		 * Class constructor
		 * 
		 * @param string $prefix
		 * @param array $settings
		 * 
		 * @since 1.0.0
		 */
		public function __construct( $option ) {
			
			//$this->prefix   = $prefix . '_settings';
			//$this->settings = $settings;
			
			//add_action( 'admin_init', array( $this, 'register_settings' ) );
			$this->option = $option;
			
			if ( false === get_option( $this->option ) ) {
				add_option( $this->option );
			}
			
			$this->set_defaults();
			
			add_action( 'wp_ajax_sc_button_save', array( $this, 'sc_button_save' ) );
			
		}
		
		public function sc_button_save() {
			//echo 'test';
			//echo '<pre>' . print_r( $_POST, true ) . '</pre>';
			
			$settings = array();
			
			$saved = explode( '&', $_POST['form_data'] );
			
			foreach( $saved as $k => $v ) {
				//$settings[$v] = explode();
				$value = explode( '=', $v );
				
				//echo '<pre>' . print_r( $value, true ) . '</pre>';
				
				$settings[$value[0]] = $value[1];
			}
			//echo '<pre>' . print_r( $settings, true ) . '</pre>';		
			
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
		
		public function ajax_save_button( $id, $label ) {
			echo '<button id="test">' . $label . '</button>';
		}
		
		public function do_ajax_save() {
			
			update_option( $this->option, $this->settings );
		}
		
		public function get_settings() {
			$saved_settings = is_array( get_option( $this->option ) ) ? get_option( $this->option ) : array();
			
			return array_merge( $this->settings, $saved_settings );
		}
		
		public function update_settings( $settings ) {
			$this->settings = $settings;
			
			update_option( $this->option, $this->settings );
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
			
			if( isset( $settings[$id] ) && ! empty( $settings[$id] ) ) {
				return $settings[$id];
			}
			
			return '';
		}
		
		public function textbox( $id, $classes = '' ) {
			
			$html = '<input type="text" class="' . esc_html( $classes ) . '" name="' . $this->get_setting_id( $id ) . '" id="' . $this->get_setting_id( $id ) . '" value="' . $this->get_setting_value( $id ) . '" />';
			
			echo $html;
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
	}
}
