<?php
/**
 * Admin: Page branding
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

?>

<div class="simpay-branding-bar">
	<div class="simpay-branding-bar__title">
		<img
			src="<?php echo esc_url( SIMPLE_PAY_INC_URL . '/core/assets/images/wp-simple-pay.svg' ); // @phpstan-ignore-line ?>"
			alt="WP Simple Pay"
			class="simpay-branding-bar__logo"
		/>
		<span class="simpay-branding-bar__divider">/</span>
	</div>
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
	var brandingBar = document.querySelector( '.simpay-branding-bar__title' );

	var titleEls = getNextInclusiveUntil(
		document.querySelector( '.wp-heading-inline' ),
		'.wp-header-end'
	);

	titleEls.forEach( function( el ) {
		brandingBar.appendChild( el );
	} );
} );
</script>
