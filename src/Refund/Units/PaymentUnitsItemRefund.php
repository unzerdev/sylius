<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Units;

use SyliusUnzerPlugin\Calculator\Refund\PaymentRefundCalculatorInterface;
use SyliusUnzerPlugin\DTO\PartialRefundItem;
use SyliusUnzerPlugin\Refund\Generator\PaymentNewUnitRefundGeneratorInterface;
use SyliusUnzerPlugin\Refund\Generator\PaymentRefundedGeneratorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;

final class PaymentUnitsItemRefund implements PaymentUnitsItemRefundInterface
{
    /** @var PaymentRefundedGeneratorInterface */
    private PaymentRefundedGeneratorInterface $paymentRefundedGenerator;

    /** @var PaymentNewUnitRefundGeneratorInterface */
    private PaymentNewUnitRefundGeneratorInterface $paymentNewUnitRefundGenerator;

    /** @var PaymentRefundCalculatorInterface */
    private PaymentRefundCalculatorInterface $paymentRefundCalculator;

    public function __construct(
        PaymentRefundedGeneratorInterface $paymentRefundedGenerator,
        PaymentNewUnitRefundGeneratorInterface $paymentNewUnitRefundGenerator,
        PaymentRefundCalculatorInterface $paymentRefundCalculator
    ) {
        $this->paymentRefundedGenerator = $paymentRefundedGenerator;
        $this->paymentNewUnitRefundGenerator = $paymentNewUnitRefundGenerator;
        $this->paymentRefundCalculator = $paymentRefundCalculator;
    }

    public function refund(OrderInterface $order, int $totalToRefund): array
    {
        $partialRefundItems = $this->paymentRefundedGenerator->generate($order);
        $partialRefundItems = $this->paymentNewUnitRefundGenerator->generate($order, $partialRefundItems);
        $partialRefundItems = $this->paymentRefundCalculator->calculate($partialRefundItems, $totalToRefund);

        $unitsToRefund = [];
        /** @var PartialRefundItem $partialRefundItem */
        foreach ($partialRefundItems->getPartialRefundItems() as $partialRefundItem) {
            if (0 < $partialRefundItem->getAmountToRefund()) {
                $unitsToRefund[] = new OrderItemUnitRefund(
                    $partialRefundItem->getId(),
                    $partialRefundItem->getAmountToRefund()
                );
            }
        }

        return $unitsToRefund;
    }
}
