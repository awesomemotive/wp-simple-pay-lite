<?php
/**
 * Admin: Plugin instant payout settings
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 *
 * @var string $docs_url URL to Instant Payouts documentation.
 * @var string $enroll_url URL to Instant Payouts enrollment.
 */

use SimplePay\Core\Utils;

?>

<div class="simpay-landing-zone">

	<h2 class="simpay-landing-zone__title">
		<?php
		esc_html_e(
			'💰 Send Funds Instantly',
			'stripe'
		);
		?>
	</h2>

	<p class="simpay-landing-zone__subtitle">
		<?php
		esc_html_e(
			'With Stripe\'s Instant Payouts, you can transfer funds immediately to your debit card or bank account. You can request Instant Payouts at any time, including weekends and holidays, and funds typically appear in your bank account within 30 minutes or less.',
			'stripe'
		);
		?>
	</p>

	<section>
		<a href="<?php echo esc_url( $enroll_url ); ?>" class="button button-primary button-large simpay-upgrade-btn" target="_blank" rel="noopener noreferrer">
			<?php
			esc_html_e(
				'Enable Stripe Instant Payouts',
				'stripe'
			);
			?>
		</a>

		<div style="margin-top: 18px;">
			<?php
			esc_html_e(
				'Subject to approval and currently available in the United States, Canada, United Kingdom, and Singapore.',
				'stripe'
			);
			?>
			<br/>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Opening <a> tag, do not translate. %2$s Closing </a> tag, do not translate. */
					__(
						'%1$sRead More%2$s',
						'stripe'
					),
					'<a href="' . esc_url( $docs_url ) .'" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
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
		</div>
	</section>

</div>
