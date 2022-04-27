<?php
/**
 * Admin notice: License missing
 *
 * @package SimplePay\Core\Admin\Notices\Promos
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 *
 * @var array<string> $data Notice data.
 */
?>

<p>
	<?php
	echo wp_kses(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__(
				'Please %1$senter and activate%2$s your license key for WP Simple Pay Pro to enable automatic updates.',
				'stripe'
			),
			'<a href="' . esc_url( $data['license_url'] ) . '">',
			'</a>'
		),
		array(
			'a' => array(
				'href' => true,
			)
		)
	);
	?>
</p>
