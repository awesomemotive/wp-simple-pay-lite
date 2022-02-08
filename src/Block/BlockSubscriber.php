<?php
/**
 * Block: Subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Block;

use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * BlockSubscriber class.
 *
 * @since 4.4.2
 */
class BlockSubscriber implements SubscriberInterface {

	/**
	 * Block types.
	 *
	 * @since 4.4.2
	 * @var \SimplePay\Core\Block\BlockInterface[] $blocks Block types.
	 */
	private $blocks;

	/**
	 * BlockSubscriber.
	 *
	 * @since 4.4.2
	 *
	 * @param \SimplePay\Core\Block\BlockInterface[] $blocks Block types.
	 */
	public function __construct( array $blocks ) {
		$this->blocks = array();

		foreach ( $blocks as $block ) {
			$this->add_block( $block );
		}
	}

	/**
	 * Adds an block to be registered.
	 *
	 * @since 4.4.2
	 *
	 * @param \SimplePay\Core\Block\BlockInterface $block Block to add for future registration.
	 * @return void
	 */
	private function add_block( BlockInterface $block ) {
		$this->blocks[] = $block;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'init' => 'register_block_types',
		);
	}

	/**
	 * Registers added block types.
	 *
	 * @since 4.4.2
	 *
	 * @return void
	 */
	public function register_block_types() {
		foreach ( $this->blocks as $block ) {
			$block->register();
		}
	}

}