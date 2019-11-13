<?php

namespace SimplePay\Core\Admin;

use SimplePay\Core\Abstracts\Field;
use SimplePay\Core\Abstracts\Admin_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin pages class.
 *
 * Handles settings pages and settings UI in admin dashboard.
 *
 * @since 3.0.0
 */
class Pages {

	/**
	 * Current settings page.
	 *
	 * @access private
	 * @var string
	 */
	private $page = '';

	/**
	 * Default tab.
	 *
	 * @access private
	 * @var string
	 */
	private $tab = '';

	/**
	 * Settings pages.
	 *
	 * @access private
	 * @var array
	 */
	private $settings = array();

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param string $page
	 */
	public function __construct( $page = 'settings' ) {

		$this->page     = $page;
		$settings_pages = ! is_null( \SimplePay\Core\SimplePay()->objects ) ? simpay_get_admin_pages() : '';

		$settings_page_tabs = array();
		$tabs               = isset( $settings_pages[ $page ] ) ? $settings_pages[ $page ] : false;

		if ( $tabs && is_array( $tabs ) ) {
			foreach ( $tabs as $tab ) {

				$settings_page = simpay_get_admin_page( $tab );

				if ( $settings_page instanceof Admin_Page ) {
					$settings_page_tabs[ $settings_page->id ] = $settings_page;
				}
			}

			$this->settings = $settings_page_tabs;
		}

		// The first tab is the default tab when opening a page.
		$this->tab = isset( $tabs[0] ) ? $tabs[0] : '';

		add_filter( 'admin_footer_text', array( $this, 'add_footer_text' ) );

		do_action( 'simpay_admin_pages', $page );
	}

	public function add_footer_text( $footer_text ) {

		if ( simpay_is_admin_screen() ) {
			/* Translators: 1. The plugin name */
			$footer_text = sprintf( __( 'If you like <strong>%1$s</strong> please leave us a %2$s rating. A huge thanks in advance!', 'stripe' ),
			SIMPLE_PAY_PLUGIN_NAME, '<a href="https://wordpress.org/support/plugin/stripe/reviews?rate=5#new-post" rel="noopener noreferrer" target="_blank" class="simpay-rating-link" data-rated="' .
			esc_attr__( 'Thanks :)', 'stripe' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
		}

		return $footer_text;
	}

	/**
	 * Get settings pages.
	 *
	 * @since  3.0.0
	 * @access private
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = array();

		if ( ! empty( $this->settings ) && is_array( $this->settings ) ) {
			foreach ( $this->settings as $id => $object ) {

				if ( $object instanceof Admin_Page ) {

					$settings_page = $object->get_settings();

					if ( isset( $settings_page[ $id ] ) ) {
						$settings[ $id ] = $settings_page[ $id ];
					}
				}

			}
		}

		return $settings;
	}

	/**
	 * Register settings.
	 *
	 * Adds settings sections and fields to settings pages.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings
	 */
	public function register_settings( $settings = array() ) {

		$settings = $settings ? $settings : $this->get_settings();

		if ( ! empty( $settings ) && is_array( $settings ) ) {

			foreach ( $settings as $tab_id => $settings_page ) {

				if ( isset( $settings_page['sections'] ) ) {

					$sections = $settings_page['sections'];

					if ( ! empty( $sections ) && is_array( $sections ) ) {

						foreach ( $sections as $section_id => $section ) {

							add_settings_section( $section_id, isset( $section['title'] ) ? $section['title'] : '', isset( $section['callback'] ) ? $section['callback'] : '', 'simpay_' . $this->page . '_' . $tab_id );

							if ( isset( $section['fields'] ) ) {

								$fields = $section['fields'];

								if ( ! empty( $fields ) && is_array( $fields ) ) {

									foreach ( $fields as $field ) {

										if ( isset( $field['id'] ) && isset( $field['type'] ) ) {

											$field_object = simpay_get_field( $field, $field['type'] );

											if ( $field_object instanceof Field ) {

												add_settings_field( $field['id'], isset( $field['title'] ) ? $field['title'] : '', array(
													$field_object,
													'html',
												), 'simpay_' . $this->page . '_' . $tab_id, $section_id );

											} // add field

										} // is field valid?

									} // loop fields

								} // are fields non empty?

							} // are there fields?

							$page = simpay_get_admin_page( $tab_id );

							register_setting( 'simpay_' . $this->page . '_' . $tab_id, 'simpay_' . $this->page . '_' . $tab_id, $page instanceof Admin_Page ? array(
								$page,
								'validate',
							) : '' );

						} // loop sections

					} // are sections non empty?

				} // are there sections?

			} // loop settings

		} // are there settings?

	}

	/**
	 * Print Settings Pages.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		global $current_tab;

		// Get current tab/section
		$current_tab = empty( $_GET['tab'] ) ? $this->tab : sanitize_title( $_GET['tab'] );
		$this->tab   = $current_tab;

		$sidebar = apply_filters( 'simpay_settings_sidebar_template', SIMPLE_PAY_INC . 'core/admin/notices/promos/general/sidebar.php' );

		?>
		<div id="simpay-global-settings" class="wrap <?php echo ! empty( $sidebar ) ? 'simpay-global-settings--has-sidebar' : null; ?>">
			<h1><?php echo get_admin_page_title(); ?></h1>

			<div id="simpay-settings-left">
				<form id="simpay-settings-page-form"
				      method="post"
				      action="options.php">
					<?php

					// Include settings pages
					$settings_pages = self::get_settings();
					if ( ! empty( $settings_pages ) && is_array( $settings_pages ) ) {

						echo '<h2 class="nav-tab-wrapper simpay-nav-tab-wrapper">';

						// Get tabs for the settings page
						if ( ! empty( $settings_pages ) && is_array( $settings_pages ) ) {

							foreach ( $settings_pages as $id => $settings ) {

								$tab_id    = isset( $id ) ? $id : '';
								$tab_label = isset( $settings['label'] ) ? $settings['label'] : '';
								$tab_link  = admin_url( 'admin.php?page=simpay_settings&tab=' . $tab_id );

								echo '<a href="' . $tab_link . '" class="nav-tab ' . ( $current_tab == $tab_id ? 'nav-tab-active' : '' ) . '">' . $tab_label . '</a>';
							}

						}

						do_action( 'simpay_admin_page_' . $this->page . '_tabs' );

						echo '</h2>';

						settings_errors();

						foreach ( $settings_pages as $tab_id => $contents ) {

							if ( $tab_id === $current_tab ) {

								echo isset( $contents['description'] ) ? '<p>' . $contents['description'] . '</p>' : '';

								do_action( 'simpay_admin_page_' . $this->page . '_' . $current_tab . '_start' );

								settings_fields( 'simpay_' . $this->page . '_' . $tab_id );
								do_settings_sections( 'simpay_' . $this->page . '_' . $tab_id );

								do_action( 'simpay_admin_page_' . $this->page . '_' . $current_tab . '_end' );

								// TODO Hide general settings docs links for now (issue #301).
								// Leave for now as it may come back in some form later on.
								// If not remove this and related properties.
								//simpay_docs_link( $contents['link_text'], $contents['link_slug'], $contents['ga_content'] );

								$submit = apply_filters( 'simpay_admin_page_' . $this->page . '_' . $current_tab . '_submit', true );
								if ( true === $submit ) {
									submit_button();
								}
							}
						}
					}

					?>
				</form>
			</div>

			<div id="simpay-settings-sidebar-right">
				<?php
					if ( ! empty( $sidebar ) ) {
						include_once( $sidebar );
					}
				?>
			</div>

			<br class="simpay-clearfix">
		</div>
		<?php

	}

}
