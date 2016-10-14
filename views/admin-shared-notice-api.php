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

<style>
	#sc-api-notice .button-secondary {
		margin-left: 15px;
	}
</style>

<div id="sc-api-notice" class="error">
	<p>
		<?php
			_e( 'This is a message to inform about API changes.', 'stripe' );
		?>
		<a href="<?php echo esc_url( add_query_arg( 'sc-dismiss-api-nag', 1 ) ); ?>" class="button-secondary"><?php _e( 'Hide this', 'stripe' ); ?></a>
	</p>
</div>
