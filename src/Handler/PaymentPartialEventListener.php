<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Handler;

use Payum\Core\Payum;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Sylius\RefundPlugin\Exception\OrderNotAvailableForRefunding;
use Sylius\RefundPlugin\Exception\RefundUnitsNotBelongToOrder;
use Sylius\RefundPlugin\Validator\RefundUnitsValidationConstraintMessages;
use SyliusUnzerPlugin\EventListener\DisableListenerInterface;
use SyliusUnzerPlugin\EventListener\DisableListenerTrait;
use SyliusUnzerPlugin\Handler\Request\RefundOrder;
use SyliusUnzerPlugin\Util\StaticHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final class PaymentPartialEventListener implements DisableListenerInterface
{
    use DisableListenerTrait;

    /** @var OrderRepositoryInterface<OrderInterface> */
    private OrderRepositoryInterface $orderRepository;


    /** @var Payum */
    private Payum $payum;

    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param Payum $payum
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Payum $payum
    ) {
        $this->orderRepository = $orderRepository;
        $this->payum = $payum;
    }


    public function __invoke(UnitsRefunded $units): void
    {
        if (!$this->enabled) {
            return;
        }
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['number' => $units->orderNumber()]);

        /** @var PaymentInterface|null $payment */
        $payment = $order->getPayments()->last();
        if (null === $payment) {
            throw new NotFoundHttpException();
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        $gateWayName = $paymentMethod->getGatewayConfig()->getGatewayName();

        if ($gateWayName !== StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY) {
            return;
        }


        if ($paymentMethod->getId() !== $units->paymentMethodId()) {
            throw RefundUnitsNotBelongToOrder::withValidationConstraint(
                "Refund payment method does not match the payment method of the order",
            );
        }

        $details = $payment->getDetails();
        /** @var ChannelInterface $channel */
        $channel = $order->getChannel();
        $details['metadata']['refund']['items'] = $units->units();
        $details['metadata']['refund']['refundedTotal'] = $units->amount();
        $details['metadata']['refund']['orderId'] = (string) $order->getId();
        $details['metadata']['refund']['channelId'] = (string) $channel->getId();
        $details['metadata']['refund']['currencyCode'] = (string) $order->getCurrencyCode();
        $details['metadata']['refund']['leftToRefund'] = $order->getTotal() - $units->amount();
        $payment->setDetails($details);

        $gateway = $this->payum->getGateway('unzer_payment');

        $gateway->execute(new RefundOrder($details));
    }
}
