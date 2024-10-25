<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\EventListener;
use Sylius\Component\Payment\Model\PaymentInterface;

class PaymentCancelListener
{
    public function cancelPayment(PaymentInterface $payment): void
    {
        // Proveri da li je novo stanje "cancelled"
        if ($payment->getState() === PaymentInterface::STATE_CANCELLED) {
            // Pozovi Payum cancel akciju
            $gatewayName = $payment->getMethod()?->getGatewayConfig()?->getGatewayName();
            $gateway = $this->payum->getGateway($gatewayName);

            $gateway->execute(new Cancel($payment));
        }
    }

}
