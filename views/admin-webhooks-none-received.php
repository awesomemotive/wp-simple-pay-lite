<?php
/**
 * Admin: No webhooks have been received notice
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 *
 * @var string $docs_url URL to webhook documentation.
 * @var string $verify_url URL to mark webhooks as verified.
 * @var string $dismiss_url URL To dismiss this notice permanently.
 */

?>

<div class="notice inline notice-error">
	<p>
		<strong>
		<?php esc_html_e(
			'WP Simple Pay may not be functioning correctly.',
			'simple-pay'
		);
		?>
		</strong>
	</p>

	<p>
		<?php
		$mode = simpay_is_test_mode()
			? __( 'test mode', 'simple-pay' )
			: __( 'live mode', 'simple-pay' );

		echo wp_kses(
			sprintf(
				__(
					'Expected %1$s webhook events have not been received. Please ensure you have properly configured your %2$s webhook endpoint in Stripe and signing secrets below to avoid interruption of functionality.',
					'simple-pay'
				),
				sprintf(
					'<span class="simpay-badge simpay-badge--%s" style="font-size: 10px; font-weight: 700; text-transform: uppercase; margin-top: -3px; vertical-align: middle">' . $mode . '</span>',
					simpay_is_test_mode() ? 'yellow' : 'green'
				),
				'<strong>' . $mode . '</strong>'
			),
			array(
				'strong' => array(),
				'span'   => array(
					'class' => true,
					'style' => true,
				)
			)
		);
		?>
	</p>

	<p style="display: flex; align-items: center;">
		<a
			href="<?php echo esc_url( $docs_url ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			class="simpay-external-link button button-secondary"
		>
			<?php esc_html_e( 'Learn More', 'simple-pay' ); ?>
		</a>

		<a href="<?php echo esc_url( $verify_url ); ?>" style="margin-left: 5px;">
			<?php esc_html_e( 'I have configured webhooks in Stripe', 'simple-pay' ); ?>
		</a>

		<a href="<?php echo esc_url( $dismiss_url ); ?>" style="margin-left: auto;">
			<?php esc_html_e( 'Do not show again', 'simple-pay' ); ?>
		</a>
	</p>
</div>
