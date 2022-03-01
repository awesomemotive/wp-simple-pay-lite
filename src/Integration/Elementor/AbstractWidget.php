<?php
/**
 * Elementor: Abstract widget
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration\Elementor;

use Elementor\Widget_Base;

/**
 * AbstractWidget class.
 *
 * @since 4.4.3
 */
abstract class AbstractWidget implements WidgetInterface {

	/**
	 * Payment Form control.
	 *
	 * @var \SimplePay\Core\Integration\Elementor\PaymentFormControl
	 */
	private $control;

	/**
	 * AbstractWidget.
	 *
	 * @since 4.4.3
	 *
	 * @param \SimplePay\Core\Integration\Elementor\PaymentFormControl $control Payment form control.
	 */
	public function __construct( PaymentFormControl $control ) {
		$this->control = $control;
	}

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_name();

	/**
	 * Adds the widget control.
	 *
	 * @since 4.4.3
	 *
	 * @param \Elementor\Widget_Base $widget Widget.
	 * @return void
	 */
	public function add_control( Widget_Base $widget ) { // @phpstan-ignore-line
		$this->control->add_control( $widget );
	}

	/**
	 * Renders the widget.
	 *
	 * @since 4.4.3
	 *
	 * @param string                 $content Widget content.
	 * @param \Elementor\Widget_Base $widget Widget.
	 * @return string
	 */
	public function render_widget( $content, $widget ) { // @phpstan-ignore-line
		if ( $widget->get_name() !== $this->get_name() ) { // @phpstan-ignore-line
			return $content;
		}

		return $this->control->render_widget( $content, $widget );
	}

}
