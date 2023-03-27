<?php
/**
 * Emails: Template interface
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
 * TemplateInterface interface.
 *
 * @since 4.7.3
 */
interface TemplateInterface {

	/**
	 * Returns the CSS styles for the template.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_styles();

	/**
	 * Returns the header markup for the template.
	 *
	 * @since 4.7.3
	 *
	 * @param string $content The header content of the email.
	 * @return string
	 */
	public function get_header( $content );

	/**
	 * Returns the body of the email.
	 *
	 * @since 4.7.3
	 *
	 * @param string $content The content of the email.
	 * @return string
	 */
	public function get_body( $content );

	/**
	 * Returns the footer of the email.
	 *
	 * @since 4.7.3
	 *
	 * @param string $content The footer content of the email.
	 * @return string
	 */
	public function get_footer( $content );

}
