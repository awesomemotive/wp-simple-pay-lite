<?php
/**
 * Elementor: "Call to Action" widget
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration\Elementor;

use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * CallToActionWidget class.
 *
 * @since 4.4.3
 */
class CallToActionWidget extends AbstractWidget implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_name() {
		return 'call-to-action';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'elementor/element/call-to-action/section_ribbon/after_section_end' =>
				'add_control',
			'elementor/widget/render_content' => array( 'render_widget', 10, 2 ),
		);
	}

}
