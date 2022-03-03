<?php
/**
 * Elementor: "Button" widget
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
 * ButtonWidget class.
 *
 * @since 4.4.3
 */
class ButtonWidget extends AbstractWidget implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_name() {
		return 'button';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'elementor/element/button/section_button/after_section_end' =>
				'add_control',
			'elementor/widget/render_content' => array( 'render_widget', 10, 2 ),
		);
	}

}
