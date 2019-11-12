<?php use function SimplePay\Core\Admin\Notices\Promos\bfcm_is_promo_active;

// Get the upgrade URL
$upgrade_url = simpay_pro_upgrade_url( 'under-box-promo' );

// Adjust the upgrade URL if there's an active promotion
if ( true === bfcm_is_promo_active() ) {
	$utm_args    = array(
		'utm_source'   => 'form-settings',
		'utm_medium'   => 'wp-admin',
		'utm_campaign' => 'bfcm2019',
		'utm_content'  => 'upgrade-promo',
	);
	$upgrade_url = esc_url( add_query_arg( $utm_args, 'https://wpsimplepay.com/lite-vs-pro/' ) );
}
?>

<p>
	<a href="<?php echo $upgrade_url; ?>"
	   class="simpay-upgrade-btn simpay-upgrade-btn-large" target="_blank">
		<?php _e( 'Click here to Upgrade', 'stripe' ); ?>
	</a>
</p>

</div>
