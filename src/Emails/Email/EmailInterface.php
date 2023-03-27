<?php
/**
 * Emails: Interface
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EmailInterface interface.
 *
 * @since 4.7.3
 */
interface EmailInterface {

	/**
	 * Returns the unique ID of the email.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Returns the type of email.
	 *
	 * @since 4.7.3
	 *
	 * @return string 'internal' or 'external'.
	 */
	public function get_type();

	/**
	 * Returns the label of the email.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Returns the descriptor of the email.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Returns the template the email should use.
	 *
	 * @since 4.7.3
	 *
	 * @return \SimplePay\Core\Emails\Template\TemplateInterface
	 */
	public function get_template();

	/**
	 * Returns a list of license levels the email is available for.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string>
	 */
	public function get_licenses();

	/**
	 * Determines if the email is available for use.
	 *
	 * This is usually determined by the current license level.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function is_available();

	/**
	 * Determines if the email is enabled (usually via a UI) and should send.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Returns the header content to load inside the template header.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_header_content();

	/**
	 * Returns the footer content to load inside the template footer.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_footer_content();

}
