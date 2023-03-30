<?php
/**
 * Internal email header content
 *
 * @since 4.7.3
 *
 * @package SimplePay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 *
 * @var string $image The image HTML.
 * @var array<string, string|array<string, string>> $alert An array of alert data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<table class="header__image">
	<tbody>
		<tr>
			<td>
				<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</td>
		</tr>
	</tbody>
</table>

<?php
if ( ! empty( $alert ) ) :
	/** @var string $title */
	$title = $alert['title'];

	/** @var string $content */
	$content = $alert['content'];

	/** @var array<array<string, string>> $links */
	$links = $alert['links'];
	?>
<table class="header__alert">
	<tbody>
		<tr>
			<td>
				<h2><?php echo esc_html( $title ); ?></h2>
				<?php echo wp_kses_post( wpautop( $content ) ); ?>

				<?php foreach ( $links as $link ) : ?>
					<table class="button red">
						<tr>
							<td>
								<table>
									<tr>
										<td>
											<a href="<?php echo esc_url( $link['url'] ); ?>">
												<?php echo esc_html( $link['text'] ); ?>
											</a>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				<?php endforeach; ?>
			</td>
		</tr>
	</tbody>
</table>
<?php endif; ?>
