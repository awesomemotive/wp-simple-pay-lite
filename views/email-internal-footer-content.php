<?php
/**
 * Internal email footer content
 *
 * @since 4.7.3
 *
 * @package SimplePay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 *
 * @var string $credit
 * @var array<string, string|array<string, string>> $blurb
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
if ( ! empty( $blurb ) ) :
	/** @var string $image */
	$image = $blurb['image'];

	/** @var string $title */
	$title = $blurb['title'];

	/** @var string $content */
	$content = $blurb['content'];

	/** @var array<array<string, string>> $links */
	$links = $blurb['links'];
	?>
<table class="footer__blurb">
	<tbody>
		<tr>
			<td>
				<img
					src="<?php echo esc_url( $image ); ?>"
					alt="<?php echo esc_attr( $title ); ?>"
				/>
				<h3><?php echo esc_html( $title ); ?></h3>
				<?php echo wp_kses_post( wpautop( $content ) ); ?>

				<?php foreach ( $links as $link ) : ?>
					<table class="button small centered">
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

<table class="footer__credit">
	<tbody>
		<tr>
			<td>
				<?php echo wp_kses_post( $credit ); ?>
			</td>
		</tr>
	</tbody>
</table>
