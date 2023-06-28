<?php
/**
 * Form builder: custom field subscriber
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.7
 */

namespace SimplePay\Core\Admin\FormBuilder;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\Post_Types\Simple_Pay\Edit_Form as Lite_Edit_Form;
use SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form as Pro_Edit_Form;
use WP_Post;

/**
 * CustomFieldSubscriber class.
 *
 * @since 4.7.7
 */
class CustomFieldSubscriber implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'wp_ajax_simpay_add_field' => 'add_field',
		);
	}

	/**
	 * Returns the markup for a newly added custom field.
	 *
	 * @since 4.7.7
	 *
	 * @return void
	 */
	public function add_field() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		check_ajax_referer( 'simpay_custom_fields_nonce', 'addFieldNonce' );

		ob_start();

		$type = isset( $_POST['fieldType'] ) ? sanitize_key( strtolower( $_POST['fieldType'] ) ) : '';

		$counter = isset( $_POST['counter'] ) ? intval( $_POST['counter'] ) : 0;
		$uid     = isset( $_POST['nextUid'] ) ? intval( $_POST['nextUid'] ) : $counter;

		// Load new metabox depending on what type was selected.
		if ( ! empty( $type ) ) {
			try {
				global $post;

				$post = get_post( absint( $_POST['post_id'] ) );

				if ( ! $post instanceof WP_Post ) {
					wp_send_json_error();
				}

				/** @var $post WP_Post */

				if ( $this->license->is_lite() ) {
					echo Lite_Edit_Form\__unstable_get_custom_field(
						$type,
						$counter,
						array(
							'uid' => $uid,
						),
						$post->ID
					);
				} else {
					echo Pro_Edit_Form\get_custom_field(
						$type,
						$counter,
						array(
							'uid' => $uid,
						),
						$post->ID
					);
				}
			} catch ( \Exception $e ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $e,
					)
				);
			}
		} else {
			wp_send_json_error( array( 'success' => false ) );
		}

		ob_end_flush();

		die();
	}

}
