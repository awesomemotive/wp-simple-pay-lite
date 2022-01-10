<?php
/**
 * Event management: Event manager
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\EventManagement;

/**
 * EventManager class.
 *
 * @since 4.4.0
 */
class EventManager {

	/**
	 * Adds an event subscriber.
	 *
	 * @since 4.4.0
	 *
	 * @param SubscriberInterface $subscriber SubscriberInterface implementation.
	 * @return void
	 */
	public function add_subscriber( SubscriberInterface $subscriber ) {
		$events = $subscriber->get_subscribed_events();

		if ( empty( $events ) ) {
			return;
		}

		foreach ( $events as $hook_name => $parameters ) {
			$this->add_subscriber_callback(
				$subscriber,
				$hook_name,
				$parameters
			);
		}
	}

	/**
	 * Removes an event subscriber.
	 *
	 * @since 4.4.0
	 *
	 * @param SubscriberInterface $subscriber SubscriberInterface implementation.
	 * @return void
	 */
	public function remove_subscriber( SubscriberInterface $subscriber ) {
		$events = $subscriber->get_subscribed_events();

		if ( empty( $events ) ) {
			return;
		}

		foreach ( $events as $hook_name => $parameters ) {
			$this->remove_subscriber_callback(
				$subscriber,
				$hook_name,
				$parameters
			);
		}
	}

	/**
	 * Adds a callback to a specific hook of the WordPress plugin API.
	 *
	 * @since 4.4.0
	 *
	 * @param string   $hook_name Name of the hook.
	 * @param callable $callback Callback function.
	 * @param int      $priority Optional. Callback priority. Default 10.
	 * @param int      $accepted_args Optional. Number of arguments the callback accepts. Default 1.
	 * @return void
	 */
	public function add_callback(
		$hook_name,
		$callback,
		$priority = 10,
		$accepted_args = 1
	) {
		add_filter( $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Checks the WordPress plugin API to see if the given hook has the given callback.
	 *
	 * @since 4.4.0
	 *
	 * @param string         $hook_name Hook name.
	 * @param callable|false $callback Optional. Callback function.
	 * @return int|bool If callback is omitted, returns boolean for whether the hook has anything registered.
	 *                   When checking a specific function, the priority of that hook is returned, or false
	 *                   if the function is not attached.
	 */
	public function has_callback( $hook_name, $callback = false ) {
		return has_filter( $hook_name, $callback );
	}

	/**
	 * Removes the given callback from the given hook.
	 *
	 * @since 4.4.0
	 *
	 * @param string   $hook_name Hook name.
	 * @param callable $callback Callback.
	 * @param int      $priority Optional. Callback priority. Default 10.
	 * @return bool Whether the function existed before it was removed.
	 */
	public function remove_callback( $hook_name, $callback, $priority = 10 ) {
		return remove_filter( $hook_name, $callback, $priority );
	}

	/**
	 * Adds the given subscriber's callback to a specific hook.
	 *
	 * @since 4.4.0
	 *
	 * @param SubscriberInterface $subscriber Subscriber_Interface implementation.
	 * @param string              $hook_name Hook name.
	 * @param mixed               $parameters Event parameters. Accepts a string, array, or a multidimensional array.
	 * @return void
	 */
	private function add_subscriber_callback(
		SubscriberInterface $subscriber,
		$hook_name,
		$parameters
	) {
		if ( is_string( $parameters ) ) {
			$callback = array( $subscriber, $parameters );

			if ( is_callable( $callback ) ) {
				$this->add_callback( $hook_name, $callback );
			}
		} elseif (
			is_array( $parameters ) &&
			count( $parameters ) !== count( $parameters, COUNT_RECURSIVE )
		) {
			foreach ( $parameters as $parameter ) {
				$this->add_subscriber_callback(
					$subscriber,
					$hook_name,
					$parameter
				);
			}
		} elseif ( is_array( $parameters ) && isset( $parameters[0] ) ) {
			$callback = array( $subscriber, $parameters[0] );

			if ( is_callable( $callback ) ) {
				$this->add_callback(
					$hook_name,
					$callback,
					isset( $parameters[1] )
						? $parameters[1]
						: 10,
					isset( $parameters[2] )
						? $parameters[2]
						: 1
				);
			}
		}
	}

	/**
	 * Removes the given subscriber's callback to a specific hook.
	 *
	 * @since 4.4.0
	 *
	 * @param SubscriberInterface $subscriber SubscriberInterface implementation.
	 * @param string              $hook_name Hook name.
	 * @param mixed               $parameters Event parameters. Accepts a string, array, or a multidimensional array.
	 * @return void
	 */
	private function remove_subscriber_callback(
		SubscriberInterface $subscriber,
		$hook_name,
		$parameters
	) {
		if ( is_string( $parameters ) ) {
			$callback = array( $subscriber, $parameters );

			if ( is_callable( $callback ) ) {
				$this->remove_callback( $hook_name, $callback );
			}
		} elseif (
			is_array( $parameters ) &&
			count( $parameters ) !== count( $parameters, COUNT_RECURSIVE )
		) {
			foreach ( $parameters as $parameter ) {
				$this->remove_subscriber_callback(
					$subscriber,
					$hook_name,
					$parameter
				);
			}
		} elseif ( is_array( $parameters ) && isset( $parameters[0] ) ) {
			$callback = array( $subscriber, $parameters[0] );

			if ( is_callable( $callback ) ) {
				$this->remove_callback(
					$hook_name,
					$callback,
					isset( $parameters[1] )
						? $parameters[1]
						: 10
				);
			}
		}
	}
}
