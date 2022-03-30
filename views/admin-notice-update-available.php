<?php
/**
 * Admin notice: Update available notice
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 *
 * @var array<mixed> $data Notice data.
 */

/** @var string $update_url  */
$update_url = $data['update_url'];
?>

<div class="simpay-admin-notice-update-plugin">

	<div class="simpay-admin-notice-update-plugin__copy">
		<img
			src="<?php echo esc_url( SIMPLE_PAY_INC_URL . '/core/assets/images/wp-simple-pay.svg' ); // @phpstan-ignore-line ?>"
			alt="WP Simple Pay"
		/>

		<div>
			<strong>
				<?php
				esc_html_e(
					'Did you know you\'re running an outdated version of WP Simple Pay?',
					'stripe'
				);
				?>
			</strong><br />

			<?php
			esc_html_e(
				'Update WP Simple Pay now for improved security and access to the latest features.',
				'stripe'
			);
			?>
		</div>
	</div>

	<a href="<?php echo esc_url( $update_url ); ?>" class="button button-primary">
		<?php esc_html_e( 'Update Now', 'stripe' ); ?>
	</a>
</div>
