<?php
/**
 * Admin: Payment form instant payouts education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 *
 * @var string $enroll_url URL to enroll in instant payouts.
 */

use SimplePay\Core\Utils;

?>

<div
	class="simpay-notice simpay-instant-payouts-notice"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-simpay-form-settings-instant-payouts-education' ) ); ?>"
	data-id="simpay-form-settings-instant-payouts-education"
	data-lifespan="<?php echo esc_attr( DAY_IN_SECONDS * 180 ); // @phpstan-ignore-line ?>"
>
	<strong style="display: flex; align-items: center;">
		<svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; margin-right: 5px;" viewBox="0 0 20 20" fill="#635aff">
			<path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
		</svg>
		<span><?php esc_html_e( 'Instant Payouts', 'stripe' ); ?></span>
	</strong>

	<p>
	<?php
	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening <a> tag, do not translate. %2$s Closing </a> tag, do not translate. */
			__(
				'%1$sEnable Stripe Instant Payouts%2$s to instantly send funds to your debit card or bank account.',
				'stripe'
			),
			'<a href="' . esc_url( $enroll_url ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
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
