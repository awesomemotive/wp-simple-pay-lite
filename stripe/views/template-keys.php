<div class="tab-content" id="keys-settings-tab">
	<form method="post" action="#license-settings" id="license-settings">
		<?php
			global $sc_options;
		?>
		
		<div>
			<label for="<?php echo $sc_options->get_setting_id( 'enable_live_key' ); ?>">Transaction Mode</label>
			<?php $sc_options->toggle_control( 'enable_live_key', array( __( 'Test', 'sc' ), __( 'Live', 'sc' ) ) ); ?>
		</div>
		
		<div>
			<label for="<?php echo $sc_options->get_setting_id( 'test_secret_key' ); ?>">Test Secret Key</label>
			<?php $sc_options->textbox( 'test_secret_key', 'regular-text' ); ?>
		</div>
		
		<div>
			<label for="<?php echo $sc_options->get_setting_id( 'test_publish_key' ); ?>">Test Publish Key</label>
			<?php $sc_options->textbox( 'test_publish_key', 'regular-text' ); ?>
		</div>
		
		<div>
			<label for="<?php echo $sc_options->get_setting_id( 'live_secret_key' ); ?>">Live Secret Key</label>
			<?php $sc_options->textbox( 'live_secret_key', 'regular-text' ); ?>
		</div>
		
		<div>
			<label for="<?php echo $sc_options->get_setting_id( 'live_publish_key' ); ?>">Live Publish Key</label>
			<?php $sc_options->textbox( 'live_publish_key', 'regular-text' ); ?>
		</div>

		<?php $sc_options->ajax_save_button( 'Save Settings' ); ?>
	</form>
</div>
