<?php
/**
 * Admin: Product education dashboard widget report
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 *
 * @var bool                                        $dismissed_recommended_plugin
 *                                                  If the recommended plugin has been dismissed.
 * @var array<string, string|array<string, string>> $recommended_plugin Recommended plugin data.
 */
?>

<div id="simpay-admin-dashboard-widget-report" class="simpay-admin-dashboard-widget-report"></div>


<?php
if (
	false === $dismissed_recommended_plugin &&
	! empty( $recommended_plugin )
) :
	/** @var string $plugin_name */
	$plugin_name = $recommended_plugin['name'];

	/** @var string $install_url */
	$install_url = $recommended_plugin['install_url'];

	/** @var string $more_url */
	$more_url = $recommended_plugin['more'];
?>
<div
	class="simpay-notice simpay-admin-dashboard-widget-report-recommended-plugin"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-simpay-dashboard-widget-recommended-plugin' ) ); ?>"
	data-id="simpay-dashboard-widget-recommended-plugin"
>
	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: Plugin name. */
				__( 'Recommended Plugin: %s', 'stripe' ),
				'<strong>' . esc_html( $plugin_name ) . '</strong>'
			),
			array(
				'strong' => array(),
			)
		);
		?>

		&nbsp;&ndash;&nbsp;

		<?php if ( current_user_can( 'install_plugins' ) ) :?>
			<a href="<?php echo esc_url( $install_url ); ?>">
				<?php esc_html_e( 'Install', 'stripe' ); ?></a> &vert;
		<?php endif; ?>

		<a href="<?php echo esc_url( $more_url ); ?>" target="_blank">
			<?php esc_html_e( 'Learn More', 'stripe' ); ?>
		</a>
	</p>

	<button type="button" class="button-link simpay-notice-dismiss">
		&times;
	</button>
</div>
<?php endif; ?>
