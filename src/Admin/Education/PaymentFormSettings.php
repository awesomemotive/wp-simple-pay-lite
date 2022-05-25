<?php
/**
 * Admin: Payment form feature education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\Admin\Education;

use Sandhills\Utils\Persistent_Dismissible;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;
use SimplePay\Core\Settings;

/**
 * PaymentFormSettings class.
 *
 * @since 4.4.0
 */
class PaymentFormSettings extends AbstractProductEducation implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		// Lite.
		if ( true === $this->license->is_lite() ) {
			return array(
				'simpay_admin_after_form_display_options_rows' =>
					array( 'form_type', 20 ),
				'__unstable_simpay_form_settings_lite_payment_amount' =>
					'price_options',
				'simpay_form_settings_meta_form_display_panel' => 'form_fields',
				'simpay_admin_before_stripe_checkout_rows' =>
					'payment_methods',
			);
		}

		// Personal license.
		if ( false === $this->license->is_pro( 'personal', '>' ) ) {
			return array(
				'__unstable_simpay_form_settings_pro_after_price_options' =>
					'subscription_options',
			);
		}

		// Non-Personal license.
		return array();
	}

	/**
	 * Outputs the "Form Type" upsell CTA.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function form_type() {
		// Dismissed temporary notice.
		$dismissed_notice = (bool) Persistent_Dismissible::get(
			array(
				'id' => 'simpay-form-settings-form-type-license-upgrade',
			)
		);

		if ( true === $dismissed_notice ) {
			return;
		}

		$utm_medium            = 'form-settings-form-type';
		$utm_content           = 'Create On-Site Payment Forms';
		$upgrade_url           = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_text          = $this->get_upgrade_button_text();
		$upgrade_subtext       = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-payment-form-type-settings.php'; // @phpstan-ignore-line
	}

	/**
	 * Outputs the "Price Options" upsell CTA.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function price_options() {
		// Dismissed temporary notice.
		$dismissed_notice = (bool) Persistent_Dismissible::get(
			array(
				'id' => 'simpay-form-settings-payment-license-upgrade',
			)
		);

		if ( true === $dismissed_notice ) {
			return;
		}

		$utm_medium            = 'form-settings-payment';
		$utm_content           = 'Multiple Price Options & Subscriptions';
		$upgrade_url           = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_text          = $this->get_upgrade_button_text();
		$upgrade_subtext       = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-payment-form-payment-settings.php'; // @phpstan-ignore-line
	}

	/**
	 * Outputs the "Subscription Options" upsell CTA.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function subscription_options() {
		// Dismissed temporary notice.
		$dismissed_notice = (bool) Persistent_Dismissible::get(
			array(
				'id' => 'license_subscription_upgrade',
			)
		);

		if ( true === $dismissed_notice ) {
			return;
		}

		$utm_medium            = 'form-settings-subscription';
		$utm_content           = 'Need your customers to sign up for recurring payments?';
		$upgrade_url           = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_text          = $this->get_upgrade_button_text();
		$upgrade_subtext       = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-payment-form-subscription-settings.php'; // @phpstan-ignore-line
	}

	/**
	 * Outputs the "Form Fields" upsell CTA.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function form_fields() {

		$utm_medium            = 'form-settings-fields';
		$utm_content           = 'Custom Fields + Custom Data';
		$upgrade_url           = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_text          = $this->get_upgrade_button_text();
		$upgrade_subtext       = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);
		$field_groups          = Edit_Form\get_custom_fields_grouped();

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-payment-form-form-field-settings.php'; // @phpstan-ignore-line
	}

	/**
	 * Outputs the "Payment Methods" upsell CTA.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function payment_methods() {
		// Dismissed temporary notice.
		$dismissed_notice = (bool) Persistent_Dismissible::get(
			array(
				'id' => 'simpay-form-settings-payment-methods-license-upgrade',
			)
		);

		if ( true === $dismissed_notice ) {
			return;
		}

		$utm_medium            = 'form-settings-payment-methods';
		$utm_content           = 'Offer Multiple Payment Methods (Stripe Checkout)';
		$upgrade_url           = $this->get_upgrade_button_url(
			$utm_medium,
			$utm_content
		);
		$upgrade_text          = $this->get_upgrade_button_text();
		$upgrade_subtext       = $this->get_upgrade_button_subtext(
			$upgrade_url
		);
		$already_purchased_url = $this->get_already_purchased_url(
			$utm_medium,
			$utm_content
		);

		$icons = array(
			'<svg height="30" width="30" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#e3e8ee"></path><path d="M26 11H6v-.938C6 9.2 6.56 8.5 7.25 8.5h17.5c.69 0 1.25.7 1.25 1.563zm0 3.125v8.125c0 .69-.56 1.25-1.25 1.25H7.25c-.69 0-1.25-.56-1.25-1.25v-8.125zM11 18.5a1.25 1.25 0 0 0 0 2.5h1.25a1.25 1.25 0 0 0 0-2.5z" fill="#697386"></path></g></svg>',
			'<svg height="30" width="30" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#e3e8ee"></path><path d="M7.274 13.5a1.25 1.25 0 0 1-.649-2.333C7.024 10.937 10.15 9.215 16 6c5.851 3.215 8.976 4.937 9.375 5.167a1.25 1.25 0 0 1-.65 2.333zm12.476 10v-8.125h3.75V23.5H25a1 1 0 0 1 1 1V26H6v-1.5a1 1 0 0 1 1-1h1.5v-8.125h3.75V23.5h1.875v-8.125h3.75V23.5z" fill="#697386"></path></g></svg>',
			'<svg height="30" width="30" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#fff"/><g fill-rule="nonzero"><path d="M25.64 14.412h-7.664l-.783.896-2.525 2.898-.783.896H6.331l.764-.906.362-.428.763-.907H4.746c-.636 0-1.155.548-1.155 1.205v2.55c0 .666.52 1.204 1.155 1.204h13.328c.637 0 1.508-.398 1.928-.896l2.016-2.33z" fill="#005498"/><path d="M27.176 11.694c.636 0 1.154.548 1.154 1.205v2.539c0 .667-.518 1.204-1.154 1.204H23.71l.773-.896.382-.448.773-.896h-7.662l-4.081 4.68H6.292l5.451-6.273.206-.239c.43-.488 1.301-.896 1.937-.896h13.29z" fill="#ffbf00"/></g></g></svg>',
			'<svg width="30" height="30" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 16"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.587 6.988a523.403 523.403 0 00-4.322-5.492c-.23-.289-.566-.635-.958-.438-.265.132-.506.525-.533.82-.089.969-.082 1.945-.092 2.918-.001.11.11.23.19.331.604.771 1.218 1.535 1.818 2.308.167.215.26.39.29.557-.03.234-.123.352-.29.567-.6.773-1.214 1.54-1.818 2.311-.08.102-.191.226-.19.336.01.973.003 1.949.092 2.917.027.295.268.688.533.82.392.196.728-.152.958-.441a521.565 521.565 0 004.322-5.496c.254-.327.388-.546.413-1.014-.025-.34-.16-.677-.413-1.004z" fill="#1F2C5C"/><path fill-rule="evenodd" clip-rule="evenodd" d="M.413 6.988c1.426-1.84 2.87-3.669 4.322-5.492.23-.289.566-.635.958-.438.265.132.506.525.533.82.089.969.082 1.945.092 2.918.001.11-.11.23-.19.331-.604.771-1.218 1.535-1.818 2.308-.167.215-.261.39-.29.557.029.234.123.352.29.567.6.773 1.214 1.54 1.818 2.311.08.102.191.226.19.336-.01.973-.003 1.949-.092 2.917-.027.295-.268.688-.533.82-.392.196-.728-.152-.958-.441A523.131 523.131 0 01.413 9.006C.159 8.679.025 8.46 0 7.992c.025-.34.16-.677.413-1.004z" fill="#1A8ACB"/></svg>',
			'<svg height="30" width="30" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#fff"></path><path d="M18.28 21.961l-.155.817h-6.556l.308-1.615c.172-.906.47-1.499.898-1.78.427-.28 1.355-.496 2.784-.647 1.142-.117 1.85-.276 2.12-.478.273-.2.488-.722.648-1.564.14-.738.102-1.217-.117-1.437-.219-.22-.765-.33-1.641-.33-1.094 0-1.8.09-2.12.267-.322.178-.547.607-.675 1.286l-.11.64h-1.007l.092-.445c.195-1.027.555-1.711 1.08-2.053.526-.34 1.48-.512 2.862-.512 1.226 0 2.017.184 2.369.552.352.37.426 1.088.223 2.157-.195 1.027-.521 1.713-.98 2.06-.46.344-1.359.578-2.698.7-1.176.108-1.894.26-2.153.453-.26.192-.474.739-.645 1.64l-.055.29zm8.623-7.748l-1.1 5.783h1.362l-.156.817h-1.36l-.377 1.98h-1.025l.376-1.98H19.53l.215-1.137 5.573-5.463h1.587zm-2.126 5.783l.981-5.16h-.02l-5.208 5.16z" fill="#99a0a6"></path><path d="M3 22.762l1.656-8.652h4.269c1.051 0 1.733.188 2.043.564.31.376.367 1.08.17 2.111-.19.989-.518 1.663-.985 2.021-.467.36-1.25.54-2.346.54l-.411.006H4.705l-.653 3.41zm1.862-4.234h2.493c1.042 0 1.73-.1 2.062-.298.332-.198.566-.655.702-1.369.16-.837.163-1.366.007-1.588-.156-.221-.604-.333-1.347-.333l-.401-.006H5.55z" fill="#d40e2b"></path><path d="M9.143 10.96l-1.013-.671a22.123 22.123 0 0 1 3.717-1.386l.186.914c-.915.26-1.88.632-2.89 1.143zm11.48-.502a10.83 10.83 0 0 0-2.991-1.001L18.449 8h.023c2.362.011 4.24.308 5.72.722l-3.569 1.736zm-13.414.301l1.034.7c-.471.27-.953.571-1.443.905H4.793s.83-.737 2.415-1.605zm10.026-2.708l-.484 1.29a12.352 12.352 0 0 0-4.016.264l-.138-.924c1.52-.358 3.074-.57 4.638-.631zm8.84 1.304C28.215 10.293 29 11.36 29 11.36h-6.92s-.228-.198-.659-.473z" fill="#99a0a6"></path></g></svg>',
			'<svg height="30" width="30" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#10298d"/><path d="M27.485 18.42h-2.749l-.37 1.342H22.24L24.533 12h3.104l2.325 7.762h-2.083l-.393-1.342zm-.408-1.512l-.963-3.364-.936 3.364zm-10.452 2.854V12h3.83c.526 0 .928.044 1.203.13.63.202 1.052.612 1.27 1.233.111.325.167.816.167 1.47 0 .788-.06 1.354-.183 1.699-.247.68-.753 1.072-1.517 1.175-.09.015-.472.028-1.146.04l-.341.011H18.68v2.004zm2.056-3.805h1.282c.407-.015.653-.047.744-.096.12-.068.202-.204.242-.408.026-.136.04-.337.04-.604 0-.329-.026-.573-.079-.732-.073-.222-.25-.358-.53-.407a3.91 3.91 0 00-.4-.011h-1.299zm-10.469-1.48H6.3c0-.32-.038-.534-.11-.642-.114-.162-.43-.242-.942-.242-.5 0-.831.046-.993.139-.161.093-.242.296-.242.608 0 .283.072.469.215.558a.91.91 0 00.408.112l.386.026c.517.033 1.033.072 1.55.119.654.066 1.126.243 1.421.53.231.222.37.515.414.875.025.216.037.46.037.73 0 .626-.057 1.083-.175 1.374-.213.532-.693.868-1.437 1.009-.312.06-.788.089-1.43.089-1.072 0-1.819-.064-2.24-.196-.517-.158-.858-.482-1.024-.969-.092-.269-.137-.72-.137-1.353h1.914v.162c0 .337.096.554.287.65.13.067.29.101.477.106h.704c.359 0 .587-.019.687-.056a.57.57 0 00.346-.34 1.38 1.38 0 00.044-.374c0-.341-.123-.55-.368-.624-.092-.03-.52-.071-1.28-.123a15.411 15.411 0 01-1.274-.128c-.626-.119-1.044-.364-1.252-.736-.184-.315-.275-.793-.275-1.432 0-.487.05-.877.148-1.17.1-.294.258-.517.48-.669.321-.234.735-.371 1.237-.412.463-.04.927-.058 1.391-.056.803 0 1.375.046 1.717.14.833.227 1.248.863 1.248 1.909a5.8 5.8 0 01-.018.385z" fill="#fff"/><path d="M13.786 13.092c.849 0 1.605.398 2.103 1.02l.444-.966a3.855 3.855 0 00-2.678-1.077c-1.62 0-3.006.995-3.575 2.402h-.865l-.51 1.111h1.111c-.018.23-.017.46.006.69h-.56l-.51 1.111h1.354a3.853 3.853 0 003.549 2.335c.803 0 1.55-.244 2.167-.662v-1.363a2.683 2.683 0 01-2.036.939 2.7 2.7 0 01-2.266-1.248h2.832l.511-1.112h-3.761a2.886 2.886 0 01-.016-.69h4.093l.51-1.11h-4.25a2.704 2.704 0 012.347-1.38" fill="#ffcc02"/></g></svg>',
			'<svg height="30" width="30" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="nonzero"><path fill="#FFF" d="M0 0h32v32H0z"></path><g transform="translate(3 5)"><path d="M0 1.694v19.464c0 .936.758 1.694 1.694 1.694h11.63c8.788 0 12.599-4.922 12.599-11.448C25.923 4.903 22.112 0 13.323 0H1.694C.759 0 0 .758 0 1.694z" fill="#FFF"></path><path d="M13.321 21.296H3.206A1.628 1.628 0 0 1 1.58 19.67V3.182c.001-.898.729-1.625 1.626-1.626h10.115c9.593 0 11.026 6.17 11.026 9.848 0 6.381-3.916 9.892-11.026 9.892zM3.206 2.098c-.598 0-1.084.485-1.085 1.084V19.67c.001.599.487 1.084 1.085 1.084h10.115c6.76 0 10.484-3.32 10.484-9.35 0-8.097-6.569-9.306-10.484-9.306H3.206z" fill="#000"></path><path d="M7.781 4.78v14.377h6.259c5.686 0 8.151-3.213 8.151-7.746 0-4.342-2.465-7.716-8.151-7.716H8.865c-.598 0-1.084.485-1.084 1.084z" fill="#C06"></path><path fill="#FFF" d="M19.713 9.47v2.8h1.674v.635h-2.429V9.47zM17.199 9.47l1.285 3.435H17.7l-.26-.762h-1.285l-.27.762h-.762l1.3-3.435h.776zm.043 2.107l-.433-1.26H16.8l-.447 1.26h.89zM14.612 9.47v.635h-1.814v.736h1.665v.587h-1.665v.842h1.853v.635h-2.607V9.47zM9.985 9.47c.21-.002.42.034.617.106.187.068.356.176.496.318.146.15.257.331.328.529.082.24.122.492.117.746.002.234-.03.467-.096.692-.059.2-.157.387-.29.549-.133.156-.3.28-.487.363-.216.093-.45.138-.685.132H8.503V9.47h1.482zm-.053 2.8a.983.983 0 0 0 .317-.053.703.703 0 0 0 .275-.176.888.888 0 0 0 .192-.319c.052-.155.076-.318.072-.481a2.04 2.04 0 0 0-.05-.47.932.932 0 0 0-.17-.357.74.74 0 0 0-.305-.23 1.212 1.212 0 0 0-.47-.079h-.538v2.165h.677z"></path><path d="M4.953 13.683a1.2 1.2 0 0 1 1.2 1.2v4.274a2.401 2.401 0 0 1-2.401-2.401v-1.872a1.2 1.2 0 0 1 1.2-1.2z" fill="#000"></path><circle fill="#000" cx="4.953" cy="11.188" r="1.585"></circle></g></g></svg>',
			'<svg height="30" width="30" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#fff"></path><path d="M4 11.191C4 9.705 5.239 8.5 6.766 8.5h18.468C26.762 8.5 28 9.705 28 11.191v9.618c0 1.486-1.238 2.691-2.766 2.691H6.766C5.239 23.5 4 22.295 4 20.809zm1.02 9.6c0 .944.783 1.71 1.75 1.71h9.213V9.5H6.77c-.967 0-1.75.765-1.75 1.708v9.584zm13.749-.104h2.272v-3.57h.025c.43.782 1.29 1.072 2.084 1.072 1.957 0 3.004-1.615 3.004-3.558 0-1.589-.997-3.319-2.815-3.319-1.035 0-1.994.417-2.45 1.338h-.025v-1.185h-2.095zm5.037-6.005c0 1.047-.518 1.766-1.376 1.766-.758 0-1.39-.72-1.39-1.678 0-.984.556-1.716 1.39-1.716.885 0 1.376.757 1.376 1.627z" fill="#04337b"></path><path d="M14.153 11.463v5.71c0 2.657-1.33 3.515-4.017 3.515a7.958 7.958 0 0 1-2.547-.41l.115-1.764c.703.335 1.292.533 2.253.533 1.33 0 2.047-.607 2.047-1.874v-.348h-.026c-.55.757-1.318 1.105-2.24 1.105-1.83 0-2.969-1.34-2.969-3.252 0-1.924.935-3.366 3.007-3.366.985 0 1.78.523 2.267 1.318h.025v-1.168zM9.15 14.64c0 1.005.616 1.576 1.306 1.576.818 0 1.472-.67 1.472-1.664 0-.72-.435-1.527-1.472-1.527-.857 0-1.306.734-1.306 1.615z" fill="#ee3525"></path></g></svg>',
			'<svg height="30" width="30" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#1c9fe5"/><path d="M23.104 18.98a142.494 142.494 0 0011.052 3.848c2.044.85 0 5.668-2.159 4.674-2.444-1.066-7.359-3.245-11.097-5.108C18.822 24.842 15.556 28 10.907 28 6.775 28 4 25.568 4 21.943c0-3.053 2.11-6.137 6.82-6.137 2.697 0 5.47.766 8.785 1.922a25.007 25.007 0 001.529-3.838l-11.981-.006v-1.848l6.162.015V9.63H7.808V7.81l7.507.006V5.115c0-.708.38-1.115 1.042-1.115h3.14v3.827l7.442.005v1.805h-7.44v2.431l6.088.016s-.754 3.904-2.483 6.897zM5.691 21.79v-.004c0 1.736 1.351 3.489 4.64 3.489 2.54 0 5.028-1.52 7.408-4.522-3.181-1.592-4.886-2.357-7.348-2.357-2.394 0-4.7 1.164-4.7 3.394z" fill="#fff" fill-rule="nonzero"/></g></svg>',
		);

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-education-payment-form-payment-method-settings.php'; // @phpstan-ignore-line
	}

}
