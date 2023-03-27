<?php
/**
 * Emails: Mailer
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails;

use SimplePay\Core\Payments\Payment_Confirmation;
use SimplePay\Vendor\TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailer class.
 *
 * @since 4.7.3
 */
class Mailer {

	/**
	 * The email being processed.
	 *
	 * @since 4.7.3
	 * @var \SimplePay\Core\Emails\Email\EmailInterface
	 */
	protected $email;

	/**
	 * Whether the mail is being sent in Live or Test Mode.
	 *
	 * @since 4.7.3
	 * @var bool
	 */
	protected $is_livemode;

	/**
	 * The data associated with the email.
	 *
	 * @since 4.7.3
	 * @var array<string, mixed>
	 */
	protected $data;

	/**
	 * The address(es) to send the email to.
	 *
	 * @since 4.7.3
	 * @var string|array<string>
	 */
	protected $to;

	/**
	 * The subject of the email.
	 *
	 * @since 4.7.3
	 * @var string
	 */
	protected $subject;

	/**
	 * The content of the email.
	 *
	 * @since 4.7.3
	 * @var string
	 */
	protected $body;

	/**
	 * Sets the email to be processed.
	 *
	 * @since 4.7.3
	 *
	 * @param \SimplePay\Core\Emails\Email\EmailInterface $email The email to be processed.
	 */
	public function __construct( $email ) {
		$this->email = $email;
	}

	/**
	 * Returns the payment mode.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function is_livemode() {
		if ( is_null( $this->is_livemode ) ) {
			$this->is_livemode = simpay_is_livemode();
		}

		return $this->is_livemode;
	}

	/**
	 * Sets the relevant payment mode.
	 *
	 * @since 4.7.3
	 *
	 * @param bool $is_livemode Whether the mail is being sent in Live or Test Mode.
	 * @return void
	 */
	public function set_livemode( $is_livemode ) {
		$this->is_livemode = $is_livemode;
	}

	/**
	 * Returns the associated payment data.
	 *
	 * @since 4.7.3
	 *
	 * @return array<string, mixed>
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Sets the associated payment data.
	 *
	 * @since 4.7.3
	 *
	 * @param array<string, mixed> $data Payment data.
	 * @return void
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}

	/**
	 * Returns email header information.
	 *
	 * Pulls name and address from stored settings.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	private function get_headers() {
		$name         = $this->get_from_name();
		$address      = $this->get_from_address();
		$content_type = $this->get_content_type();

		$headers  = sprintf( "From: %s <%s>\r\n", $name, $address );
		$headers .= sprintf(
			"Content-Type: %s; charset=utf-8\r\n",
			$content_type
		);

		return $headers;
	}

	/**
	 * Returns the address(es) to send the email to.
	 *
	 * @since 4.7.3
	 *
	 * @return string|array<string>
	 */
	public function get_to() {
		return $this->to;
	}

	/**
	 * Sets the address(es) to send the email to.
	 *
	 * @since 4.7.3
	 *
	 * @param string|array<string> $to The address(es) to send the email to.
	 * @return void
	 */
	public function set_to( $to ) {
		$this->to = $to;
	}

	/**
	 * Returns the subject of the email.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_subject() {
		if ( true === $this->is_livemode() ) {
			$subject = $this->subject;
		} else {
			$subject = sprintf(
				/* translators: %s Email subject */
				__( '[Test Mode] %s', 'stripe' ),
				$this->subject
			);
		}

		return html_entity_decode( $this->parse_smart_tags( $subject ) );
	}

	/**
	 * Sets the subject of the email.
	 *
	 * @since 4.7.3
	 *
	 * @param string $subject The subject of the email.
	 * @return void
	 */
	public function set_subject( $subject ) {
		$this->subject = $subject;
	}

	/**
	 * Returns the HTML for the email (body).
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_body() {
		$template = $this->email->get_template();

		$autop = true;

		/**
		 * Determines if an email's message body should have wpautop applied.
		 *
		 * @since 4.0.0
		 *
		 * @param bool $autop Determines if an email's message body should have
		 *                    wpautop applied.
		 */
		$autop = apply_filters( 'simpay_emails_autop', $autop );

		$body = $this->parse_smart_tags( $this->body );
		$body = $autop ? wpautop( $body ) : $body;

		$css_to_inline_styles = new CssToInlineStyles();

		return $css_to_inline_styles->convert(
			sprintf(
				"%s\n%s\n%s",
				$template->get_header( $this->email->get_header_content() ),
				$template->get_body( $body ),
				$template->get_footer( $this->email->get_footer_content() )
			),
			$template->get_styles()
		);
	}

	/**
	 * Sets the HTML for the email (body).
	 *
	 * @since 4.7.3
	 *
	 * @param string $body The HTML for the email (body).
	 * @return void
	 */
	public function set_body( $body ) {
		$this->body = $body;
	}

	/**
	 * Sends an email.
	 *
	 * @since 4.7.3
	 *
	 * @return bool
	 */
	public function send() {
		$this->send_before();

		$sent = wp_mail(
			$this->get_to(),
			$this->get_subject(),
			$this->get_body(),
			$this->get_headers()
		);

		$this->send_after();

		return $sent;
	}

	/**
	 * Returns the email address used to send emails.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_from_address() {
		/** @var string $default */
		$default = get_site_option( 'admin_email', '' );

		/** @var string $address */
		$address = simpay_get_setting( 'email_from_address', $default );

		return $address;
	}

	/**
	 * Returns the name used to send emails.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_from_name() {
		/** @var string $default */
		$default = get_site_option( 'blogname', '' );

		/** @var string $name */
		$name = simpay_get_setting( 'email_from_name', $default );

		return wp_specialchars_decode( $name, ENT_QUOTES );
	}

	/**
	 * Returns the content type used to send HTML emails.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get_content_type() {
		return 'text/html';
	}

	/**
	 * Updates how the email will be sent.
	 *
	 * @since 4.7.3
	 *
	 * @return void
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Removes our plugin-specific email changes after an email is sent.
	 *
	 * @since 4.7.3
	 *
	 * @return void
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Parses content and replaces Smart Tags with their values.
	 *
	 * @since 4.7.3
	 *
	 * @param string $content Content to parse and replace Smart Tags in.
	 * @return string
	 */
	private function parse_smart_tags( $content ) {
		return Payment_Confirmation\Template_Tags\parse_content(
			$content,
			$this->get_data()
		);
	}

}
