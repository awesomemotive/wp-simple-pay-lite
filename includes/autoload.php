<?php
/**
 * Autoloader
 *
 * @package SimplePay
 */

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
function simpay_autoload_wp_standard_class_filepath( $class ) {
	$filename = explode( '\\', $class );
	$filename = end( $filename );

	$filepath = str_replace(
		array(
			'\\',
			$filename,
		),
		array(
			DIRECTORY_SEPARATOR,
			'',
		),
		$class
	);

	return dirname( __FILE__ ) . $filepath . 'class-' . $filename . '.php';
}

if ( ! function_exists( 'SimplePay_Autoload' ) ) {

	/**
	 * Plugin autoloader.
	 *
	 * Pattern (with or without <Subnamespace>):
	 *  <Namespace>/<Subnamespace>.../Class_Name (or Classname)
	 *  'includes/subdir.../class-name.php' or '...classname.php'
	 *
	 * @since 3.0.0
	 *
	 * @param $class
	 */
	function SimplePay_Autoload( $class ) {
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
		$with_filename_class_prefix = simpay_autoload_wp_standard_class_filepath( $unprefixed );

		if ( file_exists( $with_filename_class_prefix ) ) {
			return require $with_filename_class_prefix;
		}

		// Legacy file name (no `class-` prefix).
		$file_path = str_replace( '\\', DIRECTORY_SEPARATOR, $unprefixed );
		$file      = dirname( __FILE__ ) . $file_path . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}

	}

	// Register the autoloader.
	spl_autoload_register( 'SimplePay_Autoload' );

}
