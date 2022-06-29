<?php
/**
 * Admin: Form template preview
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.0
 *
 * @var array<mixed> $template Template data.
 * @var string       $new_url URL to the new template.
 */

/** @var string $template_name */
$template_name = isset( $template['name'] ) ? $template['name'] : '';
?>

<div class="notice notice-info is-dismissible">
	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Payment form template name. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
				__(
					'You are creating a payment form using the "%1$s" template. %2$sUse a different template &rarr;%3$s',
					'stripe'
				),
				'<strong>' . esc_html( $template_name ) . '</strong>',
				'<a href="' . esc_url( $new_url ) . '">',
				'</a>'
			),
			array(
				'strong' => array(),
				'a'      => array(
					'href' => true,
				),
			)
		);
		?>
	</p>
</div>
