<?php
/**
 * Emails: Default template
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
 * DefaultTemplate class.
 *
 * @since 4.7.3
 */
class DefaultTemplate extends AbstractTemplate {

	/**
	 * {@inheritdoc}
	 */
	public function get_styles() {
		$file = SIMPLE_PAY_DIR . 'includes/core/assets/css/simpay-email-template-default.min.css';

		if ( ! file_exists( $file ) ) {
			return '';
		}

		$styles = file_get_contents( $file );

		if ( false === $styles ) {
			return '';
		}

		return $styles;
	}

	/**
	 * {@inherit}
	 */
	public function get_header( $content ) {
		ob_start();

		require SIMPLE_PAY_DIR . 'views/email-template-default-header.php';

		/** @var string $html */
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * {@inherit}
	 */
	public function get_body( $content ) {
		ob_start();

		require SIMPLE_PAY_DIR . 'views/email-template-default-body.php';

		/** @var string $html */
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * {@inherit}
	 */
	public function get_footer( $content ) {
		ob_start();

		require SIMPLE_PAY_DIR . 'views/email-template-default-footer.php';

		/** @var string $html */
		$html = ob_get_clean();

		return $html;
	}

}
