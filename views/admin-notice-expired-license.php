<?php
/**
 * Admin notice: License expired
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 *
 * @var array<string, string|bool> $data Notice data.
 * @var string                     $learn_more_url "Learn More" URL.
 */

/** @var string $renew_url */
$renew_url = $data['renew_url'];

/** @var string $learn_more_url */
$learn_more_url = $data['learn_more_url'];
?>

<p>
	<strong>
	<?php
	esc_html_e(
		'Your WP Simple Pay Pro license has expired!',
		'stripe'
	);
	?>
	</strong>

	<?php
	if ( true === $data['is_in_grace_period'] ) :
		echo esc_html(
			sprintf(
				/* translators: License extension date. */
				__(
					'We have extended WP Simple Pay Pro functionality until %s, at which point functionality will become limited. Renew your license to continue receiving automatic updates, technical support, and access to WP Simple Pay Pro features and functionality.',
					'stripe'
				),
				$data['grace_period_ends']
			)
		);
	else :
		esc_html_e(
			'Renew your license to continue receiving automatic updates, technical support, and access to WP Simple Pay Pro features and functionality.',
			'stripe'
		);
	endif;
	?>
</p>

<p>
	<a href="<?php echo esc_url( $renew_url ); ?>" class="button button-primary">
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Renew License for %1$s50%% Off!%2$s', 'stripe' ),
				'<strong>',
				'</strong>'
			),
			array(
				'strong' => array(),
			)
		);
		?>
	</a>

	<a href="<?php echo esc_url( $learn_more_url ); ?>" style="margin-left: 5px;">
		<?php esc_html_e( 'Learn More', 'stripe' ); ?>
	</a>
</p>
