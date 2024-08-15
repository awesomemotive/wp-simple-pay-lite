<?php
/**
 * Emails: Abstract
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails\Email;

use SimplePay\Core\Emails\Template\DefaultTemplate;
use SimplePay\Core\Emails\Template\PlainTemplate;
use SimplePay\Core\NotificationInbox\NotificationAwareInterface;
use SimplePay\Core\NotificationInbox\NotificationAwareTrait;
use SimplePay\Core\NotificationInbox\NotificationRepository;
use SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email class.
 *
 * @since 4.7.3
 */
abstract class AbstractEmail implements EmailInterface, NotificationAwareInterface {

	use NotificationAwareTrait;

	const INTERNAL_TYPE = 'internal';
	const EXTERNAL_TYPE = 'external';

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
	abstract public function get_label();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_description();

	/**
	 * {@inheritdoc}
	 */
	public function get_licenses() {
		return array(
			'personal',
			'plus',
			'professional',
			'ultimate',
			'elite',
		);
	}

	/**
	 * Determines if the email is active, and can be configured.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function is_available() {
		$license = simpay_get_license();

		return in_array( $license->get_level(), static::get_licenses(), true );
	}

	/**
	 * Determines if the email is enabled, and should send.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$enabled = 'yes' === simpay_get_setting(
			sprintf(
				'email_%s',
				static::get_id()
			),
			'yes'
		);

		return static::is_available() && $enabled;
	}

	/**
	 * Returns a list of emails that should be forced to use a styled theme.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string>
	 */
	private function get_style_required_emails() {
		return array(
			'summary-report',
		);
	}

	/**
	 * Returns the name of the email template to use.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	private function get_template_name() {
		/** @var string $template */
		$template = simpay_get_setting( 'email_template', 'none' );

		if ( in_array( static::get_id(), $this->get_style_required_emails(), true ) ) {
			$template = 'default';
		}

		return $template;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_template() {
		switch ( $this->get_template_name() ) {
			case 'default':
				return new DefaultTemplate();
			default:
				return new PlainTemplate();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_header_content() {
		$type = $this->get_type();

		switch ( $type ) {
			case 'internal':
				return $this->get_internal_header_content();
			default:
				return $this->get_external_header_content();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_footer_content() {
		$type = $this->get_type();

		switch ( $type ) {
			case 'internal':
				return $this->get_internal_footer_content();
			default:
				return $this->get_external_footer_content();
		}
	}

	/**
	 * Returns the header content for internal emails.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	private function get_internal_header_content() {
		$alert = array();

		// If we can use notifications, check for an error notification.
		if ( $this->notifications instanceof NotificationRepository ) {
			$internal_alerts = $this->notifications->query(
				array(
					'source'         => 'internal',
					'type'           => 'error',
					'dismissed'      => false,
					'is_dismissible' => false,
				)
			);

			if ( ! empty( $internal_alerts ) ) {
				/** @var \SimplePay\Core\NotificationInbox\Notification */
				$notification = $internal_alerts[0];

				$links = array_filter(
					$notification->actions,
					function ( $action ) {
						return 'primary' === $action['type'];
					}
				);

				$alert = array(
					'title'   => $notification->title,
					'content' => $notification->content,
					'links'   => $links,
				);
			}
		}

		$logo_url = SIMPLE_PAY_URL . 'includes/core/assets/images/wp-simple-pay.png'; // @phpstan-ignore-line
		$image    = sprintf( '<img src="%1$s" alt="WP Simple Pay" />', $logo_url );

		ob_start();

		require SIMPLE_PAY_DIR . 'views/email-internal-header-content.php'; // @phpstan-ignore-line

		/** @var string $html */
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Returns the header content for external emails.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_external_header_content() {
		/** @var string $image_url */
		$image_url = simpay_get_setting( 'email_header_image_url', '' );
		$image     = '';

		if ( ! empty( $image_url ) ) {
			$image = sprintf(
				'<a href="%1$s" target="_blank"><img src="%2$s" alt="%3$s" style="max-width: 300px;" /></a>',
				esc_url( home_url() ),
				esc_url( $image_url ),
				get_bloginfo( 'name' )
			);
		}

		ob_start();

		require SIMPLE_PAY_DIR . 'views/email-external-header-content.php'; // @phpstan-ignore-line

		/** @var string $html */
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Returns the footer content to load inside the template footer.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	private function get_internal_footer_content() {
		$settings_url = Settings\get_url(
			array(
				'section' => 'emails',
			)
		);

		$credit = wp_kses(
			sprintf(
				/* translators: %1$s Website name. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
				__(
					'This email was automatically generated and sent from your website %1$s.<br />Manage %2$semail settings%3$s.',
					'stripe'
				),
				'<a href="' . esc_url( home_url() ) . '">' . get_bloginfo( 'name' ) . '</a>',
				'<a href="' . esc_url( $settings_url ) . '">',
				'</a>'
			),
			array(
				'a'  => array(
					'href' => array(),
				),
				'br' => array(),
			)
		);

		// @todo Add support for dynamic "Did you know?" blurbs. This should also
		// replace the "Tips" displayed in "Activity & Reports"
		$blurb = array();

		ob_start();

		require SIMPLE_PAY_DIR . 'views/email-internal-footer-content.php'; // @phpstan-ignore-line

		/** @var string $html */
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Returns the footer content for external emails.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_external_footer_content() {
		$content = simpay_get_setting( 'email_footer_content', '' );

		ob_start();

		require SIMPLE_PAY_DIR . 'views/email-external-footer-content.php'; // @phpstan-ignore-line

		/** @var string $html */
		$html = ob_get_clean();

		return $html;
	}
}
