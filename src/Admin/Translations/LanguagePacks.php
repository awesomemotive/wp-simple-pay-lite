<?php
/**
 * Translations: Language packs
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.9.0
 */

namespace SimplePay\Core\Admin\Translations;

use Automatic_Upgrader_Skin;
use Language_Pack_Upgrader;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use stdClass;

/**
 * LanguagePacks class.
 *
 * @since 4.9.0
 */
class LanguagePacks implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * @since 4.9.0
	 */
	const API_URL = 'https://translations.wpsimplepay.com/simple-pay/packages.json';

	/**
	 * @since 4.9.0
	 */
	const API_RESPONSE_CACHE_KEY = 'simplepay_language_packs';

	/**
	 * @since 4.9.0
	 */
	const TEXT_DOMAIN = 'simple-pay';

	/**
	 * List of installed translations.
	 *
	 * @since 4.9.0
	 *
	 * @var array<string, mixed>
	 */
	private $installed_translations = array();

	/**
	 * The instance of the core class used for updating/installing language packs (translations).
	 *
	 * @since 4.9.0
	 *
	 * @var \Language_Pack_Upgrader
	 */
	private $upgrader;

	/**
	 * Upgrader Skin for Automatic WordPress Upgrades.
	 *
	 * @since 4.9.0
	 *
	 * @var \Automatic_Upgrader_Skin
	 */
	private $skin;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		// Lite works automatically with .org.
		if ( $this->license->is_lite() ) {
			return array();
		}

		if ( ! is_admin() ) {
			return array();
		}

		if ( ! current_user_can( 'install_languages' ) ) {
			return array();
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/translation-install.php';

		if ( false === wp_can_install_language_pack() ) {
			return array();
		}

		return array(
			// Stub in our language packs when looking for plugin updates.
			'site_transient_update_plugins'     => 'add_available_language_packs',

			// Clear our language pack cache when updating plugins.
			'set_site_transient_update_plugins' => 'clear_language_pack_manifest_cache',

			// Download language packs on plugin activation.
			// See method for details.
			'admin_init'                        => 'on_activate_plugin',

			// Download language packs when changing languages.
			'add_option_WPLANG'                 => array( 'on_language_set', 10, 2 ),
			'update_option_WPLANG'              => array( 'on_language_change', 10, 2 ),
		);
	}

	/**
	 * Attaches the available language packs to the update_plugins transient.
	 *
	 * @since 4.9.0
	 *
	 * @param \stdClass $value Transient data.
	 * @return \stdClass
	 */
	public function add_available_language_packs( $value ) {
		if ( ! $value ) {
			$value = new stdClass;
		}

		if ( ! isset( $value->translations ) ) {
			$value->translations = array();
		}

		$translations = $this->get_available_translations();

		if ( ! empty( $translations ) ) {
			$value->translations = array_merge( $value->translations, $translations );
		}

		return $value;
	}

	/**
	 * Clear the language pack cache.
	 *
	 * @since 4.9.0
	 *
	 * @param array<string, mixed> $transient Transient data.
	 * @return void
	 */
	public function clear_language_pack_manifest_cache( $transient ) {
		global $pagenow;

		if ( 'update-core.php' === $pagenow ) {
			delete_site_transient( self::API_RESPONSE_CACHE_KEY );
		}
	}

	/**
	 * Downloads language packs when the site language is initially set.
	 *
	 * @since 4.9.0
	 *
	 * @param string $option_name The previous language.
	 * @param string $locale The new locale.
	 * @return void
	 */
	public function on_language_set( $option_name, $locale ) {
		if ( '' === $locale ) {
			return;
		}

		$this->install_language_pack( $locale );
	}

	/**
	 * Downloads language packs when the site language is changed.
	 *
	 * @since 4.9.0
	 *
	 * @param string $old_value The previous language.
	 * @param string $locale The new locale.
	 * @return void
	 */
	public function on_language_change( $old_value, $locale ) {
		if ( '' === $locale ) {
			return;
		}

		$this->install_language_pack( $locale );
	}

	/**
	 * Downloads language packs when the plugin is activated.
	 *
	 * We cannot use the simpay_activated hook because the plugin container is
	 * loaded too late. Instead, we use the admin_init hook to check for the
	 * simpay_activation_redirect transient which is set in the main plugin file.
	 *
	 * @since 4.9.0
	 *
	 * @return void
	 */
	public function on_activate_plugin() {
		global $pagenow;

		if ( 'plugins.php' !== $pagenow ) {
			return;
		}

		if ( ! get_transient( 'simpay_activation_redirect' ) ) {
			return;
		}

		$this->install_language_pack( get_locale() );
	}

	/**
	 * Downloads language packs based on available core translations.
	 *
	 * @since 4.9.0
	 *
	 * @param string $locale The locale to install.
	 * @return void
	 */
	private function install_language_pack( $locale ) {
		$available_translations = $this->get_available_translations();

		if ( empty( $available_translations ) || ! isset( $available_translations[ $locale ] ) ) {
			return;
		}

		$language_pack = (object) $available_translations[ $locale ];

		$this->skin                  = new Automatic_Upgrader_Skin();
		$this->skin->language_update = $language_pack; // @phpstan-ignore-line

		$this->upgrader = new Language_Pack_Upgrader( $this->skin );
		$this->upgrader->run(
			array(
				'package'                     => $language_pack->package,
				'destination'                 => WP_LANG_DIR . '/plugins',
				'abort_if_destination_exists' => false,
				'is_multi'                    => true,
				'hook_extra'                  => array(
					'language_update_type' => $language_pack->type,
					'language_update'      => $language_pack,
				),
			)
		);
	}

	/**
	 * Get a list of translations available for the plugin, keyed by locale.
	 *
	 * @since 4.9.0
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_available_translations() {
		$manifest = $this->get_manifest_data();

		if ( false === $manifest ) {
			return array();
		}

		$available_remote_translations = $manifest['translations'];

		if ( empty( $available_remote_translations ) ) {
			return array();
		}

		$installed_translations = $this->get_installed_translations();
		$available_translations = array();

		foreach ( $available_remote_translations as $remote_translation_data ) {
			if ( empty( $remote_translation_data['language'] ) ) {
				continue;
			}

			$language = $remote_translation_data['language'];

			/** @var array{"PO-Revision-Date"?: string} */
			$local_translation_data = isset(
				$installed_translations[ self::TEXT_DOMAIN ],
				$installed_translations[ self::TEXT_DOMAIN ][ $language ] // @phpstan-ignore-line
			)
				? $installed_translations[ self::TEXT_DOMAIN ][ $language ]
				: array();

			// Skip languages which were updated locally so they are not overwritten.
			if ( isset(
				$local_translation_data['PO-Revision-Date'],
				$remote_translation_data['updated']
			) ) {
				$local  = strtotime( $local_translation_data['PO-Revision-Date'] );
				$remote = strtotime( $remote_translation_data['updated'] );

				if ( $local >= $remote ) {
					continue;
				}
			}

			// Key a new array by the language code.
			$available_translations[ $language ] = $remote_translation_data;
		}

		return $available_translations;
	}

	/**
	 * Get installed translations.
	 *
	 * @since 4.9.0
	 *
	 * @return array<string, mixed> Language data.
	 */
	private function get_installed_translations() {
		if ( $this->installed_translations ) {
			return $this->installed_translations;
		}

		$this->installed_translations = wp_get_installed_translations( 'plugins' );

		return $this->installed_translations;
	}

	/**
	 * Returns the translation language packs manifest data.
	 *
	 * @since 4.9.0
	 *
	 * @return array{translations: array{language: string, version:string, updated: string, package: string, type: string, slug: string, iso: array<string>}[]}|false
	 */
	private function get_manifest_data() {
		/** @var array{translations: array{language: string, version:string, updated: string, package: string, type: string, slug: string, iso: array<string>}[]}|false $manifest */
		$manifest = get_site_transient( self::API_RESPONSE_CACHE_KEY );

		if ( false !== $manifest ) {
			return $manifest;
		}

		$request = wp_remote_get(
			self::API_URL,
			array(
				'timeout' => 2,
			)
		);

		$response = wp_remote_retrieve_response_code( $request );

		$empty_manifest = array(
			'translations' => array(),
		);

		if ( 200 !== $response ) {
			$manifest = $empty_manifest;
		} else {
			$response = wp_remote_retrieve_body( $request );
			$manifest = json_decode( $response, true );

			if ( ! is_array( $manifest ) || empty( $manifest['translations'] ) ) {
				$manifest = $empty_manifest;
			}
		}

		/** @var array{translations: array{language: string, version:string, updated: string, package: string, type: string, slug: string, iso: array<string>}[]}|false $manifest */

		set_site_transient( self::API_RESPONSE_CACHE_KEY, $manifest );

		return $manifest;
	}
}
