<?php
/**
 * Promos: License Upgrade
 *
 * @var bool $is_lite If the current installation is Lite.
 * @var int $license_id License price ID.
 * @var string $license_type License type.
 * @var string $upgrade_url Upgrade URL.
 *
 * @package SimplePay\Core\Admin\Notices\Promos
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.2.0
 */
?>

<div
	id="simpay-notice-license-upgrade"
	class="simpay-admin-notice-top-of-page simpay-notice"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-simpay-license-upgrade' ) ); ?>"
	data-id="simpay-license-upgrade"
	data-lifespan="<?php echo esc_attr( DAY_IN_SECONDS * 90 ); ?>"
>
	<?php
	if ( true === $is_lite ) :
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'You\'re using WP Simple Pay Lite. To unlock more features consider %1$supgrading to Pro%2$s.',
					'stripe'
				),
				'<a href="' . esc_url( $upgrade_url ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			array(
				'a' => array(
					'href'   => true,
					'target' => true,
					'rel'    => true,
				),
			)
		);
	else :
		switch ( $license_id ) {
			case 1:
				/* translators: %1$s License type. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
				$message = __(
					'You\'re using WP Simple Pay with a %1$s license. To unlock more features consider %2$supgrading now%3$s.',
					'stripe'
				);
				break;
			// Currently does not show.
			default:
				/* translators: %1$s License type. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
				$message = __(
					'You are using WP Simple Pay with a %1$s license. Consider %2$supgrading%3$s to get more sites.',
					'stripe'
				);
		}

		echo wp_kses(
			sprintf(
				$message,
				$license_type,
				'<a href="' . esc_url( $upgrade_url ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			array(
				'a' => array(
					'href'   => true,
					'target' => true,
					'rel'    => true,
				),
			)
		);
	endif;
	?>

	<button type="button" class="button-link simpay-notice-dismiss">
		&times;
	</button>
</div>
