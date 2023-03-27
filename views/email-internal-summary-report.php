<?php
/**
 * Emails: Internal summary report
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 *
 * @var array<int, array<string, mixed>> $stats Stats overview.
 * @var array<int, array<string, mixed>> $top_forms Top forms.
 * @var string $site_domain Site domain.
 * @var string $start Start date.
 * @var string $end End date.
 */

?>

<div class="summary-report">
	<h1>
		<?php esc_html_e( 'Hi there,', 'stripe' ); ?>
	</h1>

	<p class="summary-report__meta">
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Site domain. %2$s Start date. %3$s End date. */
				__(
					'Here\'s an overview of your payment form activity on %1$s from %2$s &mdash; %3$s',
					'stripe'
				),
				'<strong>' . make_clickable( $site_domain ) . '</strong>',
				'<strong>' . $start . '</strong>',
				'<strong>' . $end . '</strong>'
			),
			array(
				'strong' => array(),
			)
		);
		?>
	</p>

	<table class="summary-report__stats">
		<tr>
			<?php
			foreach ( $stats as $k => $stat ) :
				/** @var string $icon */
				$icon = $stat['icon'];

				/** @var string $label */
				$label = $stat['label'];

				/** @var array<string, string|int> $value */
				$value = $stat['value'];
				/** @var string $formatted_value */
				$formatted_value = $value['rendered'];

				/** @var int $delta */
				$delta = $stat['delta'];
				?>
				<td class="summary-report__stat">
					<div class="summary-report__stat-icon">
						<?php echo esc_html( $icon ); ?>
					</div>

					<h2 class="summary-report__stat-label">
						<?php echo esc_html( $label ); ?>
					</h2>

					<div class="summary-report__stat-value">
						<?php echo esc_html( $formatted_value ); ?>
					</div>

					<?php if ( 0 !== $delta ) : ?>
					<div class="summary-report__stat-delta">
						<strong class="<?php echo esc_attr( $delta < 0 ? 'is-negative' : 'is-positive' ); ?>">
							<?php echo esc_html( $delta < 0 ? '&darr;' : '&uarr;' ); ?>
							<?php echo esc_html( (string) absint( $delta ) ); ?>%
						</strong>
						<?php esc_html_e( 'vs. previous period', 'stripe' ); ?>
					</div>
					<?php endif; ?>
				</td>

				<?php
				// After the second column, start a new row.
				if ( 1 === $k ) :
					?>
					</tr>
					<tr>
					<?php
				endif;
				endforeach;
			?>
		</tr>
	</table>

	<h3>
		<?php esc_html_e( 'Top Forms', 'stripe' ); ?>
	</h3>

	<table class="summary-report__top-forms">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Payment Form', 'stripe' ); ?></th>
				<th><?php esc_html_e( 'Gross Volume', 'stripe' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $top_forms ) ) : ?>
				<tr>
					<td colspan="2">
						<?php esc_html_e( 'N/A.', 'stripe' ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php
				foreach ( $top_forms as $form ) :
					/** @var string $href */
					$href = $form['href'];

					/** @var string $title */
					$title = $form['title'];

					/** @var array<string, string|int> $gross_volume */
					$gross_volume = $form['gross_volume'];
					/** @var string $formatted_gross_volume */
					$formatted_gross_volume = $gross_volume['rendered'];

					/** @var int $delta */
					$delta = $form['delta'];
					?>
				<tr>
					<td>
						<a href="<?php echo esc_url( $href ); ?>">
						<?php echo esc_html( $title ); ?>
						</a>
					</td>
					<td>
						<strong>
						<?php echo esc_html( $formatted_gross_volume ); ?>
						</strong>

					<?php if ( 0 !== $delta ) : ?>
						<span class="<?php echo esc_attr( $delta < 0 ? 'is-negative' : 'is-positive' ); ?>">
							<?php echo esc_html( $delta < 0 ? '&darr;' : '&uarr;' ); ?>
							<?php echo esc_html( (string) absint( $delta ) ); ?>%
						</span>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>
