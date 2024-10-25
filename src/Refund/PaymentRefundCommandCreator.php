<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Command\RefundUnits;
use Sylius\RefundPlugin\Exception\OrderNotAvailableForRefunding;
use Sylius\RefundPlugin\Provider\RefundPaymentMethodsProviderInterface;
use SyliusUnzerPlugin\Refund\Units\PaymentUnitsItemRefundInterface;
use SyliusUnzerPlugin\Refund\Units\ShipmentUnitRefundInterface;
use Webmozart\Assert\Assert;

final class PaymentRefundCommandCreator implements PaymentRefundCommandCreatorInterface
{
    /** @var RepositoryInterface */
    private RepositoryInterface $orderRepository;

    /** @var RepositoryInterface */
    private RepositoryInterface $refundUnitsRepository;

    /** @var PaymentUnitsItemRefundInterface */
    private PaymentUnitsItemRefundInterface $itemRefund;

    /** @var ShipmentUnitRefundInterface */
    private ShipmentUnitRefundInterface $shipmentRefund;

    /** @var RefundPaymentMethodsProviderInterface */
    private RefundPaymentMethodsProviderInterface $refundPaymentMethodProvider;


    public function __construct(
        RepositoryInterface $orderRepository,
        RepositoryInterface $refundUnitsRepository,
        PaymentUnitsItemRefundInterface $itemRefund,
        ShipmentUnitRefundInterface $shipmentRefund,
        RefundPaymentMethodsProviderInterface $refundPaymentMethodProvider
    ) {
        $this->orderRepository = $orderRepository;
        $this->refundUnitsRepository = $refundUnitsRepository;
        $this->itemRefund = $itemRefund;
        $this->shipmentRefund = $shipmentRefund;
        $this->refundPaymentMethodProvider = $refundPaymentMethodProvider;
    }

    public function fromOderAndAmount(string $orderId, int $amount): RefundUnits
    {

        /** @var ?OrderInterface $order */
        $order = $this->orderRepository->findOneBy(['id' => $orderId]);
        Assert::notNull($order, sprintf('Cannot find order id with id %s', $orderId));

        $refunded = $this->getSumOfAmountExistingRefunds(
            $this->refundUnitsRepository->findBy(['order' => $order->getId()])
        );
        $left = $order->getTotal() - $refunded;
        $toRefund = $amount;
        if ($amount > $left || $amount < $left) {
            $toRefund = $left;
        }

        Assert::notNull($order->getChannel());
        $refundMethods = $this->refundPaymentMethodProvider->findForChannel($order->getChannel());

        if (0 === count($refundMethods)) {
            throw new OrderNotAvailableForRefunding(
                sprintf('Not found offline payment method on this channel with code :%s', $order->getChannel()->getCode())
            );
        }

        $refundMethod = current($refundMethods);

        $orderItemUnitRefund = $this->itemRefund->refund($order, $toRefund);
        $shipmentRefund = $this->shipmentRefund->refund($order, $orderItemUnitRefund, $toRefund);

        Assert::notNull($order->getNumber());

        return new RefundUnits($order->getNumber(), $orderItemUnitRefund, $shipmentRefund, $refundMethod->getId(), '');
    }

    private function getSumOfAmountExistingRefunds(array $refundedUnits): int
    {
        $sum = 0;

        if (0 === count($refundedUnits)) {
            return $sum;
        }

        foreach ($refundedUnits as $refundedUnit) {
            $sum += $refundedUnit->getAmount();
        }

        return $sum;
    }
}
