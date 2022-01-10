<?php
/**
 * Bootstrap: Autoload
 *
 * @package SimplePay\Core\Bootstrap
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Bootstrap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate a filepath for a class according to the WordPress coding standards.
 *
 * @since 3.5.0
 *
 * @param string $class Unprefixed PHP class name.
 * @return string $filepath Full file path and filename.
 */
function wp_standard_class_filepath( $class ) {
	$filename = explode( '\\', $class );
	$filename = end( $filename );

	// Get path pieces.
	$filepath = str_replace( '\\', DIRECTORY_SEPARATOR, $class );

	// Remove the filename. Only uses the last occurance of the filename
	// to avoid removing a directory with the same name.
	$position = strrpos( $filepath, $filename );

	if ( false !== $position ) {
		$filepath = substr_replace( $filepath, '', $position, strlen( $filename ) );
	}

	// Try prefixes.
	foreach ( array( 'class', 'abstract', 'interface' ) as $type ) {
		$file = untrailingslashit( SIMPLE_PAY_INC ) . $filepath . $type . '-' . $filename . '.php';

		if ( file_exists( $file ) ) {
			return $file;
		}
	}

	// Fallback to class-.
	return untrailingslashit( SIMPLE_PAY_INC ) . $filepath . 'class-' . $filename . '.php';
}

if ( ! function_exists( __NAMESPACE__ . '\\autoload' ) ) {

	/**
	 * Plugin autoloader.
	 *
	 * Pattern (with or without <Subnamespace>):
	 *  <Namespace>/<Subnamespace>.../Class_Name (or Classname)
	 *  'includes/subdir.../class-name.php' or '...classname.php'
	 *
	 * @since 3.0.0
	 *
	 * @param string $class Class to load.
	 */
	function autoload( $class ) {
		// Do not load unless in plugin domain.
		$namespace = 'SimplePay';

		if ( strpos( $class, $namespace ) !== 0 ) {
			return;
		}

		// Converts Class_Name (class convention) to class-name (file convention).
		$class_name = implode( '-', array_map( 'lcfirst', explode( '_', strtolower( $class ) ) ) );

		// Remove the root namespace.
		$unprefixed = substr( $class_name, strlen( $namespace ) );

		// Look for files with the WordPress standard `class-` prefix.
		$with_filename_class_prefix = wp_standard_class_filepath( $unprefixed );

		if ( file_exists( $with_filename_class_prefix ) ) {
			return require $with_filename_class_prefix;
		}

		// Legacy file name (no `class-` prefix).
		$file_path = str_replace( '\\', DIRECTORY_SEPARATOR, $unprefixed );
		$file      = untrailingslashit( SIMPLE_PAY_INC ) . $file_path . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}

	}

	// Register the autoloader.
	spl_autoload_register( __NAMESPACE__ . '\\autoload' );

}
