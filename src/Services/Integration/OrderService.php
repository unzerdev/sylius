<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration;

use Doctrine\ORM\EntityManagerInterface;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface as PaymentInterfaceAlias;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\RefundPlugin\Exception\OrderNotAvailableForRefunding;
use SyliusUnzerPlugin\Refund\PaymentRefundInterface;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;
use Sylius\RefundPlugin\Provider\OrderRefundedTotalProviderInterface;

/**
 * Class OrderService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
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
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param OrderRefundedTotalProviderInterface $orderRefundedTotalProvider
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param PaymentRefundInterface $paymentRefund
     * @param FactoryInterface $stateMachineFactory
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        OrderRefundedTotalProviderInterface $orderRefundedTotalProvider,
        OrderRepositoryInterface $orderRepository,
        PaymentRefundInterface $paymentRefund,
        FactoryInterface $stateMachineFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->orderRefundedTotalProvider = $orderRefundedTotalProvider;
        $this->orderRepository = $orderRepository;
        $this->paymentRefund = $paymentRefund;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     * @throws InvalidCurrencyCode
     */
    public function getRefundedAmountForOrder(string $orderId): Amount
    {
        /** @var \Sylius\Component\Core\Model\OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        if (null === $order || null === $order->getChannel()) {
            throw new OrderNotAvailableForRefunding($orderId);
        }
        return Amount::fromInt(($this->orderRefundedTotalProvider)($order),
            Currency::fromIsoCode($order->getCurrencyCode()));
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
    public function getCancelledAmountForOrder(string $orderId): Amount
    {
        /** @var \Sylius\Component\Core\Model\OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        if (null === $order || null === $order->getChannel()) {
            return Amount::fromInt(0, Currency::getDefault());
        }

        return Amount::fromInt(0, Currency::fromIsoCode($order->getCurrencyCode()));
    }

    /**
     * @inheritDoc
     * @throws SMException
     */
    public function cancelOrder(string $orderId, Amount $amount, bool $isFullCancellation): void
    {
        if (!$isFullCancellation) {
            return;
        }

        /** @var \Sylius\Component\Core\Model\OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        if (null === $order || null === $order->getChannel() || $order->getState() === PaymentInterfaceAlias::STATE_CANCELLED) {
            return;
        }
        $stateMachine = $this->stateMachineFactory->get($order, OrderTransitions::GRAPH);
        $stateMachine->apply(OrderTransitions::TRANSITION_CANCEL);
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    /**
     * @param string $orderId
     *
     * @return Amount|null
     *
     * @throws InvalidCurrencyCode
     */
    public function getChargeAmountForOrder(string $orderId): Amount
    {
        /** @var \Sylius\Component\Core\Model\OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        if (null === $order || null === $order->getChannel()) {
            return Amount::fromInt(0, Currency::getDefault());
        }

        if ($order->getPaymentState() !== PaymentInterfaceAlias::STATE_AUTHORIZED) {
            return Amount::fromInt($order->getTotal(), Currency::fromIsoCode($order->getCurrencyCode()));
        }

        return Amount::fromInt(0, Currency::fromIsoCode($order->getCurrencyCode()));
    }

    /**
     * @param string $orderId
     * @param Amount $amount
     * @param bool $isFullCharge
     *
     * @return void
     * @throws SMException
     */
    public function chargeOrder(string $orderId, Amount $amount, bool $isFullCharge): void
    {
        if (!$isFullCharge) {
            return;
        }

        /** @var \Sylius\Component\Core\Model\OrderInterface|null $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        if (null === $order || null === $order->getChannel()) {
            return;
        }

        foreach ($order->getPayments() as $payment) {
            $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);
            $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
            $this->entityManager->persist($payment);
        }

        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    public function changeOrderStatus(string $orderId, string $statusId): void
    {
    }
}
