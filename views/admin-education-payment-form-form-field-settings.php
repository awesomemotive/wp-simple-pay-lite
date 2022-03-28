<?php
/**
 * Admin: Payment form form fields education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 *
 * @var string                              $upgrade_url The upgrade URL.
 * @var string                              $upgrade_text The upgrade button text.
 * @var string                              $upgrade_subtext The upgrade button subtext.
 * @var array<string, array<array<string>>> $field_groups List of grouped fields.
 */

?>

<div class="simpay-teaser-float">
	<div class="simpay-teaser-float__card">
		<h2>
			<?php esc_html_e( '📝 Custom Fields + Custom Data', 'stripe' ); ?>
		</h2>

		<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Opening <strong> tag, do not translate. %2$s Closing </strong> tag, do not translate. */
					__(
						'Collect as little or as much data as you need on your payment forms. Add a variety of field types to capture more data for each payment record: %1$snumbers, dates, checkboxes, drop-downs and more%2$s.',
						'stripe'
					),
					'<strong>',
					'</strong>'
				),
				array(
					'strong' => array(),
				)
			);
			?>
		</p>

		<select style="display: block; margin: 20px auto;">
			<option value=""><?php esc_html_e( 'See available custom fields&hellip;', 'stripe' ); ?></option>
			<?php foreach ( $field_groups as $group => $options ) : ?>
				<optgroup label="<?php echo esc_attr( $group ); ?>">
					<?php foreach ( $options as $option ) : ?>
						<option>
							<?php echo esc_html( $option['label'] ); ?>
						</option>
					<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
		</select>

		<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-large simpay-upgrade-btn" target="_blank" rel="noopener noreferrer">
			<?php echo esc_html( $upgrade_text ); ?>
		</a>

		<?php if ( ! empty( $upgrade_subtext ) ) : ?>
		<div class="simpay-upgrade-btn-subtext">
			<?php echo esc_html( $upgrade_subtext ); ?>
		</div>
		<?php endif; ?>
	</div>
</div>
