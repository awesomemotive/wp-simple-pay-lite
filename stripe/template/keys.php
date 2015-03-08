<div class="sc-admin-hidden" id="keys-settings-tab">
	<form method="post" action="#license-settings">
		<?php
			//$settings = new MM_Settings( 'sc_settings' );
		global $settings;
		?>
		
		<div>	
			<p>TODO: Toggle</p>
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

		<?php $settings->ajax_save_button( 'test', 'Click to Save!' ); ?>
	</form>
</div>
