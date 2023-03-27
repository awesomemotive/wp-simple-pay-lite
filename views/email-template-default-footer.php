<?php
/**
 * Default email footer template.
 *
 * @since 4.7.3
 *
 * @package SimplePay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 *
 * @var string $content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

					</td>
				</tr>
				<tr>
					<td align="center" valign="top" class="footer">
						<?php echo wp_kses_post( $content ); ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
