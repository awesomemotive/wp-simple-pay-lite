<?php

/**
 * Show notice after plugin upgrade to warn about API Key changes (1.5.4)
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $base_class;

?>

<div id="sc-api-notice" class="error">
	<p>
		<?php
			_e( 'This note is for WP Simple Pay plugin users that have specified multiple Stripe accounts (API keys) throughout a single WordPress site.', 'stripe' );
			echo '<br>';
			printf( __( 'If you are using this functionality please view the new <a target="_blank" href="%s">documentation</a> ASAP.', 'stripe' ), 'https://wpsimplepay.com/docs/misc/multiple-stripe-api-key-support/' );
		?>
	</p>
	<p>
		<a href="<?php echo esc_url( add_query_arg( 'sc-dismiss-api-nag', 1 ) ); ?>" class="button-secondary"><?php _e( 'Hide this', 'stripe' ); ?></a>
	</p>
</div>
