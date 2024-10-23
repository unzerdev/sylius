<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;
use Sylius\Component\Payment\Model\PaymentInterface;

/**
 * Class StatusAction
 *
 * @package SyliusUnzerPlugin\Payum\Action
 */
class ConvertAction implements ActionInterface
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

        /** @var Convert $request */
        if (!$this->shouldConvert($request)) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = $payment->getDetails();

        // TODO: Get payment status and mark appropriate status based on unzer status
        $details['unzer']['payment']['status'] = PaymentInterface::STATE_AUTHORIZED;
        $request->setResult($details);
    }

    public function supports($request): bool
    {
        return $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array';
    }

    private function shouldConvert(Convert $request): bool
    {
        /**
         * @var PaymentInterface $payment
         */
        $payment = $request->getSource();

        $status = $payment->getDetails()['unzer']['payment']['status'] ?? PaymentInterface::STATE_PROCESSING;

        return in_array($status, [
            PaymentInterface::STATE_CART,
            PaymentInterface::STATE_NEW,
            PaymentInterface::STATE_PROCESSING,
        ], true);
    }
}
