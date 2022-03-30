<?php
/**
 * Admin notice: Abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\AdminNotice;

use Sandhills\Utils\Persistent_Dismissible;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * AbstractAdminNotice abstract.
 *
 * @since 4.4.0
 */
abstract class AbstractAdminNotice implements AdminNoticeInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_id();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_type();

	/**
	 * {@inheritdoc}
	 */
	abstract public function is_dismissible();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_dismissal_length();

	/**
	 * {@inheritdoc}
	 */
	abstract public function should_display();

	/**
	 * {@inheritdoc}
	 */
	public function is_dismissed() {
		$notice_id = $this->get_id();
		$permanent = (bool) get_option( 'simpay_dismiss_' . $notice_id, false );

		if ( true === $permanent ) {
			return true;
		}

		$temporary = (bool) Persistent_Dismissible::get(
			array(
				'id' => $notice_id,
			)
		);

		if ( true === $temporary ) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_data() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_view() {
		$view_file_name = sprintf( 'admin-notice-%s.php', $this->get_id() );

		return SIMPLE_PAY_DIR . 'views/' . $view_file_name; // @phpstan-ignore-line
	}

	/**
	 * {@inheritdoc}
	 */
	public function render() {
		$view_file_path = $this->get_view();

		if ( ! file_exists( $view_file_path ) ) {
			return;
		}

		$data = $this->get_notice_data();

		echo '<div ' . $this->build_attribute_string( $this->get_attributes() ) . '>';
		require $view_file_path;
		echo '</div>';
	}

	/**
	 * Returns the notice attributes.
	 *
	 * @since 4.4.1
	 *
	 * @return array<string, string>
	 */
	protected function get_attributes() {
		$attributes = array();

		// class.
		$classes = array(
			'simpay-notice',
			'notice',
			'notice-' . $this->get_type()
		);

		if ( true === $this->is_dismissible() ) {
			$classes[] = 'is-dismissible';
		}

		$attributes['class'] = implode( ' ', $classes );

		// Data.
		if ( true === $this->is_dismissible() ) {
			$attributes['data-nonce'] = wp_create_nonce(
				'simpay-dismiss-notice-' . $this->get_id()
			);

			$attributes['data-id'] = $this->get_id();

			if ( $this->get_dismissal_length() > 0 ) {
				$attributes['data-lifespan'] = (string) $this->get_dismissal_length();
			}
		}

		return $attributes;
	}

	/**
	 * Builds an HTML element attribute string given an list of attributes.
	 *
	 * @since 4.4.1
	 *
	 * @param array<string, string> $attributes Attributes to convert in to a string.
	 * @return string
	 */
	private function build_attribute_string( $attributes ) {
		$attribute_string = '';

		foreach ( $attributes as $attribute_name => $attribute_value ) {
			$attribute_string .= sprintf(
				'%s="%s" ',
				$attribute_name,
				esc_attr( $attribute_value )
			);
		}

		return $attribute_string;
	}

}
