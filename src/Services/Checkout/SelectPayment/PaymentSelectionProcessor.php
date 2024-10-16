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
        if (null === $payment) {
            return;
        }

        $paymentDetails = $payment->getDetails();
        if ($payment->getMethod()?->getCode() !== 'unzer_payment') {
            unset($paymentDetails['unzer']);
        }

        $unzerPaymentType = $this->requestStack->getCurrentRequest()?->get('unzer_payment_method_type', '');
        if ('' !== $unzerPaymentType) {
            $paymentDetails['unzer'] = ['payment_type' => $unzerPaymentType];
        }

        $payment->setDetails($paymentDetails);
    }
}
