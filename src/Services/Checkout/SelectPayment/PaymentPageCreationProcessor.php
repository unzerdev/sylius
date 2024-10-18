<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Checkout\SelectPayment;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Webmozart\Assert\Assert;

/**
 * Class PaymentPageCreationProcessor
 *
 * @package SyliusUnzerPlugin\Services\Checkout\SelectPayment
 */
final class PaymentPageCreationProcessor implements OrderProcessorInterface
{
    public function process(BaseOrderInterface $order): void
    {
        Assert::isInstanceOf($order, OrderInterface::class);

        /** @var OrderInterface $order */
        if (!$order->canBeProcessed() || null === $order->getChannel()) {
            return;
        }

        $payment = $order->getLastPayment(PaymentInterface::STATE_CART);
        if (null === $payment || $payment->getMethod()?->getCode() !== 'unzer_payment' ) {
            return;
        }

        $paymentDetails = $payment->getDetails();
        if (
            !array_key_exists('unzer', $paymentDetails) ||
            !array_key_exists('payment_type', $paymentDetails['unzer'])
        ) {
            return;
        }

        $response = CheckoutAPI::get()->paymentPage($order->getChannel()->getId())->create(new PaymentPageCreateRequest(
            $paymentDetails['unzer']['payment_type'],
            Amount::fromInt($order->getTotal(), Currency::fromIsoCode($order->getCurrencyCode())),
            'http://1-13-4.sylius.localhost/en_US/checkout/select-payment'
        ));

        if ($response->isSuccessful()) {
            $paymentDetails['unzer']['payment_page'] = [
                'id' => $response->toArray()['id']
            ];
        }

        $payment->setDetails($paymentDetails);
    }
}
