<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Sylius\Component\Payment\Model\PaymentInterface;

/**
 * Class StatusAction
 *
 * @package SyliusUnzerPlugin\Payum\Action
 */
class CaptureAction implements ActionInterface
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

        /** @var Capture $request */
        if (!$this->shouldCapture($request)) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        $details = $payment->getDetails();

        // TODO: Check details make capture request and mark appropriate status
        $details['unzer']['payment']['status'] = PaymentInterface::STATE_COMPLETED;
        $payment->setDetails($details);
    }

    public function supports($request): bool
    {
        return $request instanceof Capture &&
            $request->getFirstModel() instanceof PaymentInterface;
    }

    private function shouldCapture(Capture $request): bool
    {
        /**
         * @var PaymentInterface $payment
         */
        $payment = $request->getFirstModel();

        $status = $payment->getDetails()['unzer']['payment']['status'] ?? PaymentInterface::STATE_PROCESSING;

        return !in_array($status, [
            PaymentInterface::STATE_CANCELLED,
            PaymentInterface::STATE_REFUNDED,
            PaymentInterface::STATE_COMPLETED,
            PaymentInterface::STATE_FAILED,
        ], true);
    }
}
