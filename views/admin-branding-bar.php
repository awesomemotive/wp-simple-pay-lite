<?php
/**
 * Admin: Page branding
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 *
 * @var string $logo_url Logo URL. Empty string to not link.
 */

?>

<div class="simpay-branding-bar">
	<div class="simpay-branding-bar__title">
		<?php if ( ! empty( $logo_url ) ) : ?>
			<a href="<?php echo esc_url( $logo_url ); ?>" target="_blank" rel="noopener noreferrer">
		<?php endif; ?>
		<img
			src="<?php echo esc_url( SIMPLE_PAY_INC_URL . '/core/assets/images/wp-simple-pay.svg' ); // @phpstan-ignore-line ?>"
			alt="WP Simple Pay"
			class="simpay-branding-bar__logo"
		/>
		<?php if ( ! empty( $logo_url ) ) : ?>
			</a>
		<?php endif; ?>

		<span class="simpay-branding-bar__divider">/</span>
	</div>
	<div class="simpay-branding-bar__actions">
		<?php
		/**
		 * Allows output in the WP Simple Pay branding bar "actions" section.
		 *
		 * @since 4.4.6
		 */
		do_action( 'simpay_admin_branding_bar_actions' );
		?>
	</div>
	<div class="clear"></div>
</div>

<script>
var getNextInclusiveUntil = function( elem, selector ) {
	var siblings = [ elem ];
	var next = elem.nextElementSibling;

	while( next ) {
		if ( selector && next.matches( selector ) ) {
			break;
		}

		siblings.push( next );

		next = next.nextElementSibling;
	}

	return siblings;
};

document.addEventListener( 'DOMContentLoaded', function() {
	var brandingBarTitle = document.querySelector( '.simpay-branding-bar__title' );

	var titleEls = getNextInclusiveUntil(
		document.querySelector( '.wp-heading-inline' ),
		'.wp-header-end'
	);

	titleEls.forEach( function( el ) {
		brandingBarTitle.appendChild( el );
	} );

	// Move core update nag.
	var coreUpdateNag = document.querySelector( '.update-nag.notice.notice-warning.inline' );
	var brandingBar = document.querySelector( '.simpay-branding-bar' );

	if ( coreUpdateNag ) {
		brandingBar.after( coreUpdateNag );
	}
} );
</script>
