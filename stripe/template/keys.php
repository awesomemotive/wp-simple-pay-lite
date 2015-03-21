<div class="tab-content" id="keys-settings-tab">
	<form method="post" action="#license-settings" id="license-settings">
		<?php
			global $settings;
		?>
		
		<div>
			<label for="<?php echo $settings->get_setting_id( 'enable_live_key' ); ?>">Transaction Mode</label>
			<?php $settings->toggle_control( 'enable_live_key', array( __( 'Test', 'sc' ), __( 'Live', 'sc' ) ) ); ?>
		</div>
		
		<div>
			<label for="<?php echo $settings->get_setting_id( 'test_secret_key' ); ?>">Test Secret Key</label>
			<?php $settings->textbox( 'test_secret_key', 'regular-text' ); ?>
		</div>
		
		<div>
			<label for="<?php echo $settings->get_setting_id( 'test_publish_key' ); ?>">Test Publish Key</label>
			<?php $settings->textbox( 'test_publish_key', 'regular-text' ); ?>
		</div>
		
		<div>
			<label for="<?php echo $settings->get_setting_id( 'live_secret_key' ); ?>">Live Secret Key</label>
			<?php $settings->textbox( 'live_secret_key', 'regular-text' ); ?>
		</div>
		
		<div>
			<label for="<?php echo $settings->get_setting_id( 'live_publish_key' ); ?>">Live Publish Key</label>
			<?php $settings->textbox( 'live_publish_key', 'regular-text' ); ?>
		</div>

		<?php $settings->ajax_save_button( 'Save Settings' ); ?>
	</form>
</div>
