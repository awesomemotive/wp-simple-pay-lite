<?php
/**
 * Admin: Product education abstract
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use SimplePay\Core\License\License;

/**
 * AbstractProductEducation abstract.
 *
 * @since 4.4.0
 */
abstract class AbstractProductEducation implements ProductEducationInterface {

	/**
	 * License.
	 *
	 * @since 4.4.0
	 * @var \SimplePay\Core\License\License
	 */
	protected $license;

	/**
	 * ProductEducationDashboardWidget.
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\License\License $license Plugin license.
	 */
	public function __construct( License $license ) {
		$this->license = $license;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_upgrade_button_url( $utm_medium, $utm_content = '' ) {
		return simpay_pro_upgrade_url( $utm_medium, $utm_content );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_upgrade_button_text() {
		if ( true === $this->license->is_lite() ) {
			$text = __( 'Upgrade to WP Simple Pay Pro', 'stripe' );
		} else {
			$text = __( 'See Upgrade Options', 'stripe' );
		}

		return $text;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_already_purchased_url( $utm_medium, $utm_content = '' ) {
		return simpay_docs_link(
			$utm_content,
			$this->license->is_lite()
				? 'upgrading-wp-simple-pay-lite-to-pro'
				: 'activate-wp-simple-pay-pro-license',
			$utm_medium,
			true
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_upgrade_button_subtext( $upgrade_url ) {
		if ( true === $this->license->is_lite() ) {
			return wp_kses(
				sprintf(
					/* translators: %1$s Opening bold tag, do not translate. %2$s Closing bold tag, do not translate. %3$s Opening underline tag, do not translate. %4$s Closing underline tag, do not translate. %5$s Opening anchor tag, do not translate. %6$s Closing anchor tag, do not translate. */
					__(
						'%1$sBonus%2$s: WP Simple Pay Lite users get %3$s50%% off%4$s regular price, automatically applied at checkout. %5$sUpgrade to Pro →%6$s',
						'stripe'
					),
					'<strong>',
					'</strong>',
					'<u>',
					'</u>',
					'<a href="' . esc_url( $upgrade_url ) . '" rel="noopener noreferrer" target="_blank">',
					'</a>'
				),
				array(
					'strong' => array(),
					'u'      => array(),
					'a'      => array(
						'href'   => true,
						'target' => true,
						'rel'    => true,
					)
				)
			);
		} else {
			return wp_kses(
				sprintf(
					/* translators: %1$s Opening bold tag, do not translate. %2$s Closing bold tag, do not translate. %3$s Opening underline tag, do not translate. %4$s Closing underline tag, do not translate. %5$s Opening anchor tag, do not translate. %6$s Closing anchor tag, do not translate. */
					__(
						'%1$sBonus%2$s: WP Simple Pay Pro users get %3$s50%% off%4$s upgrade pricing, automatically applied at checkout. %5$sSee upgrade options →%6$s',
						'stripe'
					),
					'<strong>',
					'</strong>',
					'<u>',
					'</u>',
					'<a href="' . esc_url( $upgrade_url ) . '" rel="noopener noreferrer" target="_blank">',
					'</a>'
				),
				array(
					'strong' => array(),
					'u'      => array(),
					'a'      => array(
						'href'   => true,
						'target' => true,
						'rel'    => true,
					)
				)
			);
		}
	}

}
