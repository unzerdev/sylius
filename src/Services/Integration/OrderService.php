<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\RefundPlugin\Exception\OrderNotAvailableForRefunding;
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

    /**
     * @param OrderRefundedTotalProviderInterface $orderRefundedTotalProvider
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        OrderRefundedTotalProviderInterface $orderRefundedTotalProvider,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRefundedTotalProvider = $orderRefundedTotalProvider;
        $this->orderRepository = $orderRepository;
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

        $refunded = ($this->orderRefundedTotalProvider)($order);
       return Amount::fromInt(1, Currency::fromIsoCode($order->getCurrencyCode()));
    }

    /**
     * @inheritDoc
     */
    public function refundOrder(string $orderId, Amount $amount): void
    {
        // TODO: Implement refundOrder() method.
    }

    /**
     * @inheritDoc
     */
    public function getCancelledAmountForOrder(string $orderId): ?Amount
    {
        return Amount::fromFloat(1, Currency::getDefault());
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(string $orderId, Amount $amount): void
    {
        // TODO: Implement cancelOrder() method.
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
