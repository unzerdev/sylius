<?php

namespace SyliusUnzerPlugin\Services\Integration;

use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\RefundPlugin\Exception\OrderNotAvailableForRefunding;
use SyliusUnzerPlugin\Refund\PaymentRefundInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;
use Sylius\RefundPlugin\Provider\OrderRefundedTotalProviderInterface;

class OrderService implements OrderServiceInterface
{

    /**
     * @var OrderRefundedTotalProviderInterface $orderRefundedTotalProvider
     */
    private OrderRefundedTotalProviderInterface $orderRefundedTotalProvider;

    /** @var OrderRepositoryInterface<OrderInterface> */
    private OrderRepositoryInterface $orderRepository;

    /** @var PaymentRefundInterface $paymentRefund */
    private PaymentRefundInterface $paymentRefund;

    /**
     * @var FactoryInterface
     */
    private FactoryInterface $stateMachineFactory;

    /**
     * @param OrderRefundedTotalProviderInterface $orderRefundedTotalProvider
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param PaymentRefundInterface $paymentRefund
     * @param FactoryInterface $stateMachineFactory
     */
    public function __construct(
        OrderRefundedTotalProviderInterface $orderRefundedTotalProvider,
        OrderRepositoryInterface $orderRepository,
        PaymentRefundInterface $paymentRefund,
        FactoryInterface $stateMachineFactory
    ) {
        $this->orderRefundedTotalProvider = $orderRefundedTotalProvider;
        $this->orderRepository = $orderRepository;
        $this->paymentRefund = $paymentRefund;
        $this->stateMachineFactory = $stateMachineFactory;
    }


    /**
     * @inheritDoc
     * @throws InvalidCurrencyCode
     */
    public function getRefundedAmountForOrder(string $orderId): ?Amount
    {
        /** @var \Sylius\Component\Core\Model\OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        if (null === $order || null === $order->getChannel()) {
            throw new OrderNotAvailableForRefunding($orderId);
        }
       return Amount::fromInt(($this->orderRefundedTotalProvider)($order), Currency::fromIsoCode($order->getCurrencyCode()));
    }

    /**
     * @inheritDoc
     */
    public function refundOrder(string $orderId, Amount $amount): void
    {
        $this->paymentRefund->refund($orderId, $amount->getValue());
    }

    /**
     * @inheritDoc
     * @throws InvalidCurrencyCode
     */
    public function getCancelledAmountForOrder(string $orderId): ?Amount
    {
        /** @var \Sylius\Component\Core\Model\OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        if (null === $order || null === $order->getChannel()) {
            return Amount::fromInt(0, Currency::getDefault());
        }
        return Amount::fromInt($order->getTotal(), Currency::fromIsoCode($order->getCurrencyCode()));
    }

    /**
     * @inheritDoc
     * @throws SMException
     */
    public function cancelOrder(string $orderId, Amount $amount): void
    {
        /** @var \Sylius\Component\Core\Model\OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        if (null === $order || null === $order->getChannel()) {
           return;
        }
        $stateMachine = $this->stateMachineFactory->get($order, OrderTransitions::GRAPH);
        $stateMachine->apply(OrderTransitions::TRANSITION_CANCEL);
    }

    /**
     * @inheritDoc
     */
    public function getChargeAmountForOrder(string $orderId): ?Amount
    {
        return Amount::fromFloat(1, Currency::getDefault());
    }

    /**
     * @inheritDoc
     */
    public function chargeOrder(string $orderId, Amount $amount): void
    {
        // TODO: Implement chargeOrder() method.
    }

    /**
     * @inheritDoc
     */
    public function changeOrderStatus(string $orderId, string $statusId): void
    {
    }
}
