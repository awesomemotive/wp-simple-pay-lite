
<?php
/**
 * Admin: Plugin coupon education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 *
 * @var \SimplePay\Core\License\License $license Plugin license.
 */

?>

<div class="simpay-upgrade-modal" style="display: none;">
	<div class="simpay-upgrade-modal__content">
		<span class="dashicons dashicons-lock"></span>
		<h3 class="simpay-upgrade-modal__title"></h3>
		<p class="simpay-upgrade-modal__description"></p>

		<a href="" target="_blank" rel="noopener noreferrer" class="simpay-upgrade-modal__upgrade-url button button-primary button-large">
			<?php
			if ( $license->is_lite() ) :
				esc_html_e( 'Upgrade to Pro', 'stripe' );
			else :
				esc_html_e( 'Upgrade Now', 'stripe' );
			endif;
			?>
		</a>

		<a class="simpay-upgrade-modal__upgrade-purchased-url" href="" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Already purchased?', 'stripe' ); ?>
		</a>

		<p class="simpay-upgrade-modal__discount">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img" aria-hidden="true" focusable="false">
				<path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path>
			</svg>

			<?php
			if ( $license->is_lite() ) :
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening <strong> tag, do not translate. %2$s Closing </strong> tag, do not translate. %3$s Opening <u> tag, do not translate. %4$s Closing </u> tag, do not translate. %5$s Opening anchor tag, do not translate. %6$s Closing anchor tag, do not translate. */
						__(
							'%1$sBonus:%2$s WP Simple Pay Lite users get %3$s50%% off%4$s regular price, automatically applied at checkout. %5$sUpgrade to Pro →%6$s',
							'stripe'
						),
						'<strong>',
						'</strong>',
						'<u>',
						'</u>',
						'<a href="" class="simpay-upgrade-modal__upgrade-url" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
					array(
						'u'      => array(),
						'strong' => array(),
						'a'      => array(
							'href'   => true,
							'target' => true,
							'rel'    => true,
							'class'  => true,
						),
					)
				);
			else :
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening <strong> tag, do not translate. %2$s Closing </strong> tag, do not translate. %3$s Opening <u> tag, do not translate. %4$s Closing </u> tag, do not translate. %5$s Opening anchor tag, do not translate. %6$s Closing anchor tag, do not translate. */
						__(
							'%1$sBonus:%2$s WP Simple Pay Pro users get %3$s50%% off%4$s upgrade pricing, automatically applied at checkout. %5$sSee upgrade options →%6$s',
							'stripe'
						),
						'<strong>',
						'</strong>',
						'<u>',
						'</u>',
						'<a href="" class="simpay-upgrade-modal__upgrade-url" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
					array(
						'u'      => array(),
						'strong' => array(),
						'a'      => array(
							'href'   => true,
							'target' => true,
							'rel'    => true,
							'class'  => true,
						),
					)
				);
			endif;
			?>
		</p>
	</div>
</div>
