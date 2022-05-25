<?php
/**
 * Settings: Display
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Settings;

use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs the setting's primary navigation.
 *
 * @since 4.0.0
 *
 * @param string $current_section Current Section ID.
 */
function primary_nav( $current_section ) {
	$sections = Utils\get_collection( 'settings-sections' );

	if ( false === $sections ) {
		return;
	}

	$tabs = $sections->get_items();
	?>

	<div class="clear"></div>
	<h2 class="nav-tab-wrapper simpay-nav-tab-wrapper">
		<?php
		foreach ( $tabs as $tab ) :
			$url = get_url(
				array(
					'section' => $tab->id,
				)
			);

			$active_class = $current_section === $tab->id
				? 'nav-tab-active'
				: ''
			?>
		<a
			href="<?php echo esc_url( $url ); ?>"
			class="nav-tab <?php echo esc_attr( $active_class ); ?>"
		>
			<?php echo wp_kses_post( $tab->label ); ?>
		</a>
		<?php endforeach; ?>


		<?php
		/**
		 * Allows furhter output after settings tabs.
		 *
		 * @since 3.0.0
		 */
		do_action( 'simpay_admin_page_settings_tabs' );
		?>
	</h2>

	<?php
}

/**
 * Outputs the setting's secondary navigation.
 *
 * @since 4.0.0
 *
 * @param string $current_subsection Current Subsection ID.
 */
function secondary_nav( $current_subsection ) {
	$sections    = Utils\get_collection( 'settings-sections' );
	$subsections = Utils\get_collection( 'settings-subsections' );

	if ( false === $sections || false === $subsections ) {
		return;
	}

	// Get current Subsection.
	$subsection = $subsections->get_item( $current_subsection );

	if ( false === $subsection ) {
		return;
	}

	// Get current Section.
	$section = $sections->get_item( $subsection->section );

	if ( false === $section ) {
		return;
	}

	// Get the current Section's Subsections.
	$tabs = $section->get_subsections();

	// Don't output if there is only one registered Subsection.
	if ( 1 === count( $tabs ) ) {
		return;
	}
	?>

	<nav class="simpay-settings-subsections">
		<?php
		foreach ( $tabs as $tab ) :
			$url = get_url(
				array(
					'section'    => $tab->section,
					'subsection' => $tab->id,
				)
			);

			$active_class = $current_subsection === $tab->id
				? 'is-active'
				: ''
			?>
		<a
			href="<?php echo esc_url( $url ); ?>"
			class="simpay-settings-subsections__subsection simpay-settings-subsection-<?php echo esc_attr( $tab->id ); ?> <?php echo esc_attr( $active_class ); ?>"
		>
			<?php echo wp_kses_post( $tab->label ); ?>
		</a>
		<?php endforeach; ?>
	</nav>

	<?php
}

/**
 * Outputs relevant registered Sections, Subsections, and Settings for the
 * current view.
 *
 * @since 4.0.0
 */
function page() {
	$section = ! empty( $_GET['tab'] )
		? sanitize_key( $_GET['tab'] )
		: get_main_section_id();

	$subsection = ! empty( $_GET['subsection'] )
		? sanitize_key( $_GET['subsection'] )
		: get_main_subsection_id( $section );

	// Find the legacy "page" value for hook/filter compatibility.
	switch ( $section ) {
		case 'stripe':
			$page = 'keys';
			break;
		case 'payment-confirmations':
			$page = 'display';
			break;
		default:
			$page = $section;
	}
	?>

	<div id="simpay-global-settings" class="wrap">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Settings', 'stripe' ); ?>
		</h1>
		<hr class="wp-header-end">

		<div class="simpay-settings <?php echo esc_attr( $section . '-' . $subsection ); ?>">
			<?php
			settings_errors();
			primary_nav( $section );
			secondary_nav( $subsection );

			/**
			 * Allows output before a settings subsection.
			 *
			 * @since 4.4.6
			 */
			do_action( 'simpay_admin_page_settings_' . $page . '_before' );
			?>

			<form method="post" action="options.php">
				<?php
				/**
				 * Allows output before a settings section.
				 *
				 * @since 3.0.0
				 */
				do_action( 'simpay_admin_page_settings_' . $page . '_start' );

				settings_fields( 'simpay_settings' );

				do_settings_sections(
					sprintf(
						'simpay_settings_%s_%s',
						$section,
						$subsection
					)
				);

				/**
				 * Allows output after a settings section.
				 *
				 * @since 3.0.0
				 */
				do_action( 'simpay_admin_page_settings_' . $page . '_end' );

				/**
				 * Filters the display of the setting's "Submit" button.
				 *
				 * @since 3.0.0
				 *
				 * @param bool $submit If the submit button should display. Default true.
				 */
				$submit = apply_filters(
					'simpay_admin_page_settings_' . $page . '_submit',
					true
				);

				if ( true === $submit ) {
					submit_button();
				}
				?>

				<input type="hidden" name="section" value="<?php echo esc_attr( $section ); ?>" />
				<input type="hidden" name="subsection" value="<?php echo esc_attr( $subsection ); ?>" />
			</form>

			<?php
			/**
			 * Allows further output after all content on all settings pages.
			 *
			 * @since 4.4.0
			 */
			do_action( '__unstable_simpay_admin_page_settings_end' );
			?>
		</div>
	</div>

	<?php
}

/**
 * Adds script data to visually toggle registered settings.
 *
 * @since 4.0.0
 */
function register_setting_toggles() {
	$section = ! empty( $_GET['tab'] )
		? sanitize_key( $_GET['tab'] )
		: get_main_section_id();

	$subsection = ! empty( $_GET['subsection'] )
		? sanitize_key( $_GET['subsection'] )
		: get_main_subsection_id( $section );

	$settings = Utils\get_collection( 'settings' );

	if ( false === $settings ) {
		return;
	}

	$settings = $settings->by( 'subsection', $subsection );

	$toggles = array();

	foreach ( $settings as $setting ) {
		if ( empty( $setting->toggles ) ) {
			continue;
		}

		$toggles[] = array(
			'id'      => $setting->id,
			'value'   => $setting->toggles['value'],
			'toggles' => $setting->toggles['settings'],
		);
	}

	wp_localize_script(
		'simpay-admin',
		'simpayAdminSettingToggles',
		$toggles
	);
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\register_setting_toggles', 20 );
