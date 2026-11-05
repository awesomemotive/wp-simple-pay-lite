<?php
/**
 * Database: BerlinDB table upgrade helpers
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.6
 */

namespace SimplePay\Core\Database;

/**
 * Helpers for BerlinDB tables whose upgrades must only be recorded once their
 * schema changes have actually been applied.
 *
 * BerlinDB records an upgrade as done whenever its callback returns a truthy
 * value, so an upgrade that ignores a failed ALTER is never retried. These
 * helpers report the real result, and skip changes that are already in place
 * so an upgrade can safely run again after a partial failure.
 *
 * @since 4.17.6
 */
trait TableUpgradeTrait {

	/**
	 * {@inheritdoc}
	 *
	 * Compares versions with is_version_at_least() instead of
	 * version_compare(), which can't order this plugin's 12-digit versions on
	 * every platform.
	 *
	 * @since 4.17.6
	 *
	 * @param mixed $version Database version to check if upgrade is needed.
	 * @return bool True if table needs upgrading. False if not.
	 */
	public function needs_upgrade( $version = false ) {
		if ( empty( $version ) ) {
			$version = $this->version;
		}

		// Refreshes $this->db_version from the version option.
		$db_version = $this->get_version();

		return ! $this->is_version_at_least( $db_version, $version );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 4.17.6
	 *
	 * @return array<int|string, mixed> Upgrade callbacks, keyed by their db version.
	 */
	public function get_pending_upgrades() {
		$upgrades = array();

		if ( empty( $this->upgrades ) || empty( $this->db_version ) ) {
			return $upgrades;
		}

		foreach ( $this->upgrades as $version => $callback ) {
			if ( ! $this->is_version_at_least( $this->db_version, $version ) ) {
				$upgrades[ $version ] = $callback;
			}
		}

		return $upgrades;
	}

	/**
	 * Returns whether `$version` is the same as or newer than `$minimum`.
	 *
	 * Purely numeric versions (`202608180001`) are compared digit by digit.
	 * version_compare() parses each part into a C `long`, which is 32 bits on
	 * Windows, so it treats every 12-digit version as equal there and no
	 * upgrade would ever run.
	 *
	 * @since 4.17.6
	 *
	 * @param mixed $version Version to check.
	 * @param mixed $minimum Version to compare against.
	 * @return bool
	 */
	protected function is_version_at_least( $version, $minimum ) {
		$version = is_scalar( $version ) ? (string) $version : '';
		$minimum = is_scalar( $minimum ) ? (string) $minimum : '';

		if ( ctype_digit( $version ) && ctype_digit( $minimum ) ) {
			$version = ltrim( $version, '0' );
			$minimum = ltrim( $minimum, '0' );

			if ( strlen( $version ) !== strlen( $minimum ) ) {
				return strlen( $version ) > strlen( $minimum );
			}

			return strcmp( $version, $minimum ) >= 0;
		}

		return version_compare( $version, $minimum, '>=' );
	}

	/**
	 * Adds a column unless it already exists.
	 *
	 * @since 4.17.6
	 *
	 * @param string $column     Column name.
	 * @param string $definition Column definition, e.g. `varchar(255) DEFAULT NULL AFTER `email``.
	 * @return bool True if the column exists afterwards.
	 */
	protected function add_column_if_missing( $column, $definition ) {
		if ( $this->column_exists( $column ) ) {
			return true;
		}

		$result = $this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD COLUMN `{$column}` {$definition}"
		);

		return false !== $result;
	}

	/**
	 * Adds an index unless it already exists.
	 *
	 * @since 4.17.6
	 *
	 * @param string $index   Index name.
	 * @param string $columns Indexed columns, e.g. `(`email`)`.
	 * @return bool True if the index exists afterwards.
	 */
	protected function add_index_if_missing( $index, $columns ) {
		if ( $this->index_exists( $index ) ) {
			return true;
		}

		$result = $this->get_db()->query(
			"ALTER TABLE {$this->table_name} ADD INDEX {$index} {$columns}"
		);

		return false !== $result;
	}
}
