<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use UnzerSDK\Constants\PaymentState;

/**
 * Class StatusAction
 *
 * @package SyliusUnzerPlugin\Payum\Action
 */
class AuthorizeAction implements ActionInterface
{
    private const STATUS_MAP = [
        PaymentState::STATE_NAME_PENDING => BasePaymentInterface::STATE_AUTHORIZED,
        PaymentState::STATE_NAME_COMPLETED => BasePaymentInterface::STATE_COMPLETED,
        PaymentState::STATE_NAME_CANCELED => BasePaymentInterface::STATE_CANCELLED,
        PaymentState::STATE_NAME_PAYMENT_REVIEW => BasePaymentInterface::STATE_PROCESSING,
    ];
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
        $order = $payment->getOrder();

        if (null === $order || null === $order->getChannel()) {
            $this->setPaymentState($payment, BasePaymentInterface::STATE_UNKNOWN);

            return;
        }

        $response = CheckoutAPI::get()->paymentPage($order->getChannel()->getId())
            ->getPaymentState((string)$order->getId());

        if (
            $response->isSuccessful() &&
            array_key_exists($response->getPaymentState()->getName(), self::STATUS_MAP)
        ) {
            $this->setPaymentState($payment, self::STATUS_MAP[$response->getPaymentState()->getName()]);

            return;
        }

        $this->setPaymentState($payment, BasePaymentInterface::STATE_PROCESSING);
    }

    public function supports($request): bool
    {
        return $request instanceof Authorize &&
            $request->getFirstModel() instanceof PaymentInterface;
    }

    private function setPaymentState(PaymentInterface $payment, string $state): void
    {
        $details = $payment->getDetails();
        $details['unzer']['payment']['status'] = $state;
        $payment->setDetails($details);
    }

    private function shouldAuthorize(Authorize $request): bool
    {
        /**
         * @var PaymentInterface $payment
         */
        $payment = $request->getFirstModel();

        $status = $payment->getDetails()['unzer']['payment']['status'] ?? BasePaymentInterface::STATE_PROCESSING;

        return in_array($status, [
            BasePaymentInterface::STATE_NEW,
            BasePaymentInterface::STATE_PROCESSING,
        ], true);
    }
}
