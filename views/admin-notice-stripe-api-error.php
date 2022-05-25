<?php
/**
 * Admin notice: Stripe API error
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 *
 * @var array<string> $data Notice data.
 */

echo wp_kses_post( wpautop( $data['error'] ) );
