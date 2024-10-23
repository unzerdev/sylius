<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Units;

use Sylius\Component\Core\Model\OrderInterface;

interface PaymentUnitsItemRefundInterface
{
    public function refund(OrderInterface $order, int $totalToRefund): array;
}
