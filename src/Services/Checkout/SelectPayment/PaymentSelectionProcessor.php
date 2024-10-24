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
        $activePayment = $order->getLastPayment(PaymentInterface::STATE_CART);
        if (null === $activePayment) {
            return;
        }

        if ($activePayment->getMethod()?->getCode() !== 'unzer_payment') {
            $this->clearUnzerDetails($activePayment);

            return;
        }

        /** @var string $unzerPaymentType */
        $unzerPaymentType = $this->requestStack->getCurrentRequest()?->get('unzer_payment_method_type', '');
        $this->setInUnzerDetails($activePayment, $unzerPaymentType);
    }

    private function setInUnzerDetails(PaymentInterface $payment, string $paymentType): void
    {
        $paymentDetails = $payment->getDetails();
        if ('' !== $paymentType) {
            $paymentDetails['unzer']['payment_type'] = $paymentType;
        }

        $payment->setDetails($paymentDetails);
    }

    private function clearUnzerDetails(PaymentInterface $payment): void
    {
        $paymentDetails = $payment->getDetails();
        unset($paymentDetails['unzer']);
        $payment->setDetails($paymentDetails);
    }
}
