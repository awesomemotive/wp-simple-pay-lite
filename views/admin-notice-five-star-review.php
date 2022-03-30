<?php
/**
 * Admin notice: 5 star review request
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.1
 *
 * @var array<mixed> $data Notice data.
 */

/** @var string $feedback_url */
$feedback_url = $data['feedback_url'];

?>

<div class="simpay-admin-notice-five-star-rating" data-step="1">
	<p>
		<?php esc_html_e( 'Are you enjoying WP Simple Pay?', 'stripe' ); ?>
	</p>

	<p style="display: flex; align-items: center;">
		<button type="button" class="button button-primary" data-navigate="3">
			<?php esc_html_e( 'Yes', 'stripe' ); ?>
		</button>

		<button type="button" class="button button-link" data-navigate="2" style="margin-left: 10px;">
			<?php esc_html_e( 'Not really', 'stripe' ); ?>
		</button>
	</p>
</div>

<div class="simpay-admin-notice-five-star-rating" data-step="2" style="display: none;">
	<p>
		<?php
		esc_html_e(
			'We\'re sorry to hear you aren\'t enjoying WP Simple Pay. We would love a chance to improve. Could you take a minute and let us know what we can do better?',
			'stripe'
		);
		?>
	</p>

	<p style="display: flex; align-items: center;">
		<a href="<?php echo esc_url( $feedback_url ); ?>" class="button button-primary simpay-notice-dismiss" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Give feedback', 'stripe' ); ?>
		</a>

		<button type="button" class="button button-link simpay-notice-dismiss" style="margin-left: 10px;">
			<?php esc_html_e( 'No thanks', 'stripe' ); ?>
		</button>
	</p>
</div>

<div class="simpay-admin-notice-five-star-rating" data-step="3" style="display: none;">
	<p>
		<?php
		esc_html_e(
			'That\'s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?',
			'stripe'
		);
		?>
	</p>

	<p>
		<strong>
			<?php
			echo wp_kses(
				__(
					'~ Phil Derksen<br>Founder at WP Simple Pay',
					'stripe'
				),
				array(
					'br' => array(),
				)
			);
			?>
		</strong>
	</p>

	<p style="display: flex; align-items: center;">
		<a href="https://wordpress.org/support/plugin/stripe/reviews/?filter=5#new-post" class="button button-primary simpay-notice-dismiss" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Ok, you deserve it', 'stripe' ); ?>
		</a>

		<button type="button" class="button button-link simpay-notice-dismiss" style="margin-left: 10px;">
			<?php esc_html_e( 'I already did', 'stripe' ); ?>
		</button>
	</p>
</div>
