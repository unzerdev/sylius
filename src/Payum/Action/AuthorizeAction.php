<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Sylius\Component\Payment\Model\PaymentInterface;

/**
 * Class StatusAction
 *
 * @package SyliusUnzerPlugin\Payum\Action
 */
class AuthorizeAction implements ActionInterface
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

        /** @var Authorize $request */
        if (!$this->shouldAuthorize($request)) {
            return;
        }

        /**
         * @var PaymentInterface $payment
         * @var Capture $request
         */
        $payment = $request->getFirstModel();

        $details = $payment->getDetails();

        // TODO: Get payment status and mark appropriate status based on unzer status. We don't actually make authorize request here since paypage already done this.
        $details['unzer']['payment']['status'] = PaymentInterface::STATE_AUTHORIZED;
        $payment->setDetails($details);
    }

    public function supports($request): bool
    {
        return $request instanceof Authorize &&
            $request->getFirstModel() instanceof PaymentInterface;
    }

    private function shouldAuthorize(Authorize $request): bool
    {
        /**
         * @var PaymentInterface $payment
         */
        $payment = $request->getFirstModel();

        $status = $payment->getDetails()['unzer']['payment']['status'] ?? PaymentInterface::STATE_PROCESSING;

        return in_array($status, [
            PaymentInterface::STATE_NEW,
            PaymentInterface::STATE_PROCESSING,
        ], true);
    }
}
