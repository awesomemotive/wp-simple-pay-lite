<?php
/**
 * Emails: Plain (none) template
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
 * PlainTemplate class.
 *
 * @since 4.7.3
 */
class PlainTemplate extends AbstractTemplate {

	/**
	 * {@inheritdoc}
	 */
	public function get_styles() {
		return '';
	}

	/**
	 * {@inherit}
	 */
	public function get_header( $content ) {
		return '';
	}

	/**
	 * {@inherit}
	 */
	public function get_body( $content ) {
		return $content;
	}

	/**
	 * {@inherit}
	 */
	public function get_footer( $content ) {
		return '';
	}

}
