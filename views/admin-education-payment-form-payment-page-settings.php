<?php
/**
 * Admin: Payment form payment page education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.0
 *
 * @var string $features_url URL to features page on website.
 */

use SimplePay\Core\Utils;
?>

<div
	class="simpay-notice simpay-form-settings-notice"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-simpay-form-settings-payment-page-education' ) ); ?>"
	data-id="simpay-form-settings-payment-page-education"
	data-lifespan="<?php echo esc_attr( YEAR_IN_SECONDS ); // @phpstan-ignore-line ?>"
>
	<strong style="display: flex; align-items: center;">
		<svg style="width: 18px; height: 18px; margin-right: 5px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			<path stroke-linecap="round" stroke-linejoin="round" stroke="#635aff" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
		</svg>
		<span><?php esc_html_e( 'Want to improve your form conversions?', 'stripe' ); ?></span>
	</strong>

	<p>
	<?php
	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening <a> tag, do not translate. %2$s Closing </a> tag, do not translate. */
			__(
				'%1$sPayment Pages%2$s allow you to create completely custom “distraction-free” payment form landing pages to boost conversions (without writing any code).',
				'stripe'
			),
			'<a href="' . esc_url( $features_url ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
			Utils\get_external_link_markup() . '</a>'
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
