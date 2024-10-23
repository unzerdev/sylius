<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Calculator\Refund;

use SyliusUnzerPlugin\DTO\PartialRefundItems;

interface PaymentRefundCalculatorInterface
{
    public function calculate(PartialRefundItems $partialRefundItems, int $totalToRefund): PartialRefundItems;
}
