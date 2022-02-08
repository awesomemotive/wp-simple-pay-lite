<?php
/**
 * Event Management: Subscriber interface
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\EventManagement;

/**
 * SubscriberInterface interface.
 *
 * @since 4.4.0
 */
interface SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber is listening to.
	 *
	 * The array key is the event (hook/filter) name.
	 * The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * Example:
	 *
	 *  array( 'hook_name' => 'method_name' )
	 *  array( 'hook_name' => array( 'method_name', $priority ) )
	 *  array( 'hook_name' => array( 'method_name', $priority, $accepted_args ) )
	 *  array( 'hook_name' => array(
	 *    array( 'method_name_1', $priority_1, $accepted_args_1 ) ),
	 *    array( 'method_name_2', $priority_2, $accepted_args_2 ) )
	 *  )
	 *
	 * @since 4.4.0
	 *
	 * @return array<mixed>
	 */
	public function get_subscribed_events();

}
