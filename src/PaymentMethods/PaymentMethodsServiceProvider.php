<?php
/**
 * Payment Methods: Service Provider
 *
 * @since 4.16.0
 *
 * @package SimplePay
 */

namespace SimplePay\Core\PaymentMethods;

use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * Payment Methods Service Provider
 *
 * @since 4.16.0
 */
class PaymentMethodsServiceProvider extends AbstractPluginServiceProvider implements LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();
		$license   = $container->get( 'license' );
		if ( ! $license->is_lite() ) {
			$container->share( 'payment-methods-card', PaymentMethod\CardPaymentMethod::class );
			$container->share( 'payment-methods-ach-debit', PaymentMethod\ACHDebitPaymentMethod::class );
			$container->share( 'payment-methods-alipay', PaymentMethod\AlipayPaymentMethod::class );
			$container->share( 'payment-methods-affirm', PaymentMethod\AffirmPaymentMethod::class );
			$container->share( 'payment-methods-sepa-debit', PaymentMethod\SepaDebitPaymentMethod::class );
			$container->share( 'payment-methods-bacs-debit', PaymentMethod\BacsDebitPaymentMethod::class );
			$container->share( 'payment-methods-cashapp', PaymentMethod\CashAppPaymentMethod::class );
			$container->share( 'payment-methods-bancontact', PaymentMethod\BancontactPaymentMethod::class );
			$container->share( 'payment-methods-fpx', PaymentMethod\FPXPaymentMethod::class );
			$container->share( 'payment-methods-grabpay', PaymentMethod\GrabPayPaymentMethod::class );
			$container->share( 'payment-methods-ideal', PaymentMethod\IDealPaymentMethod::class );
			$container->share( 'payment-methods-klarna', PaymentMethod\KlarnaPaymentMethod::class );
			$container->share( 'payment-methods-p24', PaymentMethod\P24PaymentMethod::class );
			$container->share( 'payment-methods-afterpay-clearpay', PaymentMethod\AfterpayClearpayPaymentMethod::class );
			$container->share( 'payment-methods-mobilepay', PaymentMethod\MobilepayPaymentMethod::class );
			$container->share( 'payment-methods-becs', PaymentMethod\BescPaymentMethod::class );
			$container->share( 'payment-methods-promptpay', PaymentMethod\PromptPayPaymentMethod::class );
			$container->share( 'payment-methods-wechat-pay', PaymentMethod\WechatPayPaymentMethod::class );
		}
		// Register the payment methods subscriber.
		$container->share( 'payment-methods-subscriber', PaymentMethodsSubscriber::class );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array( 'payment-methods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		$subscribers = array(
			'payment-methods-card',
			'payment-methods-ach-debit',
			'payment-methods-alipay',
			'payment-methods-affirm',
			'payment-methods-sepa-debit',
			'payment-methods-bacs-debit',
			'payment-methods-cashapp',
			'payment-methods-bancontact',
			'payment-methods-fpx',
			'payment-methods-grabpay',
			'payment-methods-ideal',
			'payment-methods-klarna',
			'payment-methods-p24',
			'payment-methods-afterpay-clearpay',
			'payment-methods-mobilepay',
			'payment-methods-becs',
			'payment-methods-promptpay',
			'payment-methods-wechat-pay',
			'payment-methods-subscriber',
		);

		return $subscribers;
	}
}
