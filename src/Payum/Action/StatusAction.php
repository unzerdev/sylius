<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;

/**
 * Class StatusAction
 *
 * @package SyliusUnzerPlugin\Payum\Action
 */
class StatusAction implements ActionInterface
{

    /**
     * StatusAction constructor.
     */
    public function __construct()
    {
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /**
         * @var PaymentInterface $payment
         * @var GetStatusInterface $request
         */
        $payment = $request->getModel();
        $details = $payment->getDetails();

        $status = $details['unzer']['payment']['status'] ?? BasePaymentInterface::STATE_PROCESSING;

        if ($status === BasePaymentInterface::STATE_COMPLETED) {
            $request->markCaptured();

            return;
        }

        if ($status === BasePaymentInterface::STATE_AUTHORIZED) {
            $request->markAuthorized();

            return;
        }

        if ($status === BasePaymentInterface::STATE_FAILED) {
            $request->markFailed();

            return;
        }

        $request->markPending();
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface;
    }
}
