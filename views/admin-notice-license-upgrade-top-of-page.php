<?php
/**
 * Admin notice: License upgrade
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 *
 * @var array<string> $data Notice data.
 */
?>

<?php echo $data['message']; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>

<button type="button" class="button-link simpay-notice-dismiss">
	&times;
</button>
