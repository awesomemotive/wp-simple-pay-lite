/**
 * Admin upgrade link JS
 */

jQuery(document).ready(function ($) {
	// Open upgrade link in a new window.
	$('a[href="admin.php?page=stripe-checkout-upgrade"]').on('click', function () {
		$(this).attr('target', '_blank');
	});
});


