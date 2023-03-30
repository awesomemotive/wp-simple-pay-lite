<?php
/**
 * Emails: Abstract template
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails\Template;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AbstractTemplate class.
 *
 * @since 4.7.3
 */
abstract class AbstractTemplate implements TemplateInterface {

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_styles();

	/**
	 * {@inherit}
	 */
	abstract public function get_header( $content );

	/**
	 * {@inherit}
	 */
	abstract public function get_body( $content );

	/**
	 * {@inherit}
	 */
	abstract public function get_footer( $content );

}
