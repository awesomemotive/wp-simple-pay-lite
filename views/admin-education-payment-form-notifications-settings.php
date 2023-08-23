<?php
/**
 * Admin: Payment form payment notification education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.9
 *
 * @var string $settings_url Global settings URL.
 */

?>

<div
	class="simpay-notice simpay-form-settings-notice"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-simpay-form-settings-notifications-education' ) ); ?>"
	data-id="simpay-form-settings-notifications-education"
	data-lifespan="<?php echo esc_attr( DAY_IN_SECONDS * 720 ); // @phpstan-ignore-line ?>"
>
	<strong style="display: flex; align-items: center;">
		<svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; margin-right: 5px;" viewBox="0 0 20 20" fill="#635aff">
			<path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
		</svg>
		<span><?php esc_html_e( 'Custom Email Notification Messages', 'stripe' ); ?></span>
	</strong>

	<p>
	<?php
	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening <a> tag, do not translate. %2$s Closing </a> tag, do not translate. */
			__(
				'Per-form email notification messages allow you to easily create custom payment experiences. Enter messages below to use instead of the %1$sglobal email notification settings%2$s.',
				'stripe'
			),
			'<a href="' . $settings_url . '" target="_blank" rel="noopener noreferrer">',
			'</a>'
		),
		array(
			'a'    => array(
				'href'   => true,
				'rel'    => true,
				'target' => true,
				'class'  => true,
			),
			'span' => array(
				'class' => true,
			),
		)
	);
	?>
	</p>

	<button type="button" class="button button-link simpay-notice-dismiss">
		&times;
		<span class="screen-reader-text">
			<?php esc_html_e( 'Dismiss', 'stripe' ); ?>
		</span>
	</button>
</div>
