<?php
/**
 * External email header content
 *
 * @since 4.7.3
 *
 * @package SimplePay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 *
 * @var string $image The image HTML.
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
