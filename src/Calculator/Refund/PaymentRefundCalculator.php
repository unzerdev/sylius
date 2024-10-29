<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Calculator\Refund;

use SyliusUnzerPlugin\DTO\PartialRefundItem;
use SyliusUnzerPlugin\DTO\PartialRefundItems;

final class PaymentRefundCalculator implements PaymentRefundCalculatorInterface
{
    public function calculate(PartialRefundItems $partialRefundItems, int $totalToRefund): PartialRefundItems
    {
        /** @var PartialRefundItem $partialRefundItem */
        foreach ($partialRefundItems->getPartialRefundItems() as $partialRefundItem) {
            if (0 < $partialRefundItem->getAvailableAmountToRefund()) {
                $totalToRefund = $partialRefundItem->setAmountToRefund($totalToRefund);
                if (0 === $totalToRefund) {
                    break;
                }
            }
        }

        return $partialRefundItems;
    }
}
