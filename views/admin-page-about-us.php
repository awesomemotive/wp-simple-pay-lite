<?php
/**
 * Admin: "About Us" page
 *
 * "About Us" tab.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 *
 * @var string                          $active_tab Active tab slug.
 * @var array<string, string>           $tabs Available tabs data.
 * @var string                          $base_url Base URL for the admin page.
 * @var \SimplePay\Core\License\License $license Plugin license.
 */

?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'About Us', 'simple-pay' ); ?>
	</h1>
	<hr class="wp-header-end">

	<h2 class="nav-tab-wrapper simpay-nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_id => $tab_title ) : ?>
		<a href="<?php echo esc_url( add_query_arg( 'view', $tab_id, $base_url ) ); ?>" class="nav-tab <?php echo esc_attr( $tab_id === $active_tab ? 'nav-tab-active' : '' ); ?>">
			<?php echo esc_html( $tab_title ); ?>
		</a>
		<?php endforeach; ?>

		<?php if ( true === $license->is_lite() ) : ?>
			<a href="<?php echo esc_url( simpay_pro_upgrade_url( 'about-us' ) ); ?>" class="nav-tab" rel="noopener noreferrer" target="_blank">
				<?php esc_html_e( 'Lite vs. Pro', 'simple-pay' ); ?>
			</a>
		<?php endif; ?>
	</h2>

	<div style="margin-top: 20px;">
		<?php
		include_once sprintf(
			SIMPLE_PAY_DIR . '/views/admin-page-about-us-%s.php', // @phpstan-ignore-line
			$active_tab
		);
		?>
	</div>

</div>
