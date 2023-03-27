<?php
/**
 * Default email header template.
 *
 * @since 4.7.3
 *
 * @package SimplePay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 *
 * @var string $content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width" />
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" class="body">
	<tr>
		<td align="center" valign="top" class="body-inner">
			<table border="0" cellpadding="0" cellspacing="0" class="container">
				<tr>
					<td align="center" valign="middle" class="header">
						<?php echo wp_kses_post( $content ); ?>
					</td>
				</tr>
				<tr>
					<td align="left" valign="top" class="content">
