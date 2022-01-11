<?php
/**
 * Admin: "About Us" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 *
 * @var array<string, array<string>> $am_plugins List of Awesome Motive plugins.
 * @var bool                         $can_install_plugins If the current user can install plugins.
 */

?>

<div class="simpay-card">

	<figure>
		<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL ); // @phpstan-ignore-line ?>/core/assets/images/about/team.png" alt="<?php esc_attr_e( 'The WP Simple Pay Team photo', 'simple-pay' ); ?>">
		<figcaption>
			<?php esc_html_e( 'The WP Simple Pay Team', 'simple-pay' ); ?><br>
		</figcaption>
	</figure>

	<h3>
		<?php esc_html_e( 'Hello and welcome to WP Simple Pay, the #1 Stripe payments plugin for WordPress. At WP Simple Pay, we build software that helps you create secure, conversion-optimized payment forms for your website in minutes.', 'simple-pay' ); ?>
	</h3>

	<p>
		<?php esc_html_e( 'Over the years, we found that many website owners just needed a simple, reliable way to accept one-time and recurring payments without setting up a shopping cart or hiring a developer.', 'simple-pay' ); ?>
	</p>

	<p>
		<?php
		printf(
			wp_kses(
				/* translators: %1$s - Stripe URL. */
				__( 'Our goal is to take the pain out of creating payment forms and make it easy. WP Simple Pay connects the best payment processor (<a href="%1$s" target="_blank" rel="noopener noreferrer">Stripe</a>) with WordPress. No other plugins are required.', 'simple-pay' ),
				array(
					'a' => array(
						'href'   => array(),
						'rel'    => array(),
						'target' => array(),
					),
				)
			),
			'https://stripe.com/'
		);
		?>
	</p>

	<p>
		<?php
		printf(
			wp_kses(
				/* translators: %1$s - WPBeginner URL. */
				__( 'WP Simple Pay is brought to you by the same team that’s behind the largest WordPress resource site, <a href="%1$s" target="_blank" rel="noopener noreferrer">WPBeginner</a>, and all of the following best-of-class software.', 'simple-pay' ),
				array(
					'a' => array(
						'href'   => array(),
						'rel'    => array(),
						'target' => array(),
					),
				)
			),
			'https://www.wpbeginner.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay'
		);
		?>
	</p>

	<p>
		<?php esc_html_e( 'Yup, we know a thing or two about building awesome products that customers love.', 'simple-pay' ); ?>
	</p>

</div>

<div class="simpay-addons">
	<?php
	foreach ( $am_plugins as $plugin => $plugin_data ) :
		/** @var array<string> $details */
		$details = $plugin_data['details'];
		?>
		<div class="simpay-addon">
			<div class="simpay-addon__details">
				<img src="<?php echo esc_url( $details['icon'] ); ?>">
				<h5 class="addon-name">
					<?php echo esc_html( $details['name'] ); ?>
				</h5>
				<p class="addon-desc">
					<?php echo wp_kses_post( $details['desc'] ); ?>
				</p>
			</div>
			<div class="simpay-addon__actions">
				<div class="status">
					<strong>
						<?php
						printf(
							/* translators: Addon status label. */
							esc_html__( 'Status: %s', 'simple-pay' ),
							'<span class="status-label ' . esc_attr( $plugin_data['status_class'] ) . '">' . wp_kses_post( $plugin_data['status_text'] ) . '</span>'
						);
						?>
					</strong>
				</div>
				<div class="action-button">
					<?php if ( $can_install_plugins ) : ?>
						<button class="<?php echo esc_attr( $plugin_data['action_class'] ); ?>" data-plugin="<?php echo esc_attr( $plugin_data['plugin_src'] ); ?>" data-type="plugin">
							<?php echo wp_kses_post( $plugin_data['action_text'] ); ?>
						</button>
					<?php else : ?>
						<a href="<?php echo esc_url( $details['wporg'] ); ?>" target="_blank" rel="noopener noreferrer" class="simpay-external-link">
							<?php esc_html_e( 'WordPress.org', 'simple-pay' ); ?>
							<span aria-hidden="true" class="dashicons dashicons-external"></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
