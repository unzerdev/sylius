<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Checkout\SelectPayment;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

/**
 * Class PaymentSelectionProcessor
 *
 * @package SyliusUnzerPlugin\Services\Checkout\SelectPayment
 */
final class PaymentSelectionProcessor implements OrderProcessorInterface
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

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

        $unzerPaymentType = $this->requestStack->getCurrentRequest()?->get('unzer_payment_method_type');
        if (null === $unzerPaymentType) {
            return;
        }

        $payment->setDetails(array_merge($payment->getDetails(), ['unzer_payment_method_type' => $unzerPaymentType]));

        // TODO: Call core library to create payment page request
    }
}
