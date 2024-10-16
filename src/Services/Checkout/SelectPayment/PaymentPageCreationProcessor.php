<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Checkout\SelectPayment;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
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
        if (!$order->canBeProcessed()) {
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

        // TODO: Call core library to create payment page request
        $paymentDetails['unzer']['payment_page'] = [
            'id' => 's-ppg-bf1d82a8c3ed53ae81c689a6fd747b8f2910400d7998868dba3590a32d92ba64'
        ];

        $payment->setDetails($paymentDetails);
    }
}
