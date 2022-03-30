<?php
/**
 * Admin: "About Us" page
 *
 * "Getting Started" tab.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 *
 * @var string $upgrade_url The upgrade URL.
 * @var string $upgrade_text The upgrade button text.
 * @var bool   $is_lite If the current license is Lite.
 */

?>

<div class="simpay-card">

	<figure>
		<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL ); // @phpstan-ignore-line ?>/core/assets/images/about/getting-started.svg" alt="" style="max-width: 300px;" />
	</figure>

	<h3>
		<?php esc_html_e( 'Creating Your First Payment Form', 'stripe' ); ?>
	</h3>

	<p>
		<?php esc_html_e( 'Want to get started creating your first payment form with WP Simple Pay? First, if you haven’t already done so, you’ll want to sign up for a Stripe account and connect it to WP Simple Pay.', 'stripe' ); ?>
	</p>

	<p>
		<a href="https://docs.wpsimplepay.com/articles/stripe-setup/?utm_source=WordPress&utm_campaign=pro-plugin&utm_medium=about-us" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Connecting your Stripe Account', 'stripe' ); ?>
		</a>
	</p>

	<p>
		<?php esc_html_e( 'To create a new form, click on the Add New link in the left-hand menu. Then simply follow these step by step instructions to publish your first payment form in minutes.', 'stripe' ); ?>
	</p>

	<p>
		<a href="https://docs.wpsimplepay.com/articles/first-payment-form/?utm_source=WordPress&utm_campaign=pro-plugin&utm_medium=about-us" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Creating Your First Payment Form', 'stripe' ); ?>
		</a>
	</p>

	<p>
		<?php esc_html_e( 'We highly recommend staying in test mode until your payment forms are ready and you’ve looked through test transactions in Stripe.', 'stripe' ); ?>
	</p>

	<p>
		<a href="https://docs.wpsimplepay.com/articles/using-test-mode/?utm_source=WordPress&utm_campaign=pro-plugin&utm_medium=about-us" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Using Test Mode', 'stripe' ); ?>
		</a>
	</p>

</div>

<?php
if ( true === $is_lite ) :
	include_once SIMPLE_PAY_DIR . 'views/admin-settings-upgrade.php'; // @phpstan-ignore-line
endif;
?>

<div class="simpay-card simpay-doc-suggestions">

	<div class="simpay-doc-suggestion">
		<span class="dashicons dashicons-admin-site-alt"></span>

		<h3>
			<?php esc_html_e( 'Accept Donations', 'stripe' ); ?>
		</h3>

		<p>
			<?php
			esc_html_e(
				'Easily fundraise or accept donations online via 135+ supported currencies. Offer one-time or recurring donations of fixed or user-defined amounts.',
				'stripe'
			);
			?>
		</p>

		<a href="<?php echo esc_url( simpay_docs_link( 'Accept Donations', 'accepting-donations-form-setup', 'about-us', true ) ); ?>" class="button button-primary button-large" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Read Documentation', 'stripe' ); ?>
		</a>
	</div>

	<div class="simpay-doc-suggestion">
		<span class="dashicons dashicons-update-alt"></span>

		<h3>
			<?php esc_html_e( 'Sell Recurring Services', 'stripe' ); ?>
		</h3>

		<p>
			<?php
			esc_html_e(
				'Collect payments indefinitely, bill users through installment plans, collect additional setup fees, and offer free trials for recurring services.',
				'stripe'
			);
			?>
		</p>

		<a href="<?php echo esc_url( simpay_docs_link( 'Sell Recurring Services', 'sell-recurring-services-form-set-up', 'about-us', true ) ); ?>" class="button button-primary button-large" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Read Documentation', 'stripe' ); ?>
		</a>
	</div>

	<div class="simpay-doc-suggestion">
		<span class="dashicons dashicons-welcome-write-blog"></span>

		<h3>
			<?php esc_html_e( 'Reconcile Invoices', 'stripe' ); ?>
		</h3>

		<p>
			<?php
			esc_html_e(
				'Collect additional custom data on your payment forms such as an Invoice ID to reconcile invoices against your own invoicing system.',
				'stripe'
			);
			?>
		</p>

		<a href="<?php echo esc_url( simpay_docs_link( 'Reconcile Invoices', 'invoice-payment-form-set-up', 'about-us', true ) ); ?>" class="button button-primary button-large" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Read Documentation', 'stripe' ); ?>
		</a>
	</div>

</div>
